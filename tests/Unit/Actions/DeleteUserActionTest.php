<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\User\DeleteUserAction;
use App\Exceptions\User\UserNotFoundException;
use App\Models\Tenant\User;
use Tests\TestCase;

class DeleteUserActionTest extends TestCase
{
    public function testDeletesUserWhenFound(): void
    {
        $user = User::query()->create([
            'name' => 'Delete User',
            'email' => 'delete.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        (new DeleteUserAction())($user->id);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function testThrowsWhenNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);

        (new DeleteUserAction())(999999);
    }
}
