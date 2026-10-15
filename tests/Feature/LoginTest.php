<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_renders_the_react_page(): void
    {
        $this->withoutVite()->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->where('loginUrl', route('login.attempt'))
                ->where('logoUrl', asset('images/alot-logo.png'))
                ->where('auth.user', null));
    }

    public function test_missing_credentials_are_returned_to_the_login_form(): void
    {
        $this->from('/login')->post('/login', [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_invalid_credentials_return_an_error_without_flashing_the_password(): void
    {
        Auth::shouldReceive('guard')->andReturnSelf();
        Auth::shouldReceive('check')->andReturn(false);
        Auth::shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('userResolver')->andReturn(fn () => null);
        Auth::shouldReceive('attempt')->once()->with([
            'email' => 'someone@example.com',
            'password' => 'incorrect-password',
        ])->andReturn(false);

        $this->from('/login')->post('/login', [
            'email' => 'someone@example.com',
            'password' => 'incorrect-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email')
            ->assertSessionHas('_old_input.email', 'someone@example.com')
            ->assertSessionMissing('_old_input.password');
    }
}
