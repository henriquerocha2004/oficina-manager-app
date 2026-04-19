<?php

namespace Tests\Unit\Actions\Tenant;

use App\Actions\Tenant\User\ChangePasswordAction;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordActionTest extends TestCase
{
    public function testSetsNewPassword(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
            'must_change_password' => true,
        ]);

        $oldHash = $user->getAttributes()['password'];

        (new ChangePasswordAction())($user, 'new-password-123');

        $user->refresh();

        $this->assertNotSame($oldHash, $user->getAttributes()['password']);
        $this->assertTrue(Hash::check('new-password-123', $user->getAttributes()['password']));
    }

    public function testClearsMustChangePasswordFlag(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        (new ChangePasswordAction())($user, 'new-password-123');

        $user->refresh();

        $this->assertFalse($user->must_change_password);
    }

    public function testDoesNotAffectOtherFields(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@test.com',
            'must_change_password' => true,
        ]);

        (new ChangePasswordAction())($user, 'new-password-123');

        $user->refresh();

        $this->assertSame('Original Name', $user->name);
        $this->assertSame('original@test.com', $user->email);
    }
}
