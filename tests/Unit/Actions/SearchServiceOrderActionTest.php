<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\SearchServiceOrderAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Dto\ServiceOrderSearchDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Models\Tenant\Client;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class SearchServiceOrderActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createServiceOrder(?string $clientId = null, ?string $vehicleId = null): \App\Models\Tenant\ServiceOrder
    {
        $client = $clientId ? \App\Models\Tenant\Client::query()->find($clientId) : Client::factory()->create();
        $vehicle = $vehicleId ? \App\Models\Tenant\Vehicle::query()->find($vehicleId) : Vehicle::factory()->create();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        return (new CreateServiceOrderAction($domain))($dto);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testReturnsListWithPagination(): void
    {
        $this->createServiceOrder();
        $this->createServiceOrder();

        $dto = new ServiceOrderSearchDto(per_page: 1);
        $action = new SearchServiceOrderAction();

        $result = $action($dto);

        $this->assertGreaterThanOrEqual(2, $result->total());
        $this->assertCount(1, $result->items());
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testFiltersOrdersByStatus(): void
    {
        $this->createServiceOrder();
        $this->createServiceOrder();

        $dto = new ServiceOrderSearchDto(
            status: ServiceOrderStatusEnum::DRAFT,
            per_page: 50
        );

        $action = new SearchServiceOrderAction();
        $result = $action($dto);

        foreach ($result->items() as $order) {
            $this->assertEquals(ServiceOrderStatusEnum::DRAFT, $order->status);
        }
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testFiltersOrdersByClientId(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $this->createServiceOrder($client->id, $vehicle->id);
        $this->createServiceOrder();

        $dto = new ServiceOrderSearchDto(client_id: $client->id, per_page: 50);
        $action = new SearchServiceOrderAction();

        $result = $action($dto);

        $this->assertGreaterThanOrEqual(1, $result->total());
        foreach ($result->items() as $order) {
            $this->assertEquals($client->id, $order->client_id);
        }
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testReturnsEmptyWhenNoOrdersMatchFilter(): void
    {
        $dto = new ServiceOrderSearchDto(
            order_number: 'NONEXISTENT-9999',
            per_page: 15
        );

        $action = new SearchServiceOrderAction();
        $result = $action($dto);

        $this->assertCount(0, $result->items());
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testReturnsOrdersSortedByCreatedAtDesc(): void
    {
        $this->createServiceOrder();
        $this->createServiceOrder();

        $dto = new ServiceOrderSearchDto(
            sort_by: 'created_at',
            sort_direction: 'desc',
            per_page: 50
        );

        $action = new SearchServiceOrderAction();
        $result = $action($dto);

        $items = $result->items();
        for ($i = 0; $i < count($items) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $items[$i + 1]->created_at,
                $items[$i]->created_at
            );
        }
    }
}
