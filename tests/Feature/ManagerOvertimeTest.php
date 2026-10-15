<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ManagerOvertimeTest extends TestCase
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

    private function overtime(?Employee $employee = null, string $status = 'Pending'): OvertimeRequest
    {
        return OvertimeRequest::create([
            'employee_id' => ($employee ?? $this->worker)->id, 'hours' => 2.5,
            'date' => '2026-10-15', 'status' => $status, 'reason' => 'Release support',
        ]);
    }

    public function test_empty_overtime_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('manager.overtime.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Manager/Overtime/Index')->has('overtimeRequests', 0));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('manager.overtime.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Manager/Overtime/Index');
    }

    public function test_page_and_json_endpoint_only_include_the_managers_direct_team(): void
    {
        $this->overtime();
        $this->overtime(status: 'Approved');
        $this->overtime($this->outsider);
        $this->overtime($this->manager);
        $this->get(route('manager.overtime.index'))->assertInertia(fn (Assert $page) => $page
            ->has('overtimeRequests', 2)
            ->where('overtimeRequests', fn ($rows) => collect($rows)->every(fn ($row) => $row['employee_id'] === $this->worker->id))
            ->missing('overtimeRequests.0.employee.user.password'));
        $this->getJson(route('manager.overtime.data'))->assertOk()->assertJsonPath('success', true)
            ->assertJsonCount(2, 'overtimeRequests')->assertJsonPath('overtimeRequests.0.employee_id', $this->worker->id);
    }

    public function test_dates_review_information_and_action_urls_are_serialized_correctly(): void
    {
        $overtime = $this->overtime(status: 'Rejected');
        $overtime->update(['approved_by' => $this->manager->user_id, 'approval_date' => now(), 'rejection_reason' => 'No coverage']);
        $this->get(route('manager.overtime.index'))->assertInertia(fn (Assert $page) => $page
            ->where('overtimeRequests.0.date', '2026-10-15')->where('overtimeRequests.0.hours', '2.50')
            ->where('overtimeRequests.0.approver.email', 'manager@example.com')->where('overtimeRequests.0.rejection_reason', 'No coverage')
            ->where('overtimeRequests.0.employee.department.name', 'Engineering')
            ->where('overtimeRequests.0.approveUrl', route('manager.overtime.approve', $overtime))
            ->where('overtimeRequests.0.rejectUrl', route('manager.overtime.reject', $overtime))
            ->missing('overtimeRequests.0.approver.password'));
    }

    public function test_approval_records_the_current_manager_and_clears_the_old_rejection_reason(): void
    {
        $overtime = $this->overtime();
        $overtime->update(['rejection_reason' => 'Old reason']);
        $this->postJson(route('manager.overtime.approve', $overtime), ['approved_by' => $this->outsider->user_id])
            ->assertOk()->assertJsonPath('overtimeRequest.status', 'Approved')
            ->assertJsonPath('overtimeRequest.date', '2026-10-15');
        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtime->id, 'status' => 'Approved', 'approved_by' => $this->manager->user_id, 'rejection_reason' => null,
        ]);
        $this->assertNotNull($overtime->fresh()->approval_date);
    }

    public function test_requests_are_ordered_by_overtime_date_and_keep_fractional_hours(): void
    {
        $older = $this->overtime();
        $newer = $this->overtime();
        $newer->update(['date' => '2026-10-16', 'hours' => 0.5]);
        $this->get(route('manager.overtime.index'))->assertInertia(fn (Assert $page) => $page
            ->where('overtimeRequests.0.id', $newer->id)->where('overtimeRequests.0.hours', '0.50')
            ->where('overtimeRequests.1.id', $older->id));
        $this->getJson(route('manager.overtime.data'))->assertOk()
            ->assertJsonPath('overtimeRequests.0.date', '2026-10-16')->assertJsonPath('overtimeRequests.0.hours', '0.50')
            ->assertJsonPath('overtimeRequests.1.date', '2026-10-15');
    }

    public function test_rejection_requires_a_reason_and_records_the_current_manager(): void
    {
        $overtime = $this->overtime();
        $this->postJson(route('manager.overtime.reject', $overtime), [])->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        $this->postJson(route('manager.overtime.reject', $overtime), ['rejection_reason' => str_repeat('x', 501)])
            ->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        $this->assertSame('Pending', $overtime->fresh()->status);
        $this->postJson(route('manager.overtime.reject', $overtime), ['rejection_reason' => 'No coverage'])
            ->assertOk()->assertJsonPath('overtimeRequest.status', 'Rejected');
        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtime->id, 'approved_by' => $this->manager->user_id, 'rejection_reason' => 'No coverage',
        ]);
        $this->assertNotNull($overtime->fresh()->approval_date);
    }

    public function test_a_manager_cannot_review_outside_requests_or_their_own_request(): void
    {
        foreach ([$this->outsider, $this->manager] as $employee) {
            $overtime = $this->overtime($employee);
            $this->postJson(route('manager.overtime.approve', $overtime))->assertForbidden();
            $this->postJson(route('manager.overtime.reject', $overtime), ['rejection_reason' => 'No coverage'])->assertForbidden();
            $this->assertSame('Pending', $overtime->fresh()->status);
        }
    }

    public function test_already_reviewed_requests_cannot_be_reviewed_again(): void
    {
        foreach (['Approved', 'Rejected'] as $status) {
            $overtime = $this->overtime(status: $status);
            $this->postJson(route('manager.overtime.approve', $overtime))->assertUnprocessable();
            $this->postJson(route('manager.overtime.reject', $overtime), ['rejection_reason' => 'New reason'])->assertUnprocessable();
            $this->assertSame($status, $overtime->fresh()->status);
        }
    }

    public function test_manager_without_employee_record_gets_clear_errors(): void
    {
        $overtime = $this->overtime();
        $this->manager->delete();
        $this->get(route('manager.overtime.index'))->assertForbidden();
        $this->getJson(route('manager.overtime.data'))->assertNotFound();
        $this->postJson(route('manager.overtime.approve', $overtime))->assertForbidden();
        $this->postJson(route('manager.overtime.reject', $overtime), ['rejection_reason' => 'No coverage'])->assertForbidden();
    }

    public function test_review_permission_is_required_for_all_endpoints(): void
    {
        $overtime = $this->overtime();
        $this->manager->user->roles->first()->revokePermissionTo('overtime.review.team');
        $this->get(route('manager.overtime.index'))->assertForbidden();
        $this->getJson(route('manager.overtime.data'))->assertForbidden();
        $this->postJson(route('manager.overtime.approve', $overtime))->assertForbidden();
        $this->postJson(route('manager.overtime.reject', $overtime), ['rejection_reason' => 'No coverage'])->assertForbidden();
    }

    public function test_employee_cannot_review_requests(): void
    {
        $overtime = $this->overtime();
        $this->actingAs($this->worker->user);
        $this->get(route('manager.overtime.index'))->assertForbidden();
        $this->postJson(route('manager.overtime.approve', $overtime))->assertForbidden();
        $this->postJson(route('manager.overtime.reject', $overtime), ['rejection_reason' => 'No coverage'])->assertForbidden();
    }
}
