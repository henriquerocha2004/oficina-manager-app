<?php

namespace App\Actions\Tenant\Setting;

use App\Models\Tenant\TenantSetting;
use App\Support\MediaStorage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class UploadLogoAction
{
    public function __invoke(?UploadedFile $logoFile): void
    {
        $oldLogoPath = TenantSetting::getValue('logo_path');

        if ($logoFile === null) {
            TenantSetting::setValue('logo_path', null);

            MediaStorage::delete($oldLogoPath);

            return;
        }

        $filename = uniqid('logo_', true) . '.jpg';
        $relativePath = "tenants/logos/{$filename}";

        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($logoFile->getRealPath());
        $image->scaleDown(width: 512);
        $image->fillTransparentAreas('ffffff');

        $encodedImage = $image->encode(new JpegEncoder(quality: 85));
        MediaStorage::put($relativePath, (string) $encodedImage);

        TenantSetting::setValue('logo_path', $relativePath);

        MediaStorage::delete($oldLogoPath);
    }
}
