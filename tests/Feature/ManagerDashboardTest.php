<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Employee $manager;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
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
            'manager_id' => $managerId, 'position' => 'Developer', 'hire_date' => '2026-10-01',
        ]);
    }

    private function leave(Employee $employee, string $status = 'Pending'): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $employee->id, 'leave_type' => 'Sick', 'start_date' => '2026-10-15',
            'end_date' => '2026-10-17', 'status' => $status,
        ]);
    }

    private function overtime(Employee $employee, string $status = 'Pending'): OvertimeRequest
    {
        return OvertimeRequest::create([
            'employee_id' => $employee->id, 'date' => '2026-10-16', 'hours' => 2.5, 'status' => $status,
        ]);
    }

    public function test_empty_team_dashboard_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('manager.dashboard'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Manager/Dashboard')
                ->where('pendingLeaveCount', 0)->where('pendingOvertimeCount', 0)
                ->where('teamMemberCount', 0)->where('approvedRequestCount', 0)->has('pendingRequests', 0)
                ->where('links.leave', route('manager.leave.index'))
                ->where('links.overtime', route('manager.overtime.index'))
                ->where('links.team', route('manager.team.index')));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('manager.dashboard'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Manager/Dashboard');
    }

    public function test_counts_and_pending_requests_only_include_the_managers_team(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        $otherManager = $this->employee('other-manager@example.com', role: 'manager');
        $outsider = $this->employee('outsider@example.com', $otherManager->id);
        foreach (['Pending', 'Approved', 'Rejected'] as $status) {
            $this->leave($worker, $status);
            $this->overtime($worker, $status);
            $this->leave($outsider, $status);
            $this->overtime($outsider, $status);
        }
        $this->leave($this->manager);
        $this->overtime($this->manager);

        $this->get(route('manager.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('teamMemberCount', 1)->where('pendingLeaveCount', 1)->where('pendingOvertimeCount', 1)
            ->where('approvedRequestCount', 2)->has('pendingRequests', 2)
            ->where('pendingRequests', fn ($requests) => collect($requests)->every(fn ($row) => $row['employee']['id'] === $worker->id))
            ->missing('pendingRequests.0.employee.user.password'));
    }

    public function test_pending_requests_merge_both_types_order_by_submission_and_limit_to_five(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        for ($i = 0; $i < 7; $i++) {
            $request = $i % 2 === 0 ? $this->leave($worker) : $this->overtime($worker);
            $request->created_at = now()->subMinutes(7 - $i);
            $request->save();
        }
        $this->get(route('manager.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('pendingLeaveCount', 4)->where('pendingOvertimeCount', 3)->has('pendingRequests', 5)
            ->where('pendingRequests.0.type', 'Leave')->where('pendingRequests.0.details', 'Sick')
            ->where('pendingRequests.0.date', '2026-10-15')->where('pendingRequests.0.route', route('manager.leave.index'))
            ->where('pendingRequests.1.type', 'Overtime')->where('pendingRequests.1.details', '2.50 hours')
            ->where('pendingRequests.1.date', '2026-10-16')->where('pendingRequests.1.route', route('manager.overtime.index'))
            ->where('pendingRequests.0.employee.department.name', 'Engineering')
            ->where('pendingRequests', fn ($requests) => collect($requests)->pluck('id')->unique()->count() === 5));
    }

    public function test_manager_without_employee_record_gets_a_forbidden_response(): void
    {
        $this->manager->delete();
        $this->get(route('manager.dashboard'))->assertForbidden();
    }

    public function test_dashboard_permission_is_required(): void
    {
        $this->manager->user->roles->first()->revokePermissionTo('dashboard.view.manager');
        $this->get(route('manager.dashboard'))->assertForbidden();
    }

    public function test_employee_cannot_access_the_manager_dashboard(): void
    {
        $worker = $this->employee('worker@example.com', $this->manager->id);
        $this->actingAs($worker->user)->get(route('manager.dashboard'))->assertForbidden();
    }
}
