<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerWriteSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private Employee $managerEmployee;
    private Employee $teamMember;
    private Employee $otherEmployee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $department = Department::create(['name' => 'Operations']);
        $this->manager = $this->makeUser('manager@example.com', 'manager');
        $this->managerEmployee = $this->makeEmployee($this->manager, $department->id);
        $this->teamMember = $this->makeEmployee(
            $this->makeUser('team@example.com'),
            $department->id,
            $this->managerEmployee->id
        );
        $this->otherEmployee = $this->makeEmployee(
            $this->makeUser('other@example.com'),
            $department->id
        );

        $this->actingAs($this->manager);
    }

    private function makeUser(string $email, string $role = 'employee'): User
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Person',
            'email' => $email,
            'password' => 'test-password',
            'status' => 'active',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makeEmployee(User $user, int $departmentId, ?int $managerId = null): Employee
    {
        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $departmentId,
            'manager_id' => $managerId,
            'position' => 'Staff member',
        ]);
    }

    public function test_manager_can_approve_a_pending_leave_request_from_their_team(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->teamMember->id,
            'leave_type' => 'Sick',
            'start_date' => today(),
            'end_date' => today(),
        ]);

        $this->postJson(route('manager.leave.approve', $leave))->assertOk();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'Approved',
            'approved_by' => $this->manager->id,
        ]);
    }

    public function test_manager_cannot_review_a_leave_request_outside_their_team_or_review_it_twice(): void
    {
        $outsideLeave = LeaveRequest::create([
            'employee_id' => $this->otherEmployee->id,
            'leave_type' => 'Sick',
            'start_date' => today(),
            'end_date' => today(),
        ]);

        $this->postJson(route('manager.leave.approve', $outsideLeave))->assertForbidden();

        $alreadyReviewed = LeaveRequest::create([
            'employee_id' => $this->teamMember->id,
            'leave_type' => 'Sick',
            'start_date' => today(),
            'end_date' => today(),
            'status' => 'Approved',
        ]);

        $this->postJson(route('manager.leave.approve', $alreadyReviewed))->assertUnprocessable();
    }

    public function test_rejecting_requests_requires_a_reason_and_limits_overtime_review_to_the_team(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->teamMember->id,
            'leave_type' => 'Vacation',
            'start_date' => today(),
            'end_date' => today(),
        ]);

        $this->postJson(route('manager.leave.reject', $leave), [])
            ->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');

        $this->postJson(route('manager.leave.reject', $leave), ['rejection_reason' => 'Insufficient coverage.'])
            ->assertOk();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'Rejected',
            'rejection_reason' => 'Insufficient coverage.',
        ]);

        $outsideOvertime = OvertimeRequest::create([
            'employee_id' => $this->otherEmployee->id,
            'date' => today(),
            'hours' => 2,
        ]);

        $this->postJson(route('manager.overtime.approve', $outsideOvertime))->assertForbidden();
    }

    public function test_manager_attendance_filters_validate_input_and_only_return_their_team(): void
    {
        Attendance::create([
            'employee_id' => $this->teamMember->id,
            'date' => today(),
            'time_in' => now(),
        ]);
        Attendance::create([
            'employee_id' => $this->otherEmployee->id,
            'date' => today(),
            'time_in' => now(),
        ]);

        $this->getJson(route('manager.attendance.data'))
            ->assertOk()->assertJsonCount(1, 'attendances')
            ->assertJsonPath('attendances.0.employee_id', $this->teamMember->id);

        $this->getJson(route('manager.attendance.data', ['scope' => 'all']))
            ->assertUnprocessable()->assertJsonValidationErrors('scope');

        $this->getJson(route('manager.attendance.data', ['date' => 'not-a-date']))
            ->assertUnprocessable()->assertJsonValidationErrors('date');
    }
}
