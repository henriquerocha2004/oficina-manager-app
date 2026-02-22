<?php

namespace Tests\Feature\tenant;

use App\Models\Tenant\Client;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ServiceOrderControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function tenantRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return match (strtoupper($method)) {
            'GET' => $this->withoutMiddleware([Authenticate::class])->getJson(
                'http://test-tenant.localhost'.$uri
            ),
            'POST' => $this->withoutMiddleware([Authenticate::class])->postJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            'PUT' => $this->withoutMiddleware([Authenticate::class])->putJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            'DELETE' => $this->withoutMiddleware([Authenticate::class])->deleteJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method")
        };
    }

    public function test_store_creates_service_order_with_existing_client_and_vehicle(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'diagnosis' => 'Motor fazendo barulho',
            'observations' => 'Cliente relatou problema',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'service_order' => [
                    'id',
                    'order_number',
                    'status',
                    'client',
                    'vehicle',
                ],
            ],
        ]);

        $this->assertDatabaseHas('service_orders', [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'diagnosis' => 'Motor fazendo barulho',
        ]);
    }

    public function test_store_creates_service_order_with_new_client(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $data = [
            'new_client' => [
                'name' => 'João Silva',
                'email' => 'joao@example.com',
                'document_number' => '52998224725',
                'phone' => '11999999999',
            ],
            'vehicle_id' => $vehicle->id,
            'diagnosis' => 'Revisão geral',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('client', [
            'document_number' => '52998224725',
            'name' => 'João Silva',
        ]);

        $this->assertDatabaseHas('service_orders', [
            'diagnosis' => 'Revisão geral',
        ]);
    }

    public function test_store_creates_service_order_with_new_vehicle(): void
    {
        $user = User::factory()->create();
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
            'diagnosis' => 'Troca de óleo',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('vehicle', [
            'license_plate' => 'ABC-1234',
        ]);

        $this->assertDatabaseHas('service_orders', [
            'client_id' => $client->id,
            'diagnosis' => 'Troca de óleo',
        ]);
    }

    public function test_store_creates_service_order_with_new_client_and_new_vehicle(): void
    {
        $user = User::factory()->create();

        $data = [
            'new_client' => [
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'document_number' => '12345678909',
                'phone' => '11888888888',
            ],
            'new_vehicle' => [
                'license_plate' => 'XYZ-9876',
                'brand' => 'Honda',
                'model' => 'Civic',
                'vehicle_type' => 'car',
                'year' => 2021,
            ],
            'diagnosis' => 'Revisão de 10.000 km',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('client', [
            'document_number' => '12345678909',
        ]);

        $this->assertDatabaseHas('vehicle', [
            'license_plate' => 'XYZ-9876',
        ]);
    }

    public function test_store_transfers_vehicle_when_confirmed(): void
    {
        $user = User::factory()->create();
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::create([
            'client_id' => $client1->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'client_id' => $client2->id,
            'vehicle_id' => $vehicle->id,
            'transfer_vehicle' => true,
            'diagnosis' => 'Reparo no motor',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(201);

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

    public function test_store_returns_conflict_when_transfer_not_confirmed(): void
    {
        $user = User::factory()->create();
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::create([
            'client_id' => $client1->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $data = [
            'client_id' => $client2->id,
            'vehicle_id' => $vehicle->id,
            'diagnosis' => 'Reparo no motor',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(409);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'vehicle_id',
                'current_owner' => [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    public function test_store_returns_validation_error_when_client_data_missing(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $data = [
            'vehicle_id' => $vehicle->id,
            'diagnosis' => 'Teste',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_id']);
    }

    public function test_store_returns_validation_error_when_vehicle_data_missing(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $data = [
            'client_id' => $client->id,
            'diagnosis' => 'Teste',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vehicle_id']);
    }

    public function test_store_returns_validation_error_when_both_client_id_and_new_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $data = [
            'client_id' => $client->id,
            'new_client' => [
                'name' => 'Novo Cliente',
                'email' => 'novo@example.com',
                'document_number' => '11111111111',
                'phone' => '11111111111',
            ],
            'vehicle_id' => $vehicle->id,
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_id']);
    }

    public function test_store_returns_validation_error_when_both_vehicle_id_and_new_vehicle(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $data = [
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'new_vehicle' => [
                'license_plate' => 'ABC-1234',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'vehicle_type' => 'car',
            ],
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vehicle_id']);
    }
}
