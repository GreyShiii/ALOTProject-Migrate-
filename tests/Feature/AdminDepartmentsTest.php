<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDepartmentsTest extends TestCase
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

    public function test_departments_render_react_on_direct_and_inertia_visits(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        Employee::create(['user_id' => $this->admin->id, 'department_id' => $department->id, 'position' => 'Administrator']);

        $response = $this->get(route('admin.departments.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Departments/Index')
                ->where('actionUrl', route('admin.departments.index'))
                ->where('departments.0.name', 'Engineering')->where('departments.0.employees_count', 1));

        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.departments.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Departments/Index');
    }

    public function test_empty_departments_are_a_valid_page(): void
    {
        $this->get(route('admin.departments.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('departments', 0));
    }

    public function test_create_edit_and_delete_continue_to_accept_json(): void
    {
        $response = $this->postJson(route('admin.departments.store'), ['name' => 'Support'])
            ->assertCreated()->assertJsonPath('department.name', 'Support');
        $id = $response->json('department.id');
        $this->putJson(route('admin.departments.update', $id), ['name' => 'Customer support'])
            ->assertOk()->assertJsonPath('department.name', 'Customer support');
        $this->deleteJson(route('admin.departments.destroy', $id))->assertOk();
        $this->assertDatabaseMissing('departments', ['id' => $id]);
    }

    public function test_required_and_duplicate_names_return_field_errors(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $this->postJson(route('admin.departments.store'), ['name' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson(route('admin.departments.store'), ['name' => 'Engineering'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->putJson(route('admin.departments.update', $department), ['name' => 'Engineering'])->assertOk();
    }

    public function test_assigned_departments_cannot_be_deleted(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        Employee::create(['user_id' => $this->admin->id, 'department_id' => $department->id, 'position' => 'Administrator']);
        $this->deleteJson(route('admin.departments.destroy', $department))->assertConflict()
            ->assertJsonPath('message', 'This department has employees and cannot be deleted.');
        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_department_permission_is_still_required(): void
    {
        $this->admin->roles->first()->revokePermissionTo('departments.manage');
        $this->get(route('admin.departments.index'))->assertForbidden();
        $this->postJson(route('admin.departments.store'), ['name' => 'Support'])->assertForbidden();
    }

    public function test_employee_cannot_access_departments(): void
    {
        $this->admin->syncRoles('employee');
        $this->get(route('admin.departments.index'))->assertForbidden();
    }
}
