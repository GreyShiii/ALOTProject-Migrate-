<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ManagerLeaveTest extends TestCase
{
    use RefreshDatabase;

    private Employee $manager;
    private Employee $worker;
    private Employee $outsider;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->department = Department::create(['name' => 'Engineering']);
        $this->manager = $this->employee('manager@example.com', role: 'manager');
        $this->worker = $this->employee('worker@example.com', $this->manager->id);
        $this->outsider = $this->employee('outsider@example.com');
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

    private function leave(?Employee $employee = null, string $status = 'Pending'): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => ($employee ?? $this->worker)->id, 'leave_type' => 'Sick',
            'start_date' => '2026-10-15', 'end_date' => '2026-10-17', 'status' => $status, 'reason' => 'Recovery',
        ]);
    }

    public function test_empty_leave_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('manager.leave.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Manager/Leave/Index')->has('leaveRequests', 0));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('manager.leave.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Manager/Leave/Index');
    }

    public function test_page_and_json_endpoint_only_include_the_managers_direct_team(): void
    {
        $this->leave();
        $this->leave(status: 'Approved');
        $this->leave($this->outsider);
        $this->leave($this->manager);
        $this->get(route('manager.leave.index'))->assertInertia(fn (Assert $page) => $page
            ->has('leaveRequests', 2)
            ->where('leaveRequests', fn ($rows) => collect($rows)->every(fn ($row) => $row['employee_id'] === $this->worker->id))
            ->missing('leaveRequests.0.employee.user.password'));
        $this->getJson(route('manager.leave.data'))->assertOk()->assertJsonPath('success', true)
            ->assertJsonCount(2, 'leaveRequests')->assertJsonPath('leaveRequests.0.employee_id', $this->worker->id);
    }

    public function test_dates_review_information_and_action_urls_are_serialized_correctly(): void
    {
        $leave = $this->leave(status: 'Rejected');
        $leave->update(['approved_by' => $this->manager->user_id, 'approval_date' => now(), 'rejection_reason' => 'No coverage']);
        $this->get(route('manager.leave.index'))->assertInertia(fn (Assert $page) => $page
            ->where('leaveRequests.0.start_date', '2026-10-15')->where('leaveRequests.0.end_date', '2026-10-17')
            ->where('leaveRequests.0.approver.email', 'manager@example.com')->where('leaveRequests.0.rejection_reason', 'No coverage')
            ->where('leaveRequests.0.employee.department.name', 'Engineering')
            ->where('leaveRequests.0.approveUrl', route('manager.leave.approve', $leave))
            ->where('leaveRequests.0.rejectUrl', route('manager.leave.reject', $leave))
            ->missing('leaveRequests.0.approver.password'));
    }

    public function test_approval_records_the_current_manager_and_clears_the_old_rejection_reason(): void
    {
        $leave = $this->leave();
        $leave->update(['rejection_reason' => 'Old reason']);
        $this->postJson(route('manager.leave.approve', $leave), ['approved_by' => $this->outsider->user_id])
            ->assertOk()->assertJsonPath('leaveRequest.status', 'Approved')
            ->assertJsonPath('leaveRequest.start_date', '2026-10-15');
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id, 'status' => 'Approved', 'approved_by' => $this->manager->user_id, 'rejection_reason' => null,
        ]);
        $this->assertNotNull($leave->fresh()->approval_date);
    }

    public function test_rejection_requires_a_reason_and_records_the_current_manager(): void
    {
        $leave = $this->leave();
        $this->postJson(route('manager.leave.reject', $leave), [])->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => str_repeat('x', 501)])
            ->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        $this->assertSame('Pending', $leave->fresh()->status);
        $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => 'No coverage'])
            ->assertOk()->assertJsonPath('leaveRequest.status', 'Rejected');
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id, 'approved_by' => $this->manager->user_id, 'rejection_reason' => 'No coverage',
        ]);
        $this->assertNotNull($leave->fresh()->approval_date);
    }

    public function test_a_manager_cannot_review_outside_requests_or_their_own_request(): void
    {
        foreach ([$this->outsider, $this->manager] as $employee) {
            $leave = $this->leave($employee);
            $this->postJson(route('manager.leave.approve', $leave))->assertForbidden();
            $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => 'No coverage'])->assertForbidden();
            $this->assertSame('Pending', $leave->fresh()->status);
        }
    }

    public function test_already_reviewed_requests_cannot_be_reviewed_again(): void
    {
        foreach (['Approved', 'Rejected'] as $status) {
            $leave = $this->leave(status: $status);
            $this->postJson(route('manager.leave.approve', $leave))->assertUnprocessable();
            $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => 'New reason'])->assertUnprocessable();
            $this->assertSame($status, $leave->fresh()->status);
        }
    }

    public function test_manager_without_employee_record_gets_clear_errors(): void
    {
        $leave = $this->leave();
        $this->manager->delete();
        $this->get(route('manager.leave.index'))->assertForbidden();
        $this->getJson(route('manager.leave.data'))->assertNotFound();
        $this->postJson(route('manager.leave.approve', $leave))->assertForbidden();
        $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => 'No coverage'])->assertForbidden();
    }

    public function test_review_permission_is_required_for_all_endpoints(): void
    {
        $leave = $this->leave();
        $this->manager->user->roles->first()->revokePermissionTo('leave.review.team');
        $this->get(route('manager.leave.index'))->assertForbidden();
        $this->getJson(route('manager.leave.data'))->assertForbidden();
        $this->postJson(route('manager.leave.approve', $leave))->assertForbidden();
        $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => 'No coverage'])->assertForbidden();
    }

    public function test_employee_cannot_review_requests(): void
    {
        $leave = $this->leave();
        $this->actingAs($this->worker->user);
        $this->get(route('manager.leave.index'))->assertForbidden();
        $this->postJson(route('manager.leave.approve', $leave))->assertForbidden();
        $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => 'No coverage'])->assertForbidden();
    }
}
