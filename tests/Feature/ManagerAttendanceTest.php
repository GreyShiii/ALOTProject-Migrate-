<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ManagerAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private Employee $manager;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'Asia/Manila'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->department = Department::create(['name' => 'Engineering']);
        $this->manager = $this->employee('manager@example.com', role: 'manager');
        $this->actingAs($this->manager->user)->withoutVite();
    }

    private function employee(string $email, ?int $managerId = null, string $role = 'employee'): Employee
    {
        $user = User::create([
            'first_name' => ucfirst($role), 'last_name' => 'Test', 'email' => $email,
            'password' => 'test-password', 'status' => 'active',
        ]);
        $user->assignRole($role);

        return Employee::create([
            'user_id' => $user->id, 'department_id' => $this->department->id,
            'manager_id' => $managerId, 'position' => 'Developer',
        ]);
    }

    private function attendance(Employee $employee, string $date, string $status = 'working'): Attendance
    {
        return Attendance::create([
            'employee_id' => $employee->id, 'date' => $date,
            'time_in' => $status === 'not_started' ? null : "$date 08:00:00",
            'time_out' => $status === 'completed' ? "$date 17:30:00" : null,
        ]);
    }

    public function test_empty_attendance_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('manager.attendance.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Manager/Attendance/Index')
                ->where('todayAttendance', null)->where('todayLabel', 'October 15, 2026')
                ->where('timezone', 'Asia/Manila')->where('links.data', route('manager.attendance.data'))
                ->where('links.timeIn', route('manager.attendance.timeIn'))->where('links.timeOut', route('manager.attendance.timeOut')));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('manager.attendance.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Manager/Attendance/Index');
    }

    public function test_today_card_only_contains_the_managers_attendance(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        $this->attendance($worker, '2026-10-15', 'completed');
        $mine = $this->attendance($this->manager, '2026-10-15');
        $this->get(route('manager.attendance.index'))->assertInertia(fn (Assert $page) => $page
            ->where('todayAttendance.id', $mine->id)->where('todayAttendance.date', '2026-10-15')
            ->where('todayAttendance.time_in', '2026-10-15T00:00:00.000000Z')->where('todayAttendance.time_out', null));
    }

    public function test_personal_and_team_histories_are_separately_scoped_and_paginated(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        $outsider = $this->employee('outsider@example.com');
        for ($i = 0; $i < 12; $i++) {
            $date = today()->subDays($i)->toDateString();
            $this->attendance($this->manager, $date);
            $this->attendance($worker, $date);
            $this->attendance($outsider, $date);
        }
        $this->getJson(route('manager.attendance.data', ['scope' => 'mine']))->assertOk()
            ->assertJsonCount(10, 'attendances')->assertJsonPath('pagination.total', 12)
            ->assertJsonPath('attendances.0.employee_id', $this->manager->id)
            ->assertJsonPath('attendances.0.date', '2026-10-15')->assertJsonMissingPath('attendances.0.employee.user.password');
        $this->getJson(route('manager.attendance.data', ['scope' => 'team', 'page' => 2]))->assertOk()
            ->assertJsonCount(2, 'attendances')->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.total', 12)->assertJsonPath('attendances.0.employee_id', $worker->id)
            ->assertJsonPath('attendances.0.employee.department.name', 'Engineering');
    }

    public function test_team_filters_and_personal_date_search_preserve_scope(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        $outsider = $this->employee('outsider@example.com');
        $this->attendance($worker, '2026-10-15', 'completed');
        $this->attendance($worker, '2026-10-14');
        $this->attendance($worker, '2026-10-13', 'not_started');
        $this->attendance($outsider, '2026-10-15', 'completed');
        $this->attendance($this->manager, '2026-10-14', 'completed');
        $this->attendance($this->manager, '2026-10-13', 'completed');
        $this->getJson(route('manager.attendance.data', [
            'scope' => 'team', 'search' => 'worker@example.com', 'date' => '2026-10-15', 'status' => 'completed',
        ]))->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('attendances.0.employee_id', $worker->id);
        $this->getJson(route('manager.attendance.data', ['scope' => 'team', 'status' => 'not_started']))
            ->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('attendances.0.date', '2026-10-13');
        $this->getJson(route('manager.attendance.data', ['scope' => 'mine', 'search' => '2026-10-14']))
            ->assertOk()->assertJsonPath('pagination.total', 1)->assertJsonPath('attendances.0.date', '2026-10-14');
    }

    public function test_time_in_and_out_only_change_the_current_manager_and_prevent_duplicates(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        $this->postJson(route('manager.attendance.timeIn'), ['employee_id' => $worker->id])->assertOk()
            ->assertJsonPath('attendance.employee_id', $this->manager->id);
        $this->postJson(route('manager.attendance.timeIn'))->assertUnprocessable();
        $this->assertDatabaseCount('attendances', 1);
        $this->travel(2)->hours();
        $this->postJson(route('manager.attendance.timeOut'))->assertOk()
            ->assertJsonPath('attendance.employee_id', $this->manager->id);
        $this->postJson(route('manager.attendance.timeOut'))->assertNotFound();
        $this->assertSame('14:00', Attendance::firstOrFail()->time_out->format('H:i'));
    }

    public function test_twenty_four_hour_cooldown_applies_across_calendar_days(): void
    {
        Attendance::create([
            'employee_id' => $this->manager->id, 'date' => '2026-10-14',
            'time_in' => '2026-10-14 16:00:00', 'time_out' => '2026-10-14 18:00:00',
        ]);
        $this->postJson(route('manager.attendance.timeIn'))->assertUnprocessable()
            ->assertJsonPath('success', false);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_manager_without_employee_record_gets_clear_errors(): void
    {
        $this->manager->delete();
        $this->get(route('manager.attendance.index'))->assertForbidden();
        $this->getJson(route('manager.attendance.data'))->assertNotFound();
        $this->postJson(route('manager.attendance.timeIn'))->assertNotFound();
        $this->postJson(route('manager.attendance.timeOut'))->assertNotFound();
    }

    public function test_attendance_permission_is_required_for_all_endpoints(): void
    {
        $this->manager->user->roles->first()->revokePermissionTo('attendance.manage.team');
        $this->get(route('manager.attendance.index'))->assertForbidden();
        $this->getJson(route('manager.attendance.data'))->assertForbidden();
        $this->postJson(route('manager.attendance.timeIn'))->assertForbidden();
        $this->postJson(route('manager.attendance.timeOut'))->assertForbidden();
    }

    public function test_employee_cannot_access_manager_attendance(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        $this->actingAs($worker->user)->get(route('manager.attendance.index'))->assertForbidden();
        $this->postJson(route('manager.attendance.timeIn'))->assertForbidden();
    }
}
