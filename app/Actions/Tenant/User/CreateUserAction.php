<?php

namespace App\Actions\Tenant\User;

use App\Dto\UserDto;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Models\Tenant\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class CreateUserAction
{
    public function __invoke(UserDto $userDto, ?UploadedFile $avatarFile = null): User
    {
        $user = User::query()
            ->where('email', $userDto->email)
            ->whereNull('deleted_at')
            ->first();

        throw_if(!is_null($user), UserAlreadyExistsException::class);

        $createdUser = User::query()->create([
            'name' => $userDto->name,
            'email' => $userDto->email,
            'password' => $userDto->password,
            'role' => $userDto->role,
            'is_active' => $userDto->is_active ?? true,
        ]);

        if (is_null($avatarFile)) {
            return $createdUser;
        }

        $avatarPath = $this->storeAvatar(
            avatarFile: $avatarFile,
            userId: $createdUser->id,
        );

        $createdUser->update(['avatar_path' => $avatarPath]);

        return $createdUser->refresh();
    }

    private function storeAvatar(UploadedFile $avatarFile, int $userId): string
    {
        $filename = uniqid('avatar_', true) . '.jpg';
        $relativePath = "users/avatars/{$userId}/{$filename}";

        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($avatarFile->getRealPath());
        $image->scaleDown(width: 1024);
        $image->fillTransparentAreas('ffffff');

        $encodedImage = $image->encode(new JpegEncoder(quality: 85));
        Storage::disk('public')->put($relativePath, (string) $encodedImage);

        return $relativePath;
    }
}
