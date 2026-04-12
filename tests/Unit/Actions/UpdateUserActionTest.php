<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\User\UpdateUserAction;
use App\Dto\UserDto;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\Tenant\User;
use Tests\TestCase;

class UpdateUserActionTest extends TestCase
{
    public function testUpdatesUserWhenFound(): void
    {
        $user = User::query()->create([
            'name' => 'Original Name',
            'email' => 'original.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $dto = UserDto::fromArray([
            'name' => 'Updated Name',
            'email' => 'updated.user@example.com',
            'role' => 'reception',
            'is_active' => false,
        ]);

        (new UpdateUserAction())($dto, $user->id);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated.user@example.com',
            'role' => 'reception',
            'is_active' => false,
        ]);
    }

    public function testThrowsWhenEmailAlreadyExists(): void
    {
        $target = User::query()->create([
            'name' => 'Target User',
            'email' => 'target.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        User::query()->create([
            'name' => 'Existing User',
            'email' => 'existing.email@example.com',
            'role' => 'reception',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $dto = UserDto::fromArray([
            'name' => 'Target User Updated',
            'email' => 'existing.email@example.com',
            'role' => 'mechanic',
            'is_active' => true,
        ]);

        $this->expectException(UserAlreadyExistsException::class);

        (new UpdateUserAction())($dto, $target->id);
    }

    public function testThrowsWhenNotFound(): void
    {
        $dto = UserDto::fromArray([
            'name' => 'Any User',
            'email' => 'any.user@example.com',
            'role' => 'administrator',
            'is_active' => true,
        ]);

        $this->expectException(UserNotFoundException::class);

        (new UpdateUserAction())($dto, 999999);
    }
}
