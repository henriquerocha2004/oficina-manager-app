<?php

namespace App\Actions\Tenant\User;

use App\Models\Tenant\User;

class ChangePasswordAction
{
    public function __invoke(User $user, string $newPassword): void
    {
        $user->update([
            'password' => $newPassword,
            'must_change_password' => false,
        ]);
    }
}
