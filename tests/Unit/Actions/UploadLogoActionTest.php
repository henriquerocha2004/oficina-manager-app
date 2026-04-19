<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Setting\UploadLogoAction;
use App\Models\Tenant\TenantSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadLogoActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.tenant' => config('database.connections.tenant_test'),
        ]);

        DB::purge('tenant');
    }

    public function testStoresLogoInTenantMediaDisk(): void
    {
        config(['filesystems.tenant_media_disk' => 'tenant_media']);
        Storage::fake('tenant_media');

        $logo = UploadedFile::fake()->image('logo.png', 600, 300);

        (new UploadLogoAction())($logo);

        $logoPath = TenantSetting::getValue('logo_path');

        $this->assertNotNull($logoPath);
        Storage::disk('tenant_media')->assertExists($logoPath);
    }

    public function testDeletesPreviousLogoFromTenantMediaDiskWhenRemoving(): void
    {
        config(['filesystems.tenant_media_disk' => 'tenant_media']);
        Storage::fake('tenant_media');

        $oldLogoPath = 'tenants/logos/old-logo.jpg';
        Storage::disk('tenant_media')->put($oldLogoPath, 'logo');
        TenantSetting::setValue('logo_path', $oldLogoPath);

        (new UploadLogoAction())(null);

        $this->assertNull(TenantSetting::getValue('logo_path'));
        Storage::disk('tenant_media')->assertMissing($oldLogoPath);
    }
}
