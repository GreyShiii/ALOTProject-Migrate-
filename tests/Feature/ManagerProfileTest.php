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

class ManagerProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = $this->user('manager', 'manager@example.com');
        $this->actingAs($this->manager)->withoutVite();
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
        $response = $this->get(route('manager.profile.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Manager/Profile/Index')
                ->where('user.email', 'manager@example.com')->where('user.role', 'manager')
                ->where('employee', null)->missing('user.password')
                ->where('actionUrl', route('manager.profile.update'))
                ->where('passwordUrl', route('manager.profile.password.update')));
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $response->viewData('page')['version']])
            ->get(route('manager.profile.index'))->assertOk()->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Manager/Profile/Index');
    }

    public function test_employment_includes_the_manager_department_and_unchanged_hire_date(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $manager = Employee::create([
            'user_id' => $this->user('manager', 'reporting-manager@example.com')->id,
            'department_id' => $department->id, 'position' => 'Lead',
        ]);
        $employee = Employee::create([
            'user_id' => $this->manager->id, 'department_id' => $department->id,
            'manager_id' => $manager->id, 'position' => 'Team Lead', 'hire_date' => '2026-10-01',
        ]);
        $this->get(route('manager.profile.index'))->assertInertia(fn (Assert $page) => $page
            ->where('employee.hire_date', '2026-10-01')->where('employee.department.name', 'Engineering')
            ->where('employee.manager.user.email', 'reporting-manager@example.com')->missing('employee.manager.user.password'));
        $this->putJson(route('manager.profile.update'), [
            'first_name' => 'Manager', 'last_name' => 'Test', 'email' => 'manager@example.com',
            'position' => 'Changed', 'hire_date' => '2026-10-10', 'manager_id' => null,
        ])->assertOk()->assertJsonPath('employee.id', $employee->id)
            ->assertJsonPath('employee.manager.user.email', 'reporting-manager@example.com')
            ->assertJsonMissingPath('employee.manager.user.password');
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id, 'manager_id' => $manager->id, 'position' => 'Team Lead',
        ]);
        $this->assertSame('2026-10-01', $employee->fresh()->hire_date->format('Y-m-d'));
    }

    public function test_personal_information_updates_only_the_current_users_allowed_fields(): void
    {
        $other = $this->user('employee', 'other@example.com');
        $this->putJson(route('manager.profile.update'), [
            'first_name' => 'Updated', 'last_name' => 'Manager', 'email' => 'updated@example.com',
            'user_id' => $other->id, 'role' => 'employee', 'status' => 'inactive',
        ])->assertOk()->assertJsonPath('user.email', 'updated@example.com')->assertJsonMissingPath('user.password');
        $this->assertSame('Updated', $this->manager->fresh()->first_name);
        $this->assertTrue($this->manager->fresh()->hasRole('manager'));
        $this->assertSame('active', $this->manager->fresh()->status);
        $this->assertSame('other@example.com', $other->fresh()->email);
        $this->get(route('manager.profile.index'))->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.name', 'Updated Manager')->where('user.email', 'updated@example.com'));
    }

    public function test_duplicate_email_and_missing_names_return_field_errors(): void
    {
        $this->user('employee', 'other@example.com');
        $this->putJson(route('manager.profile.update'), [
            'first_name' => '', 'last_name' => '', 'email' => 'other@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_incorrect_current_password_is_rejected_without_changing_it(): void
    {
        $this->putJson(route('manager.profile.password.update'), [
            'current_password' => 'incorrect-password',
            'new_password' => 'new-password', 'new_password_confirmation' => 'new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertTrue(Hash::check('test-password', $this->manager->fresh()->password));
    }

    public function test_new_password_requires_confirmation_and_minimum_length(): void
    {
        $this->putJson(route('manager.profile.password.update'), [
            'current_password' => 'test-password', 'new_password' => 'new-password',
            'new_password_confirmation' => 'different-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('new_password');
        $this->putJson(route('manager.profile.password.update'), [
            'current_password' => 'test-password', 'new_password' => 'short', 'new_password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors('new_password');
    }

    public function test_valid_password_change_stores_a_hash_without_returning_the_password(): void
    {
        $this->putJson(route('manager.profile.password.update'), [
            'current_password' => 'test-password',
            'new_password' => 'new-password', 'new_password_confirmation' => 'new-password',
        ])->assertOk()->assertJsonPath('success', true)->assertJsonMissingPath('password');
        $this->assertTrue(Hash::check('new-password', $this->manager->fresh()->password));
        $this->assertFalse(Hash::check('test-password', $this->manager->fresh()->password));
    }

    public function test_profile_permission_is_required_for_all_manager_profile_endpoints(): void
    {
        $this->manager->roles->first()->revokePermissionTo('profile.update');
        $this->get(route('manager.profile.index'))->assertForbidden();
        $this->putJson(route('manager.profile.update'), [])->assertForbidden();
        $this->putJson(route('manager.profile.password.update'), [])->assertForbidden();
    }

    public function test_employee_cannot_access_any_manager_profile_endpoint(): void
    {
        $this->actingAs($this->user('employee', 'employee@example.com'));
        $this->get(route('manager.profile.index'))->assertForbidden();
        $this->putJson(route('manager.profile.update'), [])->assertForbidden();
        $this->putJson(route('manager.profile.password.update'), [])->assertForbidden();
    }
}
