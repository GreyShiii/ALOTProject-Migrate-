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

class AdminLeaveTest extends TestCase
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

    public function test_empty_leave_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('admin.leave.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Leave/Index')
                ->has('leaveRequests', 0)->has('departments', 0));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.leave.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Leave/Index');
    }

    public function test_leave_details_preserve_calendar_dates_relationships_and_rejection_reason(): void
    {
        $employee = $this->employee();
        LeaveRequest::create([
            'employee_id' => $employee->id, 'leave_type' => 'Vacation',
            'start_date' => '2026-10-15', 'end_date' => '2026-10-17', 'reason' => 'Family trip',
            'status' => 'Rejected', 'approved_by' => $this->admin->id,
            'approval_date' => '2026-10-14 10:00:00', 'rejection_reason' => 'Insufficient coverage',
        ]);
        $this->get(route('admin.leave.index'))->assertInertia(fn (Assert $page) => $page
            ->where('leaveRequests.0.start_date', '2026-10-15')->where('leaveRequests.0.end_date', '2026-10-17')
            ->where('leaveRequests.0.employee.department.name', 'Engineering')
            ->where('leaveRequests.0.employee.position', 'Administrator')
            ->where('leaveRequests.0.reason', 'Family trip')->where('leaveRequests.0.status', 'Rejected')
            ->where('leaveRequests.0.approver.email', 'admin@example.com')
            ->where('leaveRequests.0.rejection_reason', 'Insufficient coverage')
            ->missing('leaveRequests.0.employee.user.password')->missing('leaveRequests.0.approver.password'));
    }

    public function test_requests_are_latest_first_and_pending_requests_allow_no_reviewer(): void
    {
        $employee = $this->employee();
        $older = LeaveRequest::create([
            'employee_id' => $employee->id, 'leave_type' => 'Sick', 'start_date' => '2026-10-01', 'end_date' => '2026-10-01',
        ]);
        $older->created_at = now()->subDay();
        $older->save();
        LeaveRequest::create([
            'employee_id' => $employee->id, 'leave_type' => 'Emergency', 'start_date' => '2026-10-15', 'end_date' => '2026-10-15',
        ]);
        $this->get(route('admin.leave.index'))->assertInertia(fn (Assert $page) => $page
            ->has('leaveRequests', 2)->where('leaveRequests.0.leave_type', 'Emergency')
            ->where('leaveRequests.0.status', 'Pending')->where('leaveRequests.0.approver', null)
            ->where('leaveRequests.0.reason', null)->where('leaveRequests.1.leave_type', 'Sick'));
    }

    public function test_existing_json_endpoint_remains_available(): void
    {
        $employee = $this->employee();
        LeaveRequest::create([
            'employee_id' => $employee->id, 'leave_type' => 'Sick', 'start_date' => '2026-10-15', 'end_date' => '2026-10-15',
        ]);
        $this->getJson(route('admin.leave.data'))->assertOk()
            ->assertJsonPath('success', true)->assertJsonCount(1, 'leaveRequests')
            ->assertJsonPath('leaveRequests.0.employee.department.name', 'Engineering');
    }

    public function test_leave_permission_is_required_for_both_endpoints(): void
    {
        $this->admin->roles->first()->revokePermissionTo('leave.view.all');
        $this->get(route('admin.leave.index'))->assertForbidden();
        $this->getJson(route('admin.leave.data'))->assertForbidden();
    }

    public function test_manager_cannot_access_admin_leave_pages(): void
    {
        $this->admin->syncRoles('manager');
        $this->get(route('admin.leave.index'))->assertForbidden();
        $this->getJson(route('admin.leave.data'))->assertForbidden();
    }
}
