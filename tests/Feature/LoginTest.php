<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeUser(string $email, string $role = 'employee', string $status = 'active'): User
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => 'correct-password',
            'status' => $status,
        ]);

        if ($role !== '') {
            $user->assignRole($role);
        }

        return $user;
    }

    public function test_login_renders_the_react_page(): void
    {
        $this->withoutVite()->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->where('loginUrl', route('login.store'))
                ->where('logoUrl', asset('images/alot-logo.png'))
                ->where('auth.user', null));
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_missing_credentials_are_returned_to_the_login_form(): void
    {
        $this->from('/login')->post('/login', [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_invalid_credentials_return_an_error_without_flashing_the_password(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'someone@example.com',
            'password' => 'incorrect-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email')
            ->assertSessionHas('_old_input.email', 'someone@example.com')
            ->assertSessionMissing('_old_input.password');
    }

    public function test_active_user_can_login_and_is_sent_to_their_role_dashboard(): void
    {
        $user = $this->makeUser('manager@example.com', 'manager');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('manager.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = $this->makeUser('logout@example.com');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->makeUser('inactive@example.com', 'employee', 'inactive');

        $this->from('/login')->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_user_is_logged_out_of_an_existing_session(): void
    {
        $user = $this->makeUser('session@example.com', 'employee', 'inactive');

        $this->actingAs($user)->get(route('employee.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_without_an_application_role_cannot_login(): void
    {
        $user = $this->makeUser('roleless@example.com', '');

        $this->from('/login')->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'missing@example.com',
                'password' => 'incorrect-password',
            ]);
        }

        $this->post(route('login.store'), [
            'email' => 'missing@example.com',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_forgot_password_page_and_reset_link_flow_work(): void
    {
        Notification::fake();
        $user = $this->makeUser('reset@example.com');

        $this->withoutVite()->get('/forgot-password')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/ForgotPassword'));

        $this->from('/forgot-password')->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect('/forgot-password')->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_page_and_password_update_work(): void
    {
        $user = $this->makeUser('reset@example.com');
        $token = Password::createToken($user);

        $this->withoutVite()->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/ResetPassword')
                ->where('email', $user->email)
                ->where('token', $token));

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
