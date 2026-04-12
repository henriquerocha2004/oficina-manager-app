<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\User\FindOneUserAction;
use App\Exceptions\User\UserNotFoundException;
use App\Models\Tenant\User;
use Tests\TestCase;

class FindOneUserActionTest extends TestCase
{
    public function testReturnsUserWhenFound(): void
    {
        $user = User::query()->create([
            'name' => 'User Find',
            'email' => 'find.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $result = (new FindOneUserAction())($user->id);

        $this->assertSame($user->id, $result->id);
        $this->assertSame('find.user@example.com', $result->email);
    }

    public function testThrowsWhenNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);

        (new FindOneUserAction())(999999);
    }
}
