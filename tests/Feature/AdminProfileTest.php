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

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = $this->user('admin', 'admin@example.com');
        $this->actingAs($this->admin)->withoutVite();
    }

    private function user(string $role, string $email): User
    {
        $user = User::create([
            'first_name' => ucfirst($role), 'last_name' => 'Test', 'email' => $email,
            'password' => 'test-password', 'status' => 'active',
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_profile_without_employment_renders_react_on_direct_and_inertia_visits(): void
    {
        $response = $this->get(route('admin.profile.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Profile/Index')
                ->where('user.email', 'admin@example.com')->where('user.role', 'admin')
                ->where('employee', null)->missing('user.password')
                ->where('actionUrl', route('admin.profile.update'))
                ->where('passwordUrl', route('admin.profile.password.update')));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('admin.profile.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Admin/Profile/Index');
    }

    public function test_employment_includes_the_manager_department_and_unchanged_hire_date(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $manager = Employee::create([
            'user_id' => $this->user('manager', 'manager@example.com')->id,
            'department_id' => $department->id, 'position' => 'Lead',
        ]);
        Employee::create([
            'user_id' => $this->admin->id, 'department_id' => $department->id,
            'manager_id' => $manager->id, 'position' => 'Administrator', 'hire_date' => '2026-10-01',
        ]);
        $this->get(route('admin.profile.index'))->assertInertia(fn (Assert $page) => $page
            ->where('employee.hire_date', '2026-10-01')->where('employee.department.name', 'Engineering')
            ->where('employee.manager.user.email', 'manager@example.com')->missing('employee.manager.user.password'));
    }

    public function test_personal_information_updates_only_the_current_users_allowed_fields(): void
    {
        $other = $this->user('employee', 'other@example.com');
        $this->putJson(route('admin.profile.update'), [
            'first_name' => 'Updated', 'last_name' => 'Admin', 'email' => 'updated@example.com',
            'user_id' => $other->id, 'role' => 'employee', 'status' => 'inactive',
        ])->assertOk()->assertJsonPath('user.email', 'updated@example.com')->assertJsonMissingPath('user.password');
        $this->assertSame('Updated', $this->admin->fresh()->first_name);
        $this->assertTrue($this->admin->fresh()->hasRole('admin'));
        $this->assertSame('active', $this->admin->fresh()->status);
        $this->assertSame('other@example.com', $other->fresh()->email);
        $this->get(route('admin.profile.index'))->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.name', 'Updated Admin')->where('user.email', 'updated@example.com'));
    }

    public function test_duplicate_email_and_missing_names_return_field_errors(): void
    {
        $this->user('employee', 'other@example.com');
        $this->putJson(route('admin.profile.update'), [
            'first_name' => '', 'last_name' => '', 'email' => 'other@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_incorrect_current_password_is_rejected_without_changing_it(): void
    {
        $this->putJson(route('admin.profile.password.update'), [
            'current_password' => 'incorrect-password',
            'new_password' => 'new-password', 'new_password_confirmation' => 'new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertTrue(Hash::check('test-password', $this->admin->fresh()->password));
    }

    public function test_new_password_requires_confirmation_and_minimum_length(): void
    {
        $this->putJson(route('admin.profile.password.update'), [
            'current_password' => 'test-password', 'new_password' => 'new-password',
            'new_password_confirmation' => 'different-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('new_password');
        $this->putJson(route('admin.profile.password.update'), [
            'current_password' => 'test-password', 'new_password' => 'short', 'new_password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors('new_password');
    }

    public function test_valid_password_change_stores_a_hash_without_returning_the_password(): void
    {
        $this->putJson(route('admin.profile.password.update'), [
            'current_password' => 'test-password',
            'new_password' => 'new-password', 'new_password_confirmation' => 'new-password',
        ])->assertOk()->assertJsonPath('success', true)->assertJsonMissingPath('password');
        $this->assertTrue(Hash::check('new-password', $this->admin->fresh()->password));
        $this->assertFalse(Hash::check('test-password', $this->admin->fresh()->password));
    }

    public function test_profile_permission_is_required_for_all_admin_profile_endpoints(): void
    {
        $this->admin->roles->first()->revokePermissionTo('profile.update');
        $this->get(route('admin.profile.index'))->assertForbidden();
        $this->putJson(route('admin.profile.update'), [])->assertForbidden();
        $this->putJson(route('admin.profile.password.update'), [])->assertForbidden();
    }

    public function test_manager_cannot_access_admin_profile_but_other_profile_pages_still_render_blade(): void
    {
        $this->actingAs($this->user('manager', 'manager@example.com'));
        $this->get(route('admin.profile.index'))->assertForbidden();
        $this->get(route('manager.profile.index'))->assertOk()->assertViewIs('employee.profile.index');
        $this->actingAs($this->user('employee', 'employee@example.com'));
        $this->get(route('employee.profile.index'))->assertOk()->assertViewIs('employee.profile.index');
    }
}
