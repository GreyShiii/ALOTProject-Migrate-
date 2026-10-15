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

class EmployeeLeaveTest extends TestCase
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

    private function leave(?Employee $employee = null, string $status = 'Pending'): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => ($employee ?? $this->employee)->id, 'leave_type' => 'Sick',
            'start_date' => '2026-10-15', 'end_date' => '2026-10-17', 'status' => $status, 'reason' => 'Recovery',
        ]);
    }

    public function test_empty_leave_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('employee.leave.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Employee/Leave/Index')->has('leaves', 0)
                ->where('pendingCount', 0)->where('approvedCount', 0)->where('rejectedCount', 0)->where('totalCount', 0)
                ->where('storeUrl', route('employee.leave.store'))->where('canCreate', true));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('employee.leave.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Employee/Leave/Index');
    }

    public function test_page_and_json_counts_only_include_the_current_employees_requests(): void
    {
        foreach (['Pending', 'Approved', 'Rejected'] as $status) {
            $this->leave(status: $status);
            $this->leave($this->other, $status);
        }
        $this->get(route('employee.leave.index'))->assertInertia(fn (Assert $page) => $page->has('leaves', 3)
            ->where('pendingCount', 1)->where('approvedCount', 1)->where('rejectedCount', 1)->where('totalCount', 3)
            ->where('leaves', fn ($rows) => collect($rows)->every(fn ($row) => $row['employee_id'] === $this->employee->id)));
        $this->getJson(route('employee.leave.data', ['employee_id' => $this->other->id]))->assertOk()
            ->assertJsonCount(3, 'leaves')->assertJsonPath('success', true)->assertJsonPath('totalCount', 3)
            ->assertJsonPath('pendingCount', 1)->assertJsonPath('approvedCount', 1)->assertJsonPath('rejectedCount', 1)
            ->assertJsonPath('leaves.0.employee_id', $this->employee->id);
    }

    public function test_requests_are_newest_first_and_dates_and_review_details_are_preserved(): void
    {
        $older = $this->leave();
        $older->created_at = now()->subDay();
        $older->save();
        $manager = $this->employee('manager@example.com', 'manager');
        $reviewed = $this->leave(status: 'Rejected');
        $reviewed->update(['approved_by' => $manager->user_id, 'approval_date' => now(), 'rejection_reason' => 'No coverage']);
        $this->get(route('employee.leave.index'))->assertInertia(fn (Assert $page) => $page
            ->where('leaves.0.id', $reviewed->id)->where('leaves.1.id', $older->id)
            ->where('leaves.0.start_date', '2026-10-15')->where('leaves.0.end_date', '2026-10-17')
            ->where('leaves.0.approver.first_name', 'Manager')->where('leaves.0.rejection_reason', 'No coverage')
            ->missing('leaves.0.approver.password')->missing('leaves.0.approver.email'));
        $this->getJson(route('employee.leave.data'))->assertOk()->assertJsonPath('leaves.0.start_date', '2026-10-15')
            ->assertJsonPath('leaves.0.end_date', '2026-10-17')->assertJsonPath('leaves.0.rejection_reason', 'No coverage');
    }

    public function test_submission_only_sets_allowed_fields_for_the_current_employee(): void
    {
        $response = $this->postJson(route('employee.leave.store'), [
            'leave_type' => 'Vacation', 'start_date' => '2026-10-15', 'end_date' => '2026-10-17', 'reason' => 'Family visit',
            'employee_id' => $this->other->id, 'status' => 'Approved', 'approved_by' => $this->other->user_id,
            'approval_date' => now()->toDateTimeString(), 'rejection_reason' => 'Forged review',
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.status', 'Pending')
            ->assertJsonPath('data.start_date', '2026-10-15')->assertJsonPath('data.end_date', '2026-10-17');
        $this->assertDatabaseHas('leave_requests', [
            'id' => $response->json('data.id'), 'employee_id' => $this->employee->id, 'reason' => 'Family visit',
            'approved_by' => null, 'approval_date' => null, 'rejection_reason' => null,
        ]);
        $this->get(route('employee.leave.index'))->assertInertia(fn (Assert $page) => $page
            ->where('pendingCount', 1)->where('totalCount', 1)->has('leaves', 1));
    }

    public function test_all_supported_leave_types_and_single_day_requests_allow_an_optional_reason(): void
    {
        foreach (['Sick', 'Vacation', 'Emergency', 'Bereavement'] as $type) {
            $this->postJson(route('employee.leave.store'), [
                'leave_type' => $type, 'start_date' => '2026-10-15', 'end_date' => '2026-10-15',
            ])->assertOk()->assertJsonPath('data.leave_type', $type)->assertJsonPath('data.reason', null);
        }
        $this->assertDatabaseCount('leave_requests', 4);
    }

    public function test_invalid_type_dates_and_long_reason_return_field_errors_without_creating_requests(): void
    {
        $this->postJson(route('employee.leave.store'), [])->assertUnprocessable()
            ->assertJsonValidationErrors(['leave_type', 'start_date', 'end_date']);
        $this->postJson(route('employee.leave.store'), [
            'leave_type' => 'Invalid', 'start_date' => '2026-10-17', 'end_date' => '2026-10-15', 'reason' => str_repeat('x', 501),
        ])->assertUnprocessable()->assertJsonValidationErrors(['leave_type', 'end_date', 'reason']);
        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_missing_employee_record_returns_clear_errors(): void
    {
        $this->employee->delete();
        $this->get(route('employee.leave.index'))->assertForbidden();
        $this->getJson(route('employee.leave.data'))->assertNotFound();
        $this->postJson(route('employee.leave.store'), [])->assertNotFound();
    }

    public function test_view_permission_is_required_for_page_and_json(): void
    {
        $this->employee->user->roles->first()->revokePermissionTo('leave.view.own');
        $this->get(route('employee.leave.index'))->assertForbidden();
        $this->getJson(route('employee.leave.data'))->assertForbidden();
    }

    public function test_create_permission_is_separate_from_view_permission(): void
    {
        $this->employee->user->roles->first()->revokePermissionTo('leave.create.own');
        $this->get(route('employee.leave.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canCreate', false));
        $this->getJson(route('employee.leave.data'))->assertOk();
        $this->postJson(route('employee.leave.store'), [])->assertForbidden();
    }

    public function test_manager_cannot_access_employee_leave_endpoints(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $this->actingAs($manager->user);
        $this->get(route('employee.leave.index'))->assertForbidden();
        $this->getJson(route('employee.leave.data'))->assertForbidden();
        $this->postJson(route('employee.leave.store'), [])->assertForbidden();
    }
}
