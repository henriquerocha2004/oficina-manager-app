<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Models\Tenant\Client;
use App\Models\Tenant\Vehicle;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Vehicle\DeleteVehicleAction;
use App\Exceptions\Vehicle\VehicleNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeleteVehicleActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testDeletesVehicleWhenFound(): void
    {
        $client = Client::factory()->create();

        $data = [
            'license_plate' => 'DEL-9999',
            'brand' => 'Ford',
            'model' => 'Focus',
            'year' => 2019,
            'client_id' => $client->id,
        ];

        $vehicle = Vehicle::create($data);

        $this->assertDatabaseHas('vehicle', ['license_plate' => $data['license_plate']]);

        $ulid = Ulid::fromString($vehicle->id);

        $action = new DeleteVehicleAction();
        $action($ulid);

        $this->assertSoftDeleted('vehicle', ['id' => $vehicle->id]);
    }

    public function testThrowsWhenNotFound(): void
    {
        $this->expectException(VehicleNotFoundException::class);

        $ulid = Ulid::fromString((string) Ulid::generate());

        $action = new DeleteVehicleAction();
        $action($ulid);
    }
}
