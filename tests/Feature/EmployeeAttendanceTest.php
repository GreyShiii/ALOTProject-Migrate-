<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmployeeAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;
    private Employee $other;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->department = Department::create(['name' => 'Engineering']);
        $this->employee = $this->employee('employee@example.com');
        $this->other = $this->employee('other@example.com');
        $this->actingAs($this->employee->user)->withoutVite();
    }

    private function employee(string $email, string $role = 'employee'): Employee
    {
        $user = User::create([
            'first_name' => ucfirst($role), 'last_name' => 'Test', 'email' => $email,
            'password' => 'test-password', 'status' => 'active',
        ]);
        $user->assignRole($role);

        return Employee::create(['user_id' => $user->id, 'department_id' => $this->department->id, 'position' => 'Developer']);
    }

    private function attendance(Employee $employee, string $date, string $status = 'working'): Attendance
    {
        return Attendance::create([
            'employee_id' => $employee->id, 'date' => $date,
            'time_in' => $status === 'not_started' ? null : "$date 08:05:00",
            'time_out' => $status === 'completed' ? "$date 17:35:30" : null,
        ]);
    }

    public function test_attendance_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('employee.attendance.index', ['search' => 'Oct 15, 2026', 'status' => 'completed']))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Employee/Attendance/Index')
                ->where('dataUrl', route('employee.attendance.data'))->where('timezone', 'Asia/Manila')
                ->where('filters.search', 'Oct 15, 2026')->where('filters.status', 'completed'));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('employee.attendance.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Employee/Attendance/Index');
    }

    public function test_empty_history_is_valid_and_only_contains_the_current_employees_records(): void
    {
        $this->attendance($this->other, '2026-10-15');
        $this->getJson(route('employee.attendance.data'))->assertOk()
            ->assertJsonCount(0, 'attendances')->assertJsonPath('pagination.total', 0);
        for ($index = 1; $index <= 12; $index++) {
            $this->attendance($this->employee, sprintf('2026-10-%02d', $index));
        }
        $this->getJson(route('employee.attendance.data', ['employee_id' => $this->other->id, 'scope' => 'team']))
            ->assertOk()->assertJsonCount(10, 'attendances')->assertJsonPath('pagination.total', 12)
            ->assertJsonPath('attendances.0.date', '2026-10-12')
            ->assertJsonPath('attendances.0.employee_id', $this->employee->id)
            ->assertJsonMissingPath('attendances.0.employee');
        $this->getJson(route('employee.attendance.data', ['page' => 2]))->assertOk()
            ->assertJsonCount(2, 'attendances')->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('attendances.0.date', '2026-10-02')->assertJsonPath('attendances.1.date', '2026-10-01');
    }

    public function test_calendar_dates_and_timestamp_offsets_are_preserved(): void
    {
        $this->attendance($this->employee, '2026-10-15', 'completed');
        $this->getJson(route('employee.attendance.data'))->assertOk()->assertJsonPath('attendances.0.date', '2026-10-15')
            ->assertJsonPath('attendances.0.time_in', '2026-10-15T00:05:00.000000Z')
            ->assertJsonPath('attendances.0.time_out', '2026-10-15T09:35:30.000000Z');
    }

    public function test_date_search_and_status_filters_preserve_personal_scope(): void
    {
        $this->attendance($this->employee, '2026-10-15', 'completed');
        $this->attendance($this->employee, '2026-10-14');
        $this->attendance($this->employee, '2026-10-13', 'not_started');
        $this->attendance($this->other, '2026-10-15', 'completed');
        foreach (['2026-10-15', 'Oct 15, 2026', 'October 15, 2026'] as $search) {
            $this->getJson(route('employee.attendance.data', ['search' => $search, 'status' => 'completed']))
                ->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('attendances.0.date', '2026-10-15');
        }
        $this->getJson(route('employee.attendance.data', ['search' => '2026-10']))
            ->assertOk()->assertJsonPath('pagination.total', 3);
        foreach (['working' => '2026-10-14', 'completed' => '2026-10-15', 'not_started' => '2026-10-13'] as $status => $date) {
            $this->getJson(route('employee.attendance.data', ['date' => $date, 'status' => $status]))
                ->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('attendances.0.employee_id', $this->employee->id);
        }
        $this->getJson(route('employee.attendance.data', ['date' => '2026-10-14', 'status' => 'completed']))
            ->assertOk()->assertJsonCount(0, 'attendances');
    }

    public function test_invalid_filters_return_json_validation_errors(): void
    {
        $this->getJson(route('employee.attendance.data', [
            'date' => 'not-a-date', 'status' => 'unknown', 'page' => 0, 'search' => str_repeat('x', 101),
        ]))->assertUnprocessable()->assertJsonValidationErrors(['date', 'status', 'page', 'search']);
    }

    public function test_missing_employee_record_returns_clear_errors(): void
    {
        $this->employee->delete();
        $this->get(route('employee.attendance.index'))->assertForbidden();
        $this->getJson(route('employee.attendance.data'))->assertNotFound();
    }

    public function test_attendance_permission_is_required_for_page_data_and_actions(): void
    {
        $this->employee->user->roles->first()->revokePermissionTo('attendance.manage.own');
        $this->get(route('employee.attendance.index'))->assertForbidden();
        $this->getJson(route('employee.attendance.data'))->assertForbidden();
        $this->postJson(route('employee.attendance.timeIn'))->assertForbidden();
        $this->postJson(route('employee.attendance.timeOut'))->assertForbidden();
    }

    public function test_manager_cannot_access_employee_attendance(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $this->actingAs($manager->user);
        $this->get(route('employee.attendance.index'))->assertForbidden();
        $this->getJson(route('employee.attendance.data'))->assertForbidden();
    }
}
