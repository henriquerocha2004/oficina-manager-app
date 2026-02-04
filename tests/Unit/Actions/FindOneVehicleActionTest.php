<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Models\Tenant\Client;
use App\Models\Tenant\Vehicle;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Vehicle\FindOneAction;
use App\Exceptions\Vehicle\VehicleNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FindOneVehicleActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testFindsVehicleWhenExists(): void
    {
        $client = Client::factory()->create();

        $data = [
            'license_plate' => 'FND-1111',
            'brand' => 'Chevrolet',
            'model' => 'Onix',
            'year' => 2022,
            'client_id' => $client->id,
        ];

        $vehicle = Vehicle::create($data);

        $ulid = Ulid::fromString($vehicle->id);

        $action = new FindOneAction();
        $result = $action($ulid);

        $this->assertInstanceOf(Vehicle::class, $result);
        $this->assertEquals($vehicle->id, $result->id);
    }

    public function testThrowsWhenNotFound(): void
    {
        $this->expectException(VehicleNotFoundException::class);

        $ulid = Ulid::fromString((string) Ulid::generate());

        $action = new FindOneAction();
        $action($ulid);
    }
}
