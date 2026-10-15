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

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'Test', 'email' => 'admin@example.com',
            'password' => 'test-password', 'status' => 'active',
        ]);
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin)->withoutVite();
    }

    private function employee(): Employee
    {
        return Employee::create([
            'user_id' => $this->admin->id,
            'department_id' => Department::create(['name' => 'Engineering'])->id,
            'position' => 'Administrator',
        ]);
    }

    public function test_empty_attendance_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('admin.attendance.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Attendance/Index')
                ->has('attendances', 0)->has('departments', 0)->where('timezone', 'Asia/Manila'));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.attendance.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Attendance/Index');
    }

    public function test_records_include_relationships_calendar_dates_and_timestamps_without_passwords(): void
    {
        $employee = $this->employee();
        Attendance::create([
            'employee_id' => $employee->id, 'date' => '2026-10-15',
            'time_in' => '2026-10-15 08:00:00', 'time_out' => '2026-10-15 17:30:00',
        ]);
        $this->get(route('admin.attendance.index'))->assertInertia(fn (Assert $page) => $page
            ->where('attendances.0.date', '2026-10-15')
            ->where('attendances.0.time_in', '2026-10-15T00:00:00.000000Z')
            ->where('attendances.0.time_out', '2026-10-15T09:30:00.000000Z')
            ->where('attendances.0.employee.department.name', 'Engineering')
            ->where('attendances.0.employee.user.email', 'admin@example.com')
            ->missing('attendances.0.employee.user.password'));
    }

    public function test_records_are_latest_first_and_keep_incomplete_attendance(): void
    {
        $employee = $this->employee();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-10-13']);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-10-15', 'time_in' => '2026-10-15 08:00:00']);
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-10-14', 'time_in' => '2026-10-14 08:00:00', 'time_out' => '2026-10-14 17:00:00']);
        $this->get(route('admin.attendance.index'))->assertInertia(fn (Assert $page) => $page
            ->has('attendances', 3)->where('attendances.0.date', '2026-10-15')
            ->where('attendances.0.time_out', null)->where('attendances.1.date', '2026-10-14')
            ->where('attendances.2.time_in', null)->where('attendances.2.time_out', null));
    }

    public function test_existing_json_endpoint_remains_available(): void
    {
        $employee = $this->employee();
        Attendance::create(['employee_id' => $employee->id, 'date' => '2026-10-15']);
        $this->getJson(route('admin.attendance.data'))->assertOk()
            ->assertJsonPath('success', true)->assertJsonCount(1, 'attendances')
            ->assertJsonPath('attendances.0.employee.department.name', 'Engineering');
    }

    public function test_attendance_permission_is_required_for_both_endpoints(): void
    {
        $this->admin->roles->first()->revokePermissionTo('attendance.view.all');
        $this->get(route('admin.attendance.index'))->assertForbidden();
        $this->getJson(route('admin.attendance.data'))->assertForbidden();
    }

    public function test_non_admin_cannot_access_attendance(): void
    {
        $this->admin->syncRoles('employee');
        $this->get(route('admin.attendance.index'))->assertForbidden();
    }
}
