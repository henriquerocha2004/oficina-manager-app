<?php

namespace Tests\Feature\tenant;

use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Models\Tenant\Client;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderItem;
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

    public function test_find_returns_service_orders_list(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);

        $response = $this->actingAs($user, 'tenant')->tenantRequest('GET', '/service-orders');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'service_orders',
            ],
        ]);
    }

    public function test_find_one_returns_service_order(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);

        $response = $this->actingAs($user, 'tenant')->tenantRequest('GET', '/service-orders/'.$serviceOrder->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'service_order' => [
                    'id',
                    'order_number',
                    'status',
                ],
            ],
        ]);
    }

    public function test_stats_returns_statistics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'tenant')->tenantRequest('GET', '/service-orders/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'stats' => [
                    'total',
                    'by_status',
                ],
            ],
        ]);
    }

    public function test_delete_removes_service_order(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);

        $response = $this->actingAs($user, 'tenant')->tenantRequest('DELETE', '/service-orders/'.$serviceOrder->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('service_orders', ['id' => $serviceOrder->id]);
    }

    public function test_add_item_to_service_order(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);

        $data = [
            'type' => 'service',
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders/'.$serviceOrder->id.'/items', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $serviceOrder->id,
            'description' => 'Oil change',
        ]);
    }

    public function test_update_diagnosis(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);

        $data = [
            'diagnosis' => 'Updated diagnosis',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('PUT', '/service-orders/'.$serviceOrder->id.'/diagnosis', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'diagnosis' => 'Updated diagnosis',
        ]);
    }

    public function test_update_discount(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 100,
            'paid_amount' => 0,
            'outstanding_balance' => 100,
        ]);

        ServiceOrderItem::query()->create([
            'service_order_id' => $serviceOrder->id,
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Test service',
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ]);

        $data = [
            'discount' => 10.00,
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('PUT', '/service-orders/'.$serviceOrder->id.'/discount', $data);

        $response->assertStatus(200);
    }

    public function test_send_for_approval(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 100,
            'total' => 100,
            'paid_amount' => 0,
            'outstanding_balance' => 100,
        ]);

        ServiceOrderItem::query()->create([
            'service_order_id' => $serviceOrder->id,
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Test service',
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ]);

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders/'.$serviceOrder->id.'/send-for-approval');

        $response->assertStatus(200);
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'status' => ServiceOrderStatusEnum::WAITING_APPROVAL->value,
        ]);
    }

    public function test_approve_service_order(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::WAITING_APPROVAL,
            'total_parts' => 0,
            'total_services' => 100,
            'total' => 100,
            'paid_amount' => 0,
            'outstanding_balance' => 100,
        ]);

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders/'.$serviceOrder->id.'/approve');

        $response->assertStatus(200);
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'status' => ServiceOrderStatusEnum::APPROVED->value,
        ]);
    }

    public function test_start_work(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::APPROVED,
            'total_parts' => 0,
            'total_services' => 100,
            'total' => 100,
            'paid_amount' => 0,
            'outstanding_balance' => 100,
        ]);

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders/'.$serviceOrder->id.'/start-work');

        $response->assertStatus(200);
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'status' => ServiceOrderStatusEnum::IN_PROGRESS->value,
        ]);
    }

    public function test_finish_work(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::IN_PROGRESS,
            'total_parts' => 0,
            'total_services' => 100,
            'total' => 100,
            'paid_amount' => 0,
            'outstanding_balance' => 100,
        ]);

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders/'.$serviceOrder->id.'/finish-work');

        $response->assertStatus(200);
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'status' => ServiceOrderStatusEnum::WAITING_PAYMENT->value,
        ]);
    }

    public function test_cancel_service_order(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);

        $data = [
            'reason' => 'Client requested cancellation',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders/'.$serviceOrder->id.'/cancel', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrder->id,
            'status' => ServiceOrderStatusEnum::CANCELLED->value,
        ]);
    }

    public function test_register_payment(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        ClientVehicle::query()->create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'current_owner' => true,
        ]);

        $serviceOrder = ServiceOrder::query()->create([
            'order_number' => '2026-0001',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'status' => ServiceOrderStatusEnum::WAITING_PAYMENT,
            'total_parts' => 0,
            'total_services' => 100,
            'total' => 100,
            'paid_amount' => 0,
            'outstanding_balance' => 100,
        ]);

        $data = [
            'amount' => 50.00,
            'payment_method' => 'cash',
            'notes' => 'Partial payment',
        ];

        $response = $this->actingAs($user, 'tenant')->tenantRequest('POST', '/service-orders/'.$serviceOrder->id.'/payments', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('service_order_payments', [
            'service_order_id' => $serviceOrder->id,
            'amount' => 50.00,
        ]);
    }
}
