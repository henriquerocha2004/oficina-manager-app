<?php

namespace App\Actions\Tenant\Setting;

use App\Models\Tenant\TenantSetting;
use App\Support\MediaStorage;

class GetSettingsAction
{
    public function __invoke(): array
    {
        $logoPath = TenantSetting::getValue('logo_path');

        return [
            'logo_url' => MediaStorage::url($logoPath),
        ];
    }
}
