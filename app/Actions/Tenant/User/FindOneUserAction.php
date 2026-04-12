<?php

namespace App\Actions\Tenant\User;

use App\Exceptions\User\UserNotFoundException;
use App\Models\Tenant\User;

class FindOneUserAction
{
    public function __invoke(int $id): User
    {
        $user = User::query()->find($id);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
