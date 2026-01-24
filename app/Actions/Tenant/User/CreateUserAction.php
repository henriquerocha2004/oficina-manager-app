<?php

namespace App\Actions\Tenant\User;

use App\Dto\UserDto;
use App\Models\Tenant\User;

class CreateUserAction
{
    public function __invoke(UserDto $userDto): User
    {
        return User::query()->create([
            'name' => $userDto->name,
            'email' => $userDto->email,
            'password' => $userDto->password,
        ]);
    }
}
