<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Vehicle\CheckVehiclePlateAction;
use App\Models\Tenant\Client;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CheckVehiclePlateActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_exists_false_when_plate_not_found(): void
    {
        $action = new CheckVehiclePlateAction();
        $result = $action('XYZ-9999');

        $this->assertFalse($result['exists']);
        $this->assertNull($result['vehicle_id']);
        $this->assertNull($result['current_owner_id']);
        $this->assertNull($result['current_owner_name']);
    }

    public function test_returns_exists_true_with_owner_info_when_plate_exists(): void
    {
        $client = Client::factory()->create(['name' => 'João Silva']);
        $vehicle = Vehicle::factory()->create(['license_plate' => 'ABC-1234']);

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $action = new CheckVehiclePlateAction;
        $result = $action('ABC-1234');

        $this->assertTrue($result['exists']);
        $this->assertEquals($vehicle->id, $result['vehicle_id']);
        $this->assertEquals($client->id, $result['current_owner_id']);
        $this->assertEquals('João Silva', $result['current_owner_name']);
    }

    public function test_returns_exists_true_but_no_owner_when_vehicle_has_no_current_owner(): void
    {
        $vehicle = Vehicle::factory()->create(['license_plate' => 'DEF-5678']);

        $action = new CheckVehiclePlateAction;
        $result = $action('DEF-5678');

        $this->assertTrue($result['exists']);
        $this->assertEquals($vehicle->id, $result['vehicle_id']);
        $this->assertNull($result['current_owner_id']);
        $this->assertNull($result['current_owner_name']);
    }
}
