<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    /**
     * Validate and save a user's new password.
     *
     * @param array<string, mixed> $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ])->validate();

        // The User model's hashed cast encrypts this plain-text value on save.
        $user->forceFill([
            'password' => $input['password'],
        ])->save();
    }
}
