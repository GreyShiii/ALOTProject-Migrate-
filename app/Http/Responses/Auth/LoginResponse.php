<?php

namespace App\Http\Responses\Auth;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        $dashboard = match (true) {
            $user->hasRole('admin') => route('admin.dashboard'),
            $user->hasRole('manager') => route('manager.dashboard'),
            default => route('employee.dashboard'),
        };

        $destination = redirect()->intended($dashboard)->getTargetUrl();

        if ($request->header('X-Inertia')) {
            return Inertia::location($destination);
        }

        return redirect()->to($destination);
    }
}
