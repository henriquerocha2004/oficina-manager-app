<?php

namespace App\Actions\Tenant\Setting;

use App\Models\Tenant\TenantSetting;

class GetSettingsAction
{
    public function __invoke(): array
    {
        $logoPath = TenantSetting::getValue('logo_path');

        return [
            'logo_url' => $logoPath ? '/storage/' . $logoPath : null,
        ];
    }
}
