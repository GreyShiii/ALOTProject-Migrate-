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

class AdminOvertimeTest extends TestCase
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

    public function test_empty_overtime_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('admin.overtime.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Overtime/Index')
                ->has('overtimeRequests', 0)->has('departments', 0));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.overtime.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Overtime/Index');
    }

    public function test_details_preserve_dates_fractional_hours_relationships_and_rejection_reason(): void
    {
        $employee = $this->employee();
        OvertimeRequest::create([
            'employee_id' => $employee->id, 'date' => '2026-10-15', 'hours' => 2.5,
            'reason' => 'Release support', 'status' => 'Rejected', 'approved_by' => $this->admin->id,
            'approval_date' => '2026-10-14 10:00:00', 'rejection_reason' => 'No overtime required',
        ]);
        $this->get(route('admin.overtime.index'))->assertInertia(fn (Assert $page) => $page
            ->where('overtimeRequests.0.date', '2026-10-15')->where('overtimeRequests.0.hours', '2.50')
            ->where('overtimeRequests.0.employee.department.name', 'Engineering')
            ->where('overtimeRequests.0.employee.position', 'Administrator')
            ->where('overtimeRequests.0.reason', 'Release support')->where('overtimeRequests.0.status', 'Rejected')
            ->where('overtimeRequests.0.approver.email', 'admin@example.com')
            ->where('overtimeRequests.0.rejection_reason', 'No overtime required')
            ->missing('overtimeRequests.0.employee.user.password')->missing('overtimeRequests.0.approver.password'));
    }

    public function test_requests_are_ordered_by_date_and_pending_requests_allow_no_reviewer(): void
    {
        $employee = $this->employee();
        OvertimeRequest::create(['employee_id' => $employee->id, 'date' => '2026-10-20', 'hours' => 0.5]);
        OvertimeRequest::create(['employee_id' => $employee->id, 'date' => '2026-10-15', 'hours' => 2]);
        $this->get(route('admin.overtime.index'))->assertInertia(fn (Assert $page) => $page
            ->has('overtimeRequests', 2)->where('overtimeRequests.0.date', '2026-10-20')
            ->where('overtimeRequests.0.hours', '0.50')->where('overtimeRequests.0.status', 'Pending')
            ->where('overtimeRequests.0.approver', null)->where('overtimeRequests.0.reason', null)
            ->where('overtimeRequests.1.date', '2026-10-15'));
    }

    public function test_existing_json_endpoint_remains_available(): void
    {
        $employee = $this->employee();
        OvertimeRequest::create(['employee_id' => $employee->id, 'date' => '2026-10-15', 'hours' => 2]);
        $this->getJson(route('admin.overtime.data'))->assertOk()
            ->assertJsonPath('success', true)->assertJsonCount(1, 'overtimeRequests')
            ->assertJsonPath('overtimeRequests.0.employee.department.name', 'Engineering');
    }

    public function test_overtime_permission_is_required_for_both_endpoints(): void
    {
        $this->admin->roles->first()->revokePermissionTo('overtime.view.all');
        $this->get(route('admin.overtime.index'))->assertForbidden();
        $this->getJson(route('admin.overtime.data'))->assertForbidden();
    }

    public function test_manager_cannot_access_admin_overtime_pages(): void
    {
        $this->admin->syncRoles('manager');
        $this->get(route('admin.overtime.index'))->assertForbidden();
        $this->getJson(route('admin.overtime.data'))->assertForbidden();
    }
}
