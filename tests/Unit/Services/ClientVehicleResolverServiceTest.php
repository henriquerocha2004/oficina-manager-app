<?php

namespace Tests\Unit\Services;

use App\Actions\Tenant\Client\CreateClientAction;
use App\Actions\Tenant\Vehicle\CreateVehicleAction;
use App\Actions\Tenant\Vehicle\TransferVehicleOwnershipAction;
use App\Dto\ResolvedClientVehicle;
use App\Exceptions\ServiceOrder\VehicleOwnershipConflictException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\ClientVehicleResolverService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class ClientVehicleResolverServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ClientVehicleResolverService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ClientVehicleResolverService(
            new CreateClientAction,
            new CreateVehicleAction,
            new TransferVehicleOwnershipAction,
        );
    }

    public function test_resolves_existing_client_and_vehicle_same_owner(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
        ];

        $result = $this->service->resolve($data);

        $this->assertInstanceOf(ResolvedClientVehicle::class, $result);
        $this->assertEquals($client->id, $result->clientId);
        $this->assertEquals($vehicle->id, $result->vehicleId);
    }

    /**
     * @throws Throwable
     */
    public function test_creates_vehicle_for_existing_client(): void
    {
        $client = Client::factory()->create();

        $data = [
            'client_id' => $client->id,
            'new_vehicle' => [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'vehicle_type' => 'car',
                'year' => 2020,
            ],
        ];

        $result = $this->service->resolve($data);

        $this->assertEquals($client->id, $result->clientId);
        $this->assertDatabaseHas('vehicle', ['license_plate' => 'ABC-1234']);
        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $client->id,
            'current_owner' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_creates_client_and_transfers_vehicle(): void
    {
        $existingClient = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $existingClient->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'new_client' => [
                'name' => 'New Client',
                'email' => 'new@example.com',
                'document_number' => '52998224725',
                'phone' => '11999999999',
            ],
            'vehicle_id' => $vehicle->id,
            'transfer_vehicle' => true,
        ];

        $result = $this->service->resolve($data);

        $this->assertDatabaseHas('client', ['document_number' => '52998224725']);
        $this->assertEquals($vehicle->id, $result->vehicleId);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $existingClient->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => false,
        ]);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $result->clientId,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_creates_client_and_vehicle(): void
    {
        $data = [
            'new_client' => [
                'name' => 'New Client',
                'email' => 'new@example.com',
                'document_number' => '88888888888',
                'phone' => '11888888888',
            ],
            'new_vehicle' => [
                'license_plate' => 'XYZ-9876',
                'brand' => 'Honda',
                'model' => 'Civic',
                'vehicle_type' => 'car',
                'year' => 2021,
            ],
        ];

        $result = $this->service->resolve($data);

        $this->assertDatabaseHas('client', ['document_number' => '88888888888']);
        $this->assertDatabaseHas('vehicle', ['license_plate' => 'XYZ-9876']);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $result->clientId,
            'vehicle_id' => $result->vehicleId,
            'current_owner' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_transfers_vehicle_to_existing_client(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client1->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'client_id' => $client2->id,
            'vehicle_id' => $vehicle->id,
            'transfer_vehicle' => true,
        ];

        $result = $this->service->resolve($data);

        $this->assertEquals($client2->id, $result->clientId);
        $this->assertEquals($vehicle->id, $result->vehicleId);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $client1->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => false,
        ]);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $client2->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_throws_exception_when_transfer_not_confirmed(): void
    {
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client1->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'client_id' => $client2->id,
            'vehicle_id' => $vehicle->id,
        ];

        $this->expectException(VehicleOwnershipConflictException::class);

        $this->service->resolve($data);
    }

    /**
     * @throws Throwable
     */
    public function test_throws_exception_when_no_client_data(): void
    {
        $vehicle = Vehicle::factory()->create();

        $data = [
            'vehicle_id' => $vehicle->id,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Client data is required.');

        $this->service->resolve($data);
    }

    public function test_throws_exception_when_no_vehicle_data(): void
    {
        $client = Client::factory()->create();

        $data = [
            'client_id' => $client->id,
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Vehicle data is required.');

        $this->service->resolve($data);
    }
}
