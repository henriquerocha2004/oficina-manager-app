<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Vehicle\TransferVehicleOwnershipAction;
use App\Exceptions\Vehicle\VehicleNotFoundException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransferVehicleOwnershipActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_throws_when_vehicle_not_found(): void
    {
        $client = Client::factory()->create();
        $action = new TransferVehicleOwnershipAction;

        $this->expectException(VehicleNotFoundException::class);

        $action('non-existent-id', $client->id);
    }

    public function test_transfers_ownership_to_new_client(): void
    {
        $oldOwner = Client::factory()->create(['name' => 'João Silva']);
        $newOwner = Client::factory()->create(['name' => 'Maria Santos']);
        $vehicle = Vehicle::factory()->create(['license_plate' => 'ABC-1234']);

        ClientVehicle::create([
            'client_id' => $oldOwner->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $action = new TransferVehicleOwnershipAction;
        $result = $action($vehicle->id, $newOwner->id);

        $this->assertInstanceOf(ClientVehicle::class, $result);
        $this->assertEquals($newOwner->id, $result->client_id);
        $this->assertTrue($result->current_owner);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $oldOwner->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => false,
        ]);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $newOwner->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);
    }

    public function test_reuses_existing_relation_when_transferring_to_former_owner(): void
    {
        $firstOwner = Client::factory()->create(['name' => 'João']);
        $secondOwner = Client::factory()->create(['name' => 'Maria']);
        $vehicle = Vehicle::factory()->create(['license_plate' => 'DEF-5678']);

        ClientVehicle::create([
            'client_id' => $firstOwner->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => false,
        ]);

        ClientVehicle::create([
            'client_id' => $secondOwner->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $action = new TransferVehicleOwnershipAction;
        $result = $action($vehicle->id, $firstOwner->id);

        $this->assertEquals($firstOwner->id, $result->client_id);
        $this->assertTrue($result->current_owner);

        $countForFirstOwner = ClientVehicle::query()
            ->where('client_id', $firstOwner->id)
            ->where('vehicle_id', $vehicle->id)
            ->count();

        $this->assertEquals(1, $countForFirstOwner);

        $this->assertDatabaseHas('client_vehicle', [
            'client_id' => $secondOwner->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => false,
        ]);
    }

    public function test_preserves_history_when_transferring(): void
    {
        $owner1 = Client::factory()->create();
        $owner2 = Client::factory()->create();
        $owner3 = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::create([
            'client_id' => $owner1->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => false,
        ]);

        ClientVehicle::create([
            'client_id' => $owner2->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $action = new TransferVehicleOwnershipAction;
        $action($vehicle->id, $owner3->id);

        $totalRecords = ClientVehicle::query()
            ->where('vehicle_id', $vehicle->id)
            ->count();

        $this->assertEquals(3, $totalRecords);

        $currentOwnerCount = ClientVehicle::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('current_owner', true)
            ->count();

        $this->assertEquals(1, $currentOwnerCount);
    }
}
