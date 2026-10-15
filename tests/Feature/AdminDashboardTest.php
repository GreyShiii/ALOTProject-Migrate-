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

class AdminDashboardTest extends TestCase
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

    private function employee(string $email, string $role = 'employee'): Employee
    {
        $user = User::create([
            'first_name' => 'Example', 'last_name' => 'User', 'email' => $email,
            'password' => 'test-password', 'status' => 'active',
        ]);
        $user->assignRole($role);

        return Employee::create([
            'user_id' => $user->id, 'department_id' => Department::firstOrCreate(['name' => 'Engineering'])->id,
            'position' => 'Developer', 'hire_date' => '2026-10-01',
        ]);
    }

    public function test_empty_dashboard_renders_react_with_zero_counts(): void
    {
        $response = $this->get(route('admin.dashboard'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard')
                ->where('totalEmployees', 0)->where('totalManagers', 0)->where('totalDepartments', 0)
                ->where('workingEmployees', 0)->where('completedEmployees', 0)->where('employeesOnLeave', 0)
                ->has('recentEmployees', 0)->has('recentRequests', 0));

        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.dashboard'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Dashboard');
    }

    public function test_overview_counts_today_and_distinct_approved_leave(): void
    {
        $this->employee('manager@example.com', 'manager');
        $working = $this->employee('working@example.com');
        $completed = $this->employee('completed@example.com');
        $onLeave = $this->employee('leave@example.com');
        Attendance::create(['employee_id' => $working->id, 'date' => today(), 'time_in' => now()]);
        Attendance::create(['employee_id' => $completed->id, 'date' => today(), 'time_in' => now()->subHours(2), 'time_out' => now()]);
        Attendance::create(['employee_id' => $onLeave->id, 'date' => today()->subDay(), 'time_in' => now()->subDay()]);
        foreach (['Approved', 'Approved', 'Pending', 'Rejected'] as $status) {
            LeaveRequest::create([
                'employee_id' => $onLeave->id, 'leave_type' => 'Sick', 'start_date' => today()->subDay(),
                'end_date' => today()->addDay(), 'status' => $status,
            ]);
        }
        $this->get(route('admin.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('totalEmployees', 3)->where('totalManagers', 1)->where('totalDepartments', 1)
            ->where('workingEmployees', 1)->where('completedEmployees', 1)->where('employeesOnLeave', 1));
    }

    public function test_recent_requests_merge_both_types_in_order_and_preserve_calendar_dates(): void
    {
        $employee = $this->employee('worker@example.com');
        for ($i = 0; $i < 7; $i++) {
            $request = $i % 2 === 0
                ? LeaveRequest::create(['employee_id' => $employee->id, 'leave_type' => 'Sick', 'start_date' => '2026-10-15', 'end_date' => '2026-10-17'])
                : OvertimeRequest::create(['employee_id' => $employee->id, 'date' => '2026-10-16', 'hours' => 2]);
            $request->created_at = now()->subMinutes(7 - $i);
            $request->save();
        }
        $this->get(route('admin.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->has('recentRequests', 5)
            ->where('recentRequests.0.type', 'Leave')->where('recentRequests.0.date', '2026-10-15')
            ->where('recentRequests.1.type', 'Overtime')->where('recentRequests.1.date', '2026-10-16')
            ->where('recentRequests.0.employee.department.name', 'Engineering')
            ->missing('recentRequests.0.employee.user.password')
            ->where('recentEmployees.0.hire_date', '2026-10-01')
            ->missing('recentEmployees.0.user.password'));
    }

    public function test_recent_employees_are_limited_to_the_latest_five(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $employee = $this->employee("worker{$i}@example.com");
            $employee->created_at = now()->subMinutes(7 - $i);
            $employee->save();
        }
        $this->get(route('admin.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->has('recentEmployees', 5)->where('recentEmployees.0.user.email', 'worker6@example.com'));
    }

    public function test_dashboard_permission_is_required(): void
    {
        $this->admin->roles->first()->revokePermissionTo('dashboard.view.admin');
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_non_admin_cannot_access_the_dashboard(): void
    {
        $this->admin->syncRoles('employee');
        $this->get(route('admin.dashboard'))->assertForbidden();
    }
}
