<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\DeleteServiceOrderPhotoAction;
use App\Exceptions\ServiceOrder\ServiceOrderPhotoNotFoundException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderPhoto;
use App\Models\Tenant\Vehicle;
use App\Support\MediaStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeleteServiceOrderPhotoActionTest extends TestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'tenant',
            'database.connections.tenant' => config('database.connections.tenant_test'),
        ]);

        DB::purge('tenant');

        $this->userId = DB::connection('tenant')->table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test+' . Str::ulid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'administrator',
            'is_active' => true,
            'must_change_password' => false,
            'ulid' => (string) Str::ulid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createServiceOrder(): ServiceOrder
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        return ServiceOrder::on('tenant')->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $this->userId,
            'order_number' => '2026-' . random_int(1000, 9999),
            'status' => 'draft',
            'reported_problem' => null,
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
    }

    public function testDeletesPhotoWhenFound(): void
    {
        $serviceOrder = $this->createServiceOrder();

        $this->mock(MediaStorage::class)
            ->shouldReceive('delete')
            ->andReturn(true);

        $photo = ServiceOrderPhoto::query()->create([
            'service_order_id' => $serviceOrder->id,
            'file_path' => 'service-orders/photos/test.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
            'uploaded_by' => $this->userId,
        ]);

        $action = new DeleteServiceOrderPhotoAction();
        $action($serviceOrder->id, $photo->id);

        $this->assertSoftDeleted('service_order_photos', ['id' => $photo->id]);
    }

    public function testThrowsExceptionWhenPhotoNotFound(): void
    {
        $serviceOrder = $this->createServiceOrder();

        $action = new DeleteServiceOrderPhotoAction();

        $this->expectException(ServiceOrderPhotoNotFoundException::class);

        $action($serviceOrder->id, 'non-existent-photo-id');
    }

    public function testThrowsExceptionWhenPhotoDoesNotBelongToServiceOrder(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $anotherServiceOrder = $this->createServiceOrder();

        $photo = ServiceOrderPhoto::query()->create([
            'service_order_id' => $anotherServiceOrder->id,
            'file_path' => 'service-orders/photos/other.jpg',
            'original_filename' => 'other.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
            'uploaded_by' => $this->userId,
        ]);

        $action = new DeleteServiceOrderPhotoAction();

        $this->expectException(ServiceOrderPhotoNotFoundException::class);

        $action($serviceOrder->id, $photo->id);
    }
}
