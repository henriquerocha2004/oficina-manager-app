<?php

namespace App\Actions\Tenant\User;

use App\Dto\UserDto;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\Tenant\User;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class UpdateUserAction
{
    public function __invoke(
        UserDto $userDto,
        int $userId,
        ?UploadedFile $avatarFile = null,
        bool $removeAvatar = false
    ): void {
        $user = User::query()->find($userId);

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        $emailAlreadyExists = User::query()
            ->where('email', $userDto->email)
            ->where('id', '!=', $user->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($emailAlreadyExists) {
            throw new UserAlreadyExistsException();
        }

        $user->update($userDto->toArray());

        if ($removeAvatar) {
            $oldAvatarPath = $user->avatar_path;
            $user->update(['avatar_path' => null]);

            MediaStorage::delete($oldAvatarPath);

            return;
        }

        if (is_null($avatarFile)) {
            return;
        }

        $oldAvatarPath = $user->avatar_path;
        $newAvatarPath = $this->storeAvatar(
            avatarFile: $avatarFile,
            userId: $user->id,
        );

        $user->update(['avatar_path' => $newAvatarPath]);

        MediaStorage::delete($oldAvatarPath);
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
        MediaStorage::put($relativePath, (string) $encodedImage);

        return $relativePath;
    }
}
