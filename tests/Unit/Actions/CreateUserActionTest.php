<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\User\CreateUserAction;
use App\Dto\UserDto;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Models\Tenant\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    public function testCreatesUserWhenNotExists(): void
    {
        $userDto = UserDto::fromArray([
            'name' => 'John User',
            'email' => 'john.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $action = new CreateUserAction();
        $result = $action($userDto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertNotNull($result->ulid);
        $this->assertSame('ulid', $result->getAuthIdentifierName());
        $this->assertDatabaseHas('users', [
            'email' => 'john.user@example.com',
            'is_active' => true,
        ]);
    }

    public function testThrowsWhenUserAlreadyExists(): void
    {
        User::query()->create([
            'name' => 'Existing User',
            'email' => 'existing.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $userDto = UserDto::fromArray([
            'name' => 'Other User',
            'email' => 'existing.user@example.com',
            'role' => 'mechanic',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $this->expectException(UserAlreadyExistsException::class);

        (new CreateUserAction())($userDto);
    }

    public function testStoresAvatarInTenantMediaDiskWhenProvided(): void
    {
        config(['filesystems.tenant_media_disk' => 'tenant_media']);
        Storage::fake('tenant_media');

        $userDto = UserDto::fromArray([
            'name' => 'Avatar User',
            'email' => 'avatar.user@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $avatar = UploadedFile::fake()->image('avatar.png', 400, 400);

        $result = (new CreateUserAction())($userDto, $avatar);

        $this->assertNotNull($result->avatar_path);
        Storage::disk('tenant_media')->assertExists($result->avatar_path);
    }
}
