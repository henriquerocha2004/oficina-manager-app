<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Dto\VehicleDto;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Vehicle\UpdateVehicleAction;
use App\Exceptions\Vehicle\VehicleNotFoundException;
use App\Models\Tenant\Client;
use App\Models\Tenant\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateVehicleActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testUpdatesVehicleWhenFound(): void
    {
        $client = Client::factory()->create();

        $data = [
            'license_plate' => 'XYZ-5678',
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'client_id' => $client->id,
        ];

        $vehicle = Vehicle::create($data);

        $this->assertDatabaseHas('vehicle', ['license_plate' => $data['license_plate']]);

        $vehicleDto = VehicleDto::fromArray([
            'license_plate' => 'XYZ-5678',
            'brand' => 'Honda Updated',
            'model' => 'Civic',
            'year' => 2021,
            'client_id' => $client->id,
        ]);

        $ulid = Ulid::fromString($vehicle->id);

        $action = new UpdateVehicleAction();
        $action($vehicleDto, $ulid);

        $this->assertDatabaseHas('vehicle', ['id' => $vehicle->id, 'brand' => 'Honda Updated']);
    }

    public function testThrowsWhenNotFound(): void
    {
        $client = Client::factory()->create();

        $vehicleDto = VehicleDto::fromArray([
            'license_plate' => 'XYZ-5678',
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'client_id' => $client->id,
        ]);

        $this->expectException(VehicleNotFoundException::class);

        $ulid = Ulid::fromString((string) Ulid::generate());

        $action = new UpdateVehicleAction();
        $action($vehicleDto, $ulid);
    }
}
