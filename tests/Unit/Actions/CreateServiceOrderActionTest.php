<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CreateServiceOrderActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_creates_service_order_successfully(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
            diagnosis: 'Motor fazendo barulho',
            observations: 'Cliente relatou problema há 2 semanas',
        );

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $action = new CreateServiceOrderAction($domain);
        $result = $action($dto);

        $this->assertInstanceOf(ServiceOrder::class, $result);
        $this->assertEquals($client->id, $result->client_id);
        $this->assertEquals($vehicle->id, $result->vehicle_id);
        $this->assertEquals($user->id, $result->created_by);
        $this->assertEquals(ServiceOrderStatusEnum::DRAFT, $result->status);
    }

    public function test_generates_order_number(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $action = new CreateServiceOrderAction($domain);
        $result = $action($dto);

        $this->assertNotNull($result->order_number);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{4}$/', $result->order_number);
    }

    public function test_sets_initial_values_to_zero(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $action = new CreateServiceOrderAction($domain);
        $result = $action($dto);

        $this->assertEquals(0, $result->total_parts);
        $this->assertEquals(0, $result->total_services);
        $this->assertEquals(0, $result->total);
        $this->assertEquals(0, $result->paid_amount);
        $this->assertEquals(0, $result->outstanding_balance);
        $this->assertEquals(0, $result->discount);
    }

    public function test_sets_status_to_draft(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $action = new CreateServiceOrderAction($domain);
        $result = $action($dto);

        $this->assertEquals(ServiceOrderStatusEnum::DRAFT, $result->status);
    }

    public function test_stores_all_provided_fields(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();
        $technician = User::factory()->create();

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
            diagnosis: 'Diagnóstico completo',
            observations: 'Observações importantes',
            technician_id: $technician->id,
            discount: 100.00,
        );

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $action = new CreateServiceOrderAction($domain);
        $result = $action($dto);

        $this->assertEquals('Diagnóstico completo', $result->diagnosis);
        $this->assertEquals('Observações importantes', $result->observations);
        $this->assertEquals($technician->id, $result->technician_id);
        $this->assertEquals(100.00, $result->discount);

        $this->assertDatabaseHas('service_orders', [
            'id' => $result->id,
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'created_by' => $user->id,
            'diagnosis' => 'Diagnóstico completo',
            'observations' => 'Observações importantes',
            'technician_id' => $technician->id,
        ]);
    }
}
