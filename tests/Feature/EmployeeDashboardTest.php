<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmployeeDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;
    private Employee $other;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(10, 15, 45));
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

    private function leave(?Employee $employee = null, string $status = 'Pending', string $date = '2026-10-16'): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => ($employee ?? $this->employee)->id, 'leave_type' => 'Sick',
            'start_date' => $date, 'end_date' => $date, 'status' => $status, 'reason' => 'Recovery',
        ]);
    }

    private function overtime(?Employee $employee = null, string $status = 'Pending', string $date = '2026-10-16'): OvertimeRequest
    {
        return OvertimeRequest::create([
            'employee_id' => ($employee ?? $this->employee)->id, 'date' => $date, 'hours' => 2.5,
            'status' => $status, 'reason' => 'Release support',
        ]);
    }

    public function test_empty_dashboard_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('employee.dashboard'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Employee/Dashboard')
                ->where('attendance.status', 'not_started')->where('attendance.time_in', null)
                ->where('attendance.time_out', null)->where('attendance.total_hours', null)
                ->where('todayLabel', 'October 15, 2026')->where('pendingLeaveCount', 0)
                ->where('pendingOvertimeCount', 0)->where('approvedLeaveCount', 0)->where('approvedOvertimeCount', 0)
                ->has('recentRequests', 0)->where('links.leave', route('employee.leave.index'))
                ->where('links.overtime', route('employee.overtime.index'))
                ->where('links.timeIn', route('employee.attendance.timeIn'))
                ->where('links.timeOut', route('employee.attendance.timeOut')));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('employee.dashboard'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Employee/Dashboard');
        $this->getJson(route('employee.dashboard.data'))->assertOk()->assertJsonPath('attendance.status', 'not_started');
    }

    public function test_counts_only_include_own_requests_and_approvals_in_the_current_year(): void
    {
        foreach (['Pending', 'Approved', 'Rejected'] as $status) {
            $this->leave(status: $status);
            $this->overtime(status: $status);
            $this->leave($this->other, $status);
            $this->overtime($this->other, $status);
        }
        $this->leave(status: 'Approved', date: '2025-10-01');
        $this->overtime(status: 'Approved', date: '2025-10-01');
        $this->leave(date: '2025-10-01');
        $this->overtime(date: '2025-10-01');
        $this->get(route('employee.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('pendingLeaveCount', 2)->where('pendingOvertimeCount', 2)
            ->where('approvedLeaveCount', 1)->where('approvedOvertimeCount', 1));
    }

    public function test_recent_requests_merge_both_types_in_submission_order_and_limit_to_five(): void
    {
        for ($index = 0; $index < 7; $index++) {
            $request = $index % 2 === 0 ? $this->leave() : $this->overtime(status: 'Approved');
            $request->created_at = now()->subMinutes(7 - $index);
            $request->save();
        }
        $foreign = $this->leave($this->other);
        $foreign->update(['reason' => 'Private outsider request']);
        $this->get(route('employee.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->has('recentRequests', 5)->where('recentRequests.0.type', 'Sick')
            ->where('recentRequests.0.date', 'Oct 16, 2026')->where('recentRequests.0.status', 'Pending')
            ->where('recentRequests.0.route', route('employee.leave.index'))
            ->where('recentRequests.1.type', 'Overtime')->where('recentRequests.1.date', 'Oct 16, 2026 · 2.50h')
            ->where('recentRequests.1.status', 'Approved')->where('recentRequests.1.route', route('employee.overtime.index'))
            ->where('recentRequests', fn ($rows) => collect($rows)->pluck('id')->unique()->count() === 5
                && !collect($rows)->contains('reason', 'Private outsider request')));
    }

    public function test_leave_date_ranges_keep_calendar_dates_and_type_labels(): void
    {
        foreach ([
            ['2026-10-15', '2026-10-17', 'Vacation', 'Oct 15–17, 2026', 'Vacation'],
            ['2026-10-31', '2026-11-02', 'Emergency', 'Oct 31–Nov 2, 2026', 'Emergency'],
            ['2026-12-31', '2027-01-02', 'Bereavement', 'Dec 31, 2026–Jan 2, 2027', 'Bereavement'],
        ] as [$start, $end, $type, $expectedDate, $expectedType]) {
            $request = $this->leave(date: $start);
            $request->update(['end_date' => $end, 'leave_type' => $type]);
            $this->get(route('employee.dashboard'))->assertInertia(fn (Assert $page) => $page
                ->where('recentRequests', fn ($rows) => collect($rows)->contains(fn ($row) =>
                    $row['id'] === 'leave-'.$request->id && $row['date'] === $expectedDate && $row['type'] === $expectedType)));
        }
    }

    public function test_attendance_is_personal_and_times_use_the_application_timezone(): void
    {
        Attendance::create(['employee_id' => $this->other->id, 'date' => today(), 'time_in' => now()]);
        Attendance::create(['employee_id' => $this->employee->id, 'date' => today()->subDay(), 'time_in' => now()->subDay()]);
        $this->getJson(route('employee.dashboard.data'))->assertJsonPath('attendance.status', 'not_started');
        $record = Attendance::create([
            'employee_id' => $this->employee->id, 'date' => today(), 'time_in' => today()->setTime(8, 5, 10),
        ]);
        $this->get(route('employee.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('attendance.status', 'working')->where('attendance.time_in', '08:05 AM')->where('attendance.total_hours', null));
        $record->update(['time_out' => today()->setTime(17, 35, 45)]);
        $this->getJson(route('employee.dashboard.data'))->assertOk()
            ->assertJsonPath('attendance.status', 'completed')->assertJsonPath('attendance.time_out', '05:35 PM')
            ->assertJsonPath('attendance.total_hours', '9h 30m');
        $this->get(route('employee.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('attendance.status', 'completed')->where('attendance.total_hours', '9h 30m'));
    }

    public function test_existing_attendance_actions_update_the_dashboard_for_the_current_employee(): void
    {
        $this->postJson(route('employee.attendance.timeIn'), ['employee_id' => $this->other->id])->assertOk();
        $this->postJson(route('employee.attendance.timeIn'))->assertOk();
        $this->assertSame(1, Attendance::where('employee_id', $this->employee->id)->count());
        $this->assertSame(0, Attendance::where('employee_id', $this->other->id)->count());
        $this->get(route('employee.dashboard'))->assertInertia(fn (Assert $page) => $page->where('attendance.status', 'working'));
        $this->travel(90)->minutes();
        $this->postJson(route('employee.attendance.timeOut'))->assertOk();
        $this->get(route('employee.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('attendance.status', 'completed')->where('attendance.total_hours', '1h 30m'));
    }

    public function test_missing_employee_record_returns_forbidden_for_page_and_data(): void
    {
        $this->employee->delete();
        $this->get(route('employee.dashboard'))->assertForbidden();
        $this->getJson(route('employee.dashboard.data'))->assertForbidden();
    }

    public function test_dashboard_permission_is_required_for_both_endpoints(): void
    {
        $this->employee->user->roles->first()->revokePermissionTo('dashboard.view.employee');
        $this->get(route('employee.dashboard'))->assertForbidden();
        $this->getJson(route('employee.dashboard.data'))->assertForbidden();
    }

    public function test_manager_cannot_access_the_employee_dashboard(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $this->actingAs($manager->user);
        $this->get(route('employee.dashboard'))->assertForbidden();
        $this->getJson(route('employee.dashboard.data'))->assertForbidden();
    }
}
