<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\RemoveItemAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrderItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RemoveItemActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws \Throwable
     * @throws BindingResolutionException
     */
    private function createServiceOrderWithItem(): array
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
            $this->app->make(PaymentService::class)
        );

        $createAction = new CreateServiceOrderAction($domain);
        $serviceOrder = $createAction($dto);

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $addAction = new AddItemAction($domain);
        $serviceOrder = $addAction($serviceOrder->id, $user->id, $item);

        $item = $serviceOrder->items->first();

        return [$serviceOrder, $item, $user, $domain];
    }

    /**
     * @throws BindingResolutionException
     * @throws \Throwable
     */
    public function test_removes_item_from_service_order(): void
    {
        [$serviceOrder, $item, $user, $domain] = $this->createServiceOrderWithItem();

        $this->assertDatabaseHas('service_order_items', [
            'id' => $item->id,
        ]);

        $action = new RemoveItemAction($domain);
        $result = $action($serviceOrder->id, $item->id, $user->id);

        $this->assertEquals(0, $result->total);
        $this->assertSoftDeleted('service_order_items', [
            'id' => $item->id,
        ]);
    }

    /**
     * @throws \Throwable
     * @throws BindingResolutionException
     */
    public function test_recalculates_total_after_removal(): void
    {
        [$serviceOrder, $item, $user, $domain] = $this->createServiceOrderWithItem();

        $item2 = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::PART,
            'description' => 'Oil filter',
            'quantity' => 1,
            'unit_price' => 50.00,
            'subtotal' => 50.00,
        ]);

        $addAction = new AddItemAction($domain);
        $serviceOrder = $addAction($serviceOrder->id, $user->id, $item2);

        $this->assertEquals(150.00, $serviceOrder->total);

        $lastItem = $serviceOrder->items->last();

        $action = new RemoveItemAction($domain);
        $result = $action($serviceOrder->id, $lastItem->id, $user->id);

        $this->assertEquals(100.00, $result->total);
    }
}
