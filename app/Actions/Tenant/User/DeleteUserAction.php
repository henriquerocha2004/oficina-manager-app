<?php

namespace App\Actions\Tenant\User;

use App\Exceptions\User\UserNotFoundException;
use App\Models\Tenant\User;

class DeleteUserAction
{
    public function __invoke(int $id): void
    {
        $user = User::query()->find($id);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        $user->delete();
    }
}
