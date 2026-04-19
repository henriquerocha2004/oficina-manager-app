<?php

namespace App\Actions\Tenant\Setting;

use App\Models\Tenant\TenantSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

            if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            return;
        }

        $filename = uniqid('logo_', true) . '.jpg';
        $relativePath = "tenants/logos/{$filename}";

        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($logoFile->getRealPath());
        $image->scaleDown(width: 512);
        $image->fillTransparentAreas('ffffff');

        $encodedImage = $image->encode(new JpegEncoder(quality: 85));
        Storage::disk('public')->put($relativePath, (string) $encodedImage);

        TenantSetting::setValue('logo_path', $relativePath);

        if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
            Storage::disk('public')->delete($oldLogoPath);
        }
    }
}
