<?php

namespace App\Support;

use RuntimeException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class MediaStorage
{
    public static function diskName(): string
    {
        return (string) config('filesystems.tenant_media_disk', 'public');
    }

    public static function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(self::diskName());

        return $disk;
    }

    public static function put(string $path, string $contents): void
    {
        $written = self::disk()->put($path, $contents);

        if ($written === false) {
            throw new RuntimeException("Unable to write file at location: {$path}.");
        }
    }

    public static function size(string $path): int
    {
        return self::disk()->size($path);
    }

    public static function exists(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        return self::disk()->exists($path);
    }

    public static function delete(?string $path): void
    {
        if (!self::exists($path)) {
            return;
        }

        self::disk()->delete($path);
    }

    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk = self::disk();

        if ((bool) config('filesystems.tenant_media_url_temporary', true) && $disk->providesTemporaryUrls()) {
            return $disk->temporaryUrl(
                $path,
                now()->addMinutes((int) config('filesystems.tenant_media_url_expiration', 1440))
            );
        }

        return $disk->url($path);
    }
}
