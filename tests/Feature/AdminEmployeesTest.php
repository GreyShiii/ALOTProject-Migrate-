<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminEmployeesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::create([
            'first_name' => 'Admin', 'last_name' => 'Test', 'email' => 'admin@example.com',
            'password' => 'test-password', 'status' => 'active',
        ]);
        $this->admin->assignRole('admin');
        $this->department = Department::create(['name' => 'Engineering']);
        $this->actingAs($this->admin)->withoutVite();
    }

    private function employee(string $email, string $role = 'employee', ?int $managerId = null): Employee
    {
        $user = User::create([
            'first_name' => 'Example', 'last_name' => 'User', 'email' => $email,
            'password' => 'test-password', 'status' => 'active',
        ]);
        $user->assignRole($role);

        return Employee::create([
            'user_id' => $user->id, 'department_id' => $this->department->id,
            'manager_id' => $managerId, 'position' => 'Developer', 'hire_date' => '2026-10-01',
        ]);
    }

    private function formData(Employee $employee): array
    {
        return [
            'first_name' => $employee->user->first_name, 'last_name' => $employee->user->last_name,
            'email' => $employee->user->email, 'password' => '', 'role' => $employee->user->primaryRoleName(),
            'department_id' => $employee->department_id, 'manager_id' => $employee->manager_id,
            'position' => $employee->position, 'hire_date' => $employee->hire_date->format('Y-m-d'),
        ];
    }

    public function test_empty_page_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('admin.employees.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Employees/Index')
                ->has('employees', 0)->has('managers', 0)->has('departments', 1)
                ->where('actionUrl', route('admin.employees.index')));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.employees.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Employees/Index');
    }

    public function test_page_supplies_roles_relationships_and_calendar_dates_without_passwords(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $worker = $this->employee('worker@example.com', 'employee', $manager->id);
        $this->get(route('admin.employees.index'))->assertInertia(fn (Assert $page) => $page
            ->where('employees.1.hire_date', '2026-10-01')->where('employees.1.user.role', 'employee')
            ->where('employees.1.department.name', 'Engineering')->where('employees.1.manager.user.email', 'manager@example.com')
            ->missing('employees.1.user.password')->has('managers', 1));
        $this->getJson(route('admin.employees.show', $worker))->assertOk()
            ->assertJsonPath('employee.hire_date', '2026-10-01')->assertJsonMissingPath('employee.user.password');
    }

    public function test_create_and_edit_preserve_hire_date_and_optional_password(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $data = [
            'first_name' => 'New', 'last_name' => 'Employee', 'email' => 'new@example.com',
            'password' => 'test-password', 'role' => 'employee', 'department_id' => $this->department->id,
            'manager_id' => $manager->id, 'position' => 'Analyst', 'hire_date' => '2026-10-01',
        ];
        $response = $this->postJson(route('admin.employees.store'), $data)->assertCreated()
            ->assertJsonPath('employee.hire_date', '2026-10-01')->assertJsonPath('employee.user.role', 'employee');
        $id = $response->json('employee.id');
        $this->putJson(route('admin.employees.update', $id), [...$data, 'password' => '', 'position' => 'Senior analyst'])
            ->assertOk()->assertJsonPath('employee.hire_date', '2026-10-01')
            ->assertJsonPath('employee.position', 'Senior analyst');
        $this->assertTrue(Hash::check('test-password', Employee::findOrFail($id)->user->password));
        $this->assertDatabaseHas('employees', ['id' => $id, 'manager_id' => $manager->id]);
        $this->assertSame('2026-10-01', Employee::findOrFail($id)->hire_date->format('Y-m-d'));
    }

    public function test_invalid_employee_fields_return_json_validation_errors(): void
    {
        $this->postJson(route('admin.employees.store'), [])->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password', 'department_id', 'position', 'role']);
        $employee = $this->employee('worker@example.com');
        $this->putJson(route('admin.employees.update', $employee), [...$this->formData($employee), 'email' => $this->admin->email])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_demoting_a_manager_removes_subordinate_assignments(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $worker = $this->employee('worker@example.com', 'employee', $manager->id);
        $this->putJson(route('admin.employees.update', $manager), [...$this->formData($manager), 'role' => 'employee'])
            ->assertOk()->assertJsonPath('employee.user.role', 'employee');
        $this->assertNull($worker->fresh()->manager_id);
        $this->get(route('admin.employees.index'))->assertInertia(fn (Assert $page) => $page->has('managers', 0));
    }

    public function test_delete_removes_the_account_and_clears_subordinate_assignments(): void
    {
        $manager = $this->employee('manager@example.com', 'manager');
        $worker = $this->employee('worker@example.com', 'employee', $manager->id);
        $this->deleteJson(route('admin.employees.destroy', $manager))->assertOk();
        $this->assertDatabaseMissing('employees', ['id' => $manager->id]);
        $this->assertDatabaseMissing('users', ['id' => $manager->user_id]);
        $this->assertNull($worker->fresh()->manager_id);
    }

    public function test_employee_with_history_cannot_be_deleted(): void
    {
        $employee = $this->employee('worker@example.com');
        Attendance::create([
            'employee_id' => $employee->id,
            'date' => today(),
            'time_in' => now(),
        ]);

        $this->deleteJson(route('admin.employees.destroy', $employee))
            ->assertConflict();

        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
        $this->assertDatabaseHas('users', ['id' => $employee->user_id]);
    }

    public function test_leave_or_overtime_history_also_prevents_employee_deletion(): void
    {
        $employee = $this->employee('leave-history@example.com');
        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'Sick',
            'start_date' => today(),
            'end_date' => today(),
        ]);

        $this->deleteJson(route('admin.employees.destroy', $employee))->assertConflict();

        $anotherEmployee = $this->employee('overtime-history@example.com');
        OvertimeRequest::create([
            'employee_id' => $anotherEmployee->id,
            'date' => today(),
            'hours' => 1,
        ]);

        $this->deleteJson(route('admin.employees.destroy', $anotherEmployee))->assertConflict();
    }

    public function test_only_users_with_the_manager_role_can_be_assigned_as_managers(): void
    {
        $employee = $this->employee('not-manager@example.com');
        $data = [
            'first_name' => 'New', 'last_name' => 'Person', 'email' => 'new-person@example.com',
            'password' => 'test-password', 'role' => 'employee', 'department_id' => $this->department->id,
            'manager_id' => $employee->id, 'position' => 'Analyst', 'hire_date' => null,
        ];

        $this->postJson(route('admin.employees.store'), $data)
            ->assertUnprocessable()->assertJsonValidationErrors('manager_id');

        $this->putJson(route('admin.employees.update', $employee), [
            ...$this->formData($employee),
            'manager_id' => $employee->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('manager_id');
    }

    public function test_employee_management_permission_is_required(): void
    {
        $this->admin->roles->first()->revokePermissionTo('employees.manage');
        $this->get(route('admin.employees.index'))->assertForbidden();
        $this->postJson(route('admin.employees.store'), [])->assertForbidden();
    }

    public function test_non_admin_cannot_access_employees(): void
    {
        $this->admin->syncRoles('employee');
        $this->get(route('admin.employees.index'))->assertForbidden();
    }
}
