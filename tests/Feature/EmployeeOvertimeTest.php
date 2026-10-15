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

class EmployeeOvertimeTest extends TestCase
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

    private function overtime(?Employee $employee = null, string $status = 'Pending', float $hours = 2.5): OvertimeRequest
    {
        return OvertimeRequest::create([
            'employee_id' => ($employee ?? $this->employee)->id, 'date' => '2026-10-15',
            'hours' => $hours, 'status' => $status, 'reason' => 'Release support',
        ]);
    }

    public function test_empty_overtime_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('employee.overtime.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Employee/Overtime/Index')->has('overtimeRequests', 0)
                ->where('pendingCount', 0)->where('approvedCount', 0)->where('rejectedCount', 0)->where('totalCount', 0)
                ->where('approvedHours', '0.00')->where('storeUrl', route('employee.overtime.store'))->where('canCreate', true));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('employee.overtime.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Employee/Overtime/Index');
    }

    public function test_page_and_json_summaries_only_include_the_current_employees_requests(): void
    {
        foreach (['Pending', 'Approved', 'Rejected'] as $status) {
            $this->overtime(status: $status);
            $this->overtime($this->other, $status, 6);
        }
        $this->overtime(status: 'Approved', hours: 0.5);
        $this->get(route('employee.overtime.index'))->assertInertia(fn (Assert $page) => $page->has('overtimeRequests', 4)
            ->where('pendingCount', 1)->where('approvedCount', 2)->where('rejectedCount', 1)->where('totalCount', 4)
            ->where('approvedHours', '3.00')
            ->where('overtimeRequests', fn ($rows) => collect($rows)->every(fn ($row) => $row['employee_id'] === $this->employee->id)));
        $this->getJson(route('employee.overtime.data', ['employee_id' => $this->other->id]))->assertOk()
            ->assertJsonCount(4, 'overtimeRequests')->assertJsonPath('success', true)->assertJsonPath('totalCount', 4)
            ->assertJsonPath('pendingCount', 1)->assertJsonPath('approvedCount', 2)->assertJsonPath('rejectedCount', 1)
            ->assertJsonPath('approvedHours', '3.00')->assertJsonPath('overtimeRequests.0.employee_id', $this->employee->id);
    }

    public function test_requests_are_ordered_by_overtime_date_and_preserve_hours_and_review_details(): void
    {
        $older = $this->overtime();
        $older->update(['date' => '2026-10-14']);
        $manager = $this->employee('manager@example.com', 'manager');
        $reviewed = $this->overtime(status: 'Rejected', hours: 0.5);
        $reviewed->update(['approved_by' => $manager->user_id, 'approval_date' => now(), 'rejection_reason' => 'No coverage']);
        $reviewed->created_at = now()->subDay();
        $reviewed->save();
        $this->get(route('employee.overtime.index'))->assertInertia(fn (Assert $page) => $page
            ->where('overtimeRequests.0.id', $reviewed->id)->where('overtimeRequests.1.id', $older->id)
            ->where('overtimeRequests.0.date', '2026-10-15')->where('overtimeRequests.0.hours', '0.50')
            ->where('overtimeRequests.0.approver.first_name', 'Manager')->where('overtimeRequests.0.rejection_reason', 'No coverage')
            ->missing('overtimeRequests.0.approver.password')->missing('overtimeRequests.0.approver.email'));
        $this->getJson(route('employee.overtime.data'))->assertOk()->assertJsonPath('overtimeRequests.0.date', '2026-10-15')
            ->assertJsonPath('overtimeRequests.0.hours', '0.50')->assertJsonPath('overtimeRequests.0.rejection_reason', 'No coverage');
    }

    public function test_submission_only_sets_allowed_fields_for_the_current_employee(): void
    {
        $response = $this->postJson(route('employee.overtime.store'), [
            'date' => '2026-10-15', 'hours' => 2.5, 'reason' => 'Release support',
            'employee_id' => $this->other->id, 'status' => 'Approved', 'approved_by' => $this->other->user_id,
            'approval_date' => now()->toDateTimeString(), 'rejection_reason' => 'Forged review',
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.status', 'Pending')
            ->assertJsonPath('data.date', '2026-10-15')->assertJsonPath('data.hours', '2.50');
        $this->assertDatabaseHas('overtime_requests', [
            'id' => $response->json('data.id'), 'employee_id' => $this->employee->id, 'reason' => 'Release support',
            'approved_by' => null, 'approval_date' => null, 'rejection_reason' => null,
        ]);
        $this->get(route('employee.overtime.index'))->assertInertia(fn (Assert $page) => $page
            ->where('pendingCount', 1)->where('totalCount', 1)->where('approvedHours', '0.00')->has('overtimeRequests', 1));
    }

    public function test_half_hour_and_six_hour_boundaries_allow_an_optional_reason(): void
    {
        foreach (['0.50', '6.00'] as $expected) {
            $this->postJson(route('employee.overtime.store'), [
                'date' => '2026-10-15', 'hours' => $expected,
            ])->assertOk()->assertJsonPath('data.hours', $expected)->assertJsonPath('data.reason', null);
        }
        $this->assertDatabaseCount('overtime_requests', 2);
    }

    public function test_invalid_dates_hours_and_long_reason_return_field_errors_without_creating_requests(): void
    {
        $this->postJson(route('employee.overtime.store'), [])->assertUnprocessable()->assertJsonValidationErrors(['date', 'hours']);
        $this->postJson(route('employee.overtime.store'), [
            'date' => 'invalid', 'hours' => 6.5, 'reason' => str_repeat('x', 501),
        ])->assertUnprocessable()->assertJsonValidationErrors(['date', 'hours', 'reason']);
        foreach ([0, 0.49, -1, 'not-a-number'] as $hours) {
            $this->postJson(route('employee.overtime.store'), ['date' => '2026-10-15', 'hours' => $hours])
                ->assertUnprocessable()->assertJsonValidationErrors('hours');
        }
        $this->assertDatabaseCount('overtime_requests', 0);
    }

    public function test_missing_employee_record_returns_clear_errors(): void
    {
        $this->employee->delete();
        $this->get(route('employee.overtime.index'))->assertNotFound();
        $this->getJson(route('employee.overtime.data'))->assertNotFound();
        $this->postJson(route('employee.overtime.store'), [])->assertNotFound();
    }

    public function test_view_permission_is_required_for_page_and_json(): void
    {
        $this->employee->user->roles->first()->revokePermissionTo('overtime.view.own');
        $this->get(route('employee.overtime.index'))->assertForbidden();
        $this->getJson(route('employee.overtime.data'))->assertForbidden();
    }

    public function test_create_permission_is_separate_from_view_permission(): void
    {
        $this->employee->user->roles->first()->revokePermissionTo('overtime.create.own');
        $this->get(route('employee.overtime.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canCreate', false));
        $this->getJson(route('employee.overtime.data'))->assertOk();
        $this->postJson(route('employee.overtime.store'), [])->assertForbidden();
    }

    public function test_manager_cannot_access_employee_overtime_endpoints(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $this->actingAs($manager->user);
        $this->get(route('employee.overtime.index'))->assertForbidden();
        $this->getJson(route('employee.overtime.data'))->assertForbidden();
        $this->postJson(route('employee.overtime.store'), [])->assertForbidden();
    }
}
