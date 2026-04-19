<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\User\UpdateUserAction;
use App\Dto\UserDto;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\Tenant\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function testStoresAvatarInTenantMediaDiskWhenProvided(): void
    {
        config(['filesystems.tenant_media_disk' => 'tenant_media']);
        Storage::fake('tenant_media');

        $user = User::query()->create([
            'name' => 'Avatar User',
            'email' => 'avatar.update@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
        ]);

        $dto = UserDto::fromArray([
            'name' => 'Avatar User Updated',
            'email' => 'avatar.update@example.com',
            'role' => 'administrator',
            'is_active' => true,
        ]);

        $avatar = UploadedFile::fake()->image('avatar.png', 400, 400);

        (new UpdateUserAction())($dto, $user->id, $avatar);

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        Storage::disk('tenant_media')->assertExists($user->avatar_path);
    }

    public function testDeletesPreviousAvatarFromTenantMediaDiskWhenRemovingAvatar(): void
    {
        config(['filesystems.tenant_media_disk' => 'tenant_media']);
        Storage::fake('tenant_media');

        $oldAvatarPath = 'users/avatars/1/old-avatar.jpg';
        Storage::disk('tenant_media')->put($oldAvatarPath, 'avatar');

        $user = User::query()->create([
            'name' => 'Avatar User',
            'email' => 'avatar.remove@example.com',
            'role' => 'administrator',
            'password' => '12345678',
            'is_active' => true,
            'avatar_path' => $oldAvatarPath,
        ]);

        $dto = UserDto::fromArray([
            'name' => 'Avatar User',
            'email' => 'avatar.remove@example.com',
            'role' => 'administrator',
            'is_active' => true,
        ]);

        (new UpdateUserAction())($dto, $user->id, removeAvatar: true);

        $user->refresh();

        $this->assertNull($user->avatar_path);
        Storage::disk('tenant_media')->assertMissing($oldAvatarPath);
    }
}
