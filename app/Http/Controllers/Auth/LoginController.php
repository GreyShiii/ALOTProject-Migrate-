<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'loginUrl' => route('login.attempt'),
            'logoUrl' => asset('images/alot-logo.png'),
        ]);
    }

    /**
     * Handle a login attempt.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Your account has been deactivated. Please contact your administrator.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $redirect = redirect()->intended($this->redirectPath($user));

        // Dashboards still render Blade, so leave Inertia with a full page visit.
        return $request->header('X-Inertia')
            ? Inertia::location($redirect->getTargetUrl())
            : $redirect;
    }

    /**
     * Determine where to redirect based on role.
     */
    protected function redirectPath($user): string
    {
        return match (true) {
            $user->hasRole('admin') => route('admin.dashboard'),
            $user->hasRole('manager') => route('manager.dashboard'),
            default => route('employee.dashboard'),
        };
    }

    /**
     * Log the user out.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
