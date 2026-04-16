<?php

namespace Tests\Unit\Models;

use App\Models\Tenant\User;
use Illuminate\Contracts\Auth\UserProvider;
use Tests\TestCase;

class TenantUserAuthIdentifierTest extends TestCase
{
    public function testUsesUlidAsAuthenticationIdentifier(): void
    {
        $user = User::factory()->create();

        $this->assertSame('ulid', $user->getAuthIdentifierName());
        $this->assertSame($user->ulid, $user->getAuthIdentifier());
        $this->assertSame($user->id, $user->legacyId());
    }

    public function testProviderRetrievesTenantUsersByUlid(): void
    {
        $user = User::factory()->create();

        /** @var UserProvider $provider */
        $provider = app('auth')->createUserProvider('users');
        $restoredUser = $provider->retrieveById($user->ulid);

        $this->assertInstanceOf(User::class, $restoredUser);
        $this->assertTrue($restoredUser->is($user));
        $this->assertNull($provider->retrieveById((string) $user->id));
    }
}
