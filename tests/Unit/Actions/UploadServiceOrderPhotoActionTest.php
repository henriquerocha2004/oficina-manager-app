<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\UploadServiceOrderPhotoAction;
use App\Dto\ServiceOrderPhotoDto;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadServiceOrderPhotoActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'tenant',
            'database.connections.tenant' => config('database.connections.tenant_test'),
        ]);

        DB::purge('tenant');
    }

    public function testStoresServiceOrderPhotoInTenantMediaDisk(): void
    {
        config(['filesystems.tenant_media_disk' => 'tenant_media']);
        Storage::fake('tenant_media');

        $userId = DB::connection('tenant')->table('users')->insertGetId([
            'name' => 'Photo Tester',
            'email' => 'photo.tester+' . Str::ulid() . '@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'administrator',
            'is_active' => true,
            'must_change_password' => false,
            'ulid' => (string) Str::ulid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $serviceOrder = ServiceOrder::on('tenant')->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $userId,
            'order_number' => '2026-' . random_int(1000, 9999),
            'status' => 'draft',
            'reported_problem' => 'Ruido na suspensao',
            'technical_diagnosis' => null,
            'observations' => null,
            'customer_complaint' => null,
            'total_parts' => 0,
            'total_services' => 0,
            'discount' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);

        $photoFile = UploadedFile::fake()->image('inspection.png', 1600, 900);

        $dto = new ServiceOrderPhotoDto(
            service_order_id: $serviceOrder->id,
            photo: $photoFile,
            uploaded_by: $userId,
        );

        $photo = (new UploadServiceOrderPhotoAction())($dto);

        $this->assertNotNull($photo->file_path);
        $this->assertSame('inspection.png', $photo->original_filename);
        Storage::disk('tenant_media')->assertExists($photo->file_path);
    }
}
