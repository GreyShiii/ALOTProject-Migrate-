<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeSubmissionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;
    private Employee $otherEmployee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $department = Department::create(['name' => 'Operations']);
        $this->employee = $this->makeEmployee('employee@example.com', $department->id);
        $this->otherEmployee = $this->makeEmployee('other@example.com', $department->id);
        $this->actingAs($this->employee->user);
    }

    private function makeEmployee(string $email, int $departmentId): Employee
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => $email,
            'password' => 'test-password',
            'status' => 'active',
        ]);
        $user->assignRole('employee');

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $departmentId,
            'position' => 'Staff member',
        ]);
    }

    public function test_leave_request_is_created_for_the_signed_in_employee_as_pending(): void
    {
        $this->postJson(route('employee.leave.store'), [
            'employee_id' => $this->otherEmployee->id,
            'leave_type' => 'Vacation',
            'start_date' => today()->toDateString(),
            'end_date' => today()->addDay()->toDateString(),
            'status' => 'Approved',
            'approved_by' => $this->otherEmployee->user_id,
        ])->assertOk();

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'status' => 'Pending',
            'approved_by' => null,
        ]);
    }

    public function test_overtime_request_is_created_for_the_signed_in_employee_as_pending(): void
    {
        $this->postJson(route('employee.overtime.store'), [
            'employee_id' => $this->otherEmployee->id,
            'date' => today()->toDateString(),
            'hours' => 2,
            'reason' => 'Release support',
            'status' => 'Approved',
        ])->assertOk();

        $this->assertDatabaseHas('overtime_requests', [
            'employee_id' => $this->employee->id,
            'status' => 'Pending',
            'hours' => 2,
        ]);
    }

    public function test_attendance_filters_reject_invalid_values(): void
    {
        $this->getJson(route('employee.attendance.index', ['date' => 'not-a-date']))
            ->assertUnprocessable()->assertJsonValidationErrors('date');

        $this->getJson(route('employee.attendance.index', ['status' => 'all-records']))
            ->assertUnprocessable()->assertJsonValidationErrors('status');
    }
}
