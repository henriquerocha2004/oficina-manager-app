<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Actions\Tenant\Vehicle\CreateVehicleAction;
use App\Dto\VehicleDto;
use App\Exceptions\Vehicle\VehicleAlreadyExistsException;
use App\Models\Tenant\Vehicle;
use App\Models\Tenant\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateVehicleActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreatesVehicleWhenNotExists(): void
    {
        $client = Client::factory()->create();

        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'client_id' => $client->id,
        ];

        $vehicleDto = VehicleDto::fromArray($data);

        $action = new CreateVehicleAction();
        $result = $action($vehicleDto);

        $this->assertInstanceOf(Vehicle::class, $result);
        $this->assertDatabaseHas('vehicle', ['license_plate' => $data['license_plate']]);
    }

    public function testThrowsWhenVehicleAlreadyExists(): void
    {
        $client = Client::factory()->create();

        $data = [
            'license_plate' => 'ABC-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'client_id' => $client->id,
        ];

        Vehicle::create($data);

        $vehicleDto = VehicleDto::fromArray($data);

        $this->expectException(VehicleAlreadyExistsException::class);

        (new CreateVehicleAction())($vehicleDto);
    }
}
