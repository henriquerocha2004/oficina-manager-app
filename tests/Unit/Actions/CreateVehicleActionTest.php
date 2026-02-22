<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Vehicle\CreateVehicleAction;
use App\Dto\VehicleDto;
use App\Models\Tenant\Client;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class CreateVehicleActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     */
    public function testCreatesVehicleWhenNotExists(): void
    {
        $client = Client::factory()->create();

        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
        ];

        $vehicleDto = VehicleDto::fromArray($data);

        $action = new CreateVehicleAction;
        $result = $action($vehicleDto, $client->id);

        $this->assertInstanceOf(Vehicle::class, $result);
        $this->assertDatabaseHas('vehicle', ['license_plate' => $data['license_plate']]);
        $this->assertDatabaseHas('client_vehicle', [
            'vehicle_id' => $result->id,
            'client_id' => $client->id,
            'current_owner' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_reuses_vehicle_when_already_exists(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();

        $existingVehicle = Vehicle::factory()->create(['license_plate' => 'XYZ-9999']);

        ClientVehicle::query()->create([
            'client_id' => $client1->id,
            'vehicle_id' => $existingVehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'license_plate' => 'XYZ-9999',
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
        ];

        $vehicleDto = VehicleDto::fromArray($data);

        $action = new CreateVehicleAction();
        $result = $action($vehicleDto, $client2->id);

        $this->assertEquals($existingVehicle->id, $result->id);

        $vehicleCount = Vehicle::query()
            ->where('license_plate', 'XYZ-9999')
            ->count();

        $this->assertEquals(1, $vehicleCount);
        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $client1->id,
            'vehicle_id' => $existingVehicle->id,
            'current_owner' => false,
        ]);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $client2->id,
            'vehicle_id' => $existingVehicle->id,
            'current_owner' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testThrowsWhenVehicleAlreadyExists(): void
    {
        $client = Client::factory()->create();

        $data = [
            'license_plate' => 'DEF-5678',
            'brand' => 'Ford',
            'model' => 'Focus',
            'year' => 2019,
        ];

        $vehicleDto = VehicleDto::fromArray($data);

        $action = new CreateVehicleAction;
        $result = $action($vehicleDto, $client->id);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $client->id,
            'vehicle_id' => $result->id,
            'current_owner' => true,
        ]);

        $relationCount = ClientVehicle::query()
            ->where('vehicle_id', $result->id)
            ->where('current_owner', true)
            ->count();

        $this->assertEquals(1, $relationCount);
    }
}
