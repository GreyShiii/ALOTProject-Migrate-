<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = $this->makeUser('admin', 'admin@example.com');
        $this->actingAs($this->admin)->withoutVite();
    }

    private function makeUser(string $role, string $email): User
    {
        $user = User::create([
            'first_name' => ucfirst($role), 'last_name' => 'Test', 'email' => $email,
            'password' => 'test-password', 'status' => 'active',
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_users_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('admin.users.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Users/Index')
                ->where('actionUrl', route('admin.users.index'))->where('users.0.role', 'admin')
                ->missing('users.0.password'));

        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.users.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Users/Index');
    }

    public function test_user_details_include_employment_and_preserve_the_calendar_hire_date(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        Employee::create([
            'user_id' => $this->admin->id, 'department_id' => $department->id,
            'position' => 'Administrator', 'hire_date' => '2026-10-01',
        ]);
        $this->get(route('admin.users.index'))->assertInertia(fn (Assert $page) => $page
            ->where('users.0.employee.hire_date', '2026-10-01')
            ->where('users.0.employee.department.name', 'Engineering')
            ->where('users.0.employee.position', 'Administrator'));
    }

    public function test_employee_cannot_access_the_users_page(): void
    {
        $this->actingAs($this->makeUser('employee', 'employee@example.com'))
            ->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_without_users_permission_is_denied(): void
    {
        $this->admin->roles->first()->revokePermissionTo('users.manage');
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_editing_a_user_preserves_an_unchanged_password(): void
    {
        $user = $this->makeUser('employee', 'worker@example.com');
        $this->putJson(route('admin.users.update', $user), [
            'first_name' => 'Updated', 'last_name' => 'User', 'email' => $user->email,
            'password' => '', 'role' => 'manager', 'status' => 'active',
        ])->assertOk()->assertJsonPath('user.role', 'manager')->assertJsonPath('user.first_name', 'Updated');
        $this->assertTrue(Hash::check('test-password', $user->fresh()->password));
    }

    public function test_user_validation_errors_are_returned_as_json(): void
    {
        $user = $this->makeUser('employee', 'worker@example.com');
        $this->putJson(route('admin.users.update', $user), [
            'first_name' => 'Updated', 'last_name' => 'User', 'email' => $this->admin->email,
            'password' => '', 'role' => 'manager', 'status' => 'active',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_user_status_changes_preserve_self_deactivation_protection(): void
    {
        $user = $this->makeUser('employee', 'worker@example.com');
        $this->postJson(route('admin.users.deactivate', $user))->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'inactive']);
        $this->postJson(route('admin.users.activate', $user))->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'active']);
        $this->postJson(route('admin.users.deactivate', $this->admin))->assertUnprocessable();
    }

    public function test_admin_cannot_change_their_own_role_or_status_from_user_edit(): void
    {
        $data = [
            'first_name' => $this->admin->first_name,
            'last_name' => $this->admin->last_name,
            'email' => $this->admin->email,
            'password' => '',
            'role' => 'admin',
            'status' => 'inactive',
        ];

        $this->putJson(route('admin.users.update', $this->admin), $data)
            ->assertUnprocessable()->assertJsonValidationErrors('status');

        $data['status'] = 'active';
        $data['role'] = 'employee';
        $this->putJson(route('admin.users.update', $this->admin), $data)
            ->assertUnprocessable()->assertJsonValidationErrors('role');
    }
}
