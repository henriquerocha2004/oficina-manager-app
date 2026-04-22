<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCancelledException;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCompletedException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class AddItemActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    private function createServiceOrder(): ServiceOrder
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

        return $createAction($dto);
    }

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function test_adds_item_to_draft_service_order(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $user = User::factory()->create();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $action = new AddItemAction($domain);
        $result = $action($serviceOrder->id, $user->id, $item);

        $this->assertEquals(100.00, $result->total);
        $this->assertEquals(100.00, $result->total_services);
        $this->assertDatabaseHas('service_order_items', [
            'service_order_id' => $result->id,
            'description' => 'Oil change',
        ]);
    }

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function test_throws_exception_when_service_order_completed(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $action = new AddItemAction($domain);
        $action($serviceOrder->id, $user->id, $item);
    }

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function test_throws_exception_when_service_order_cancelled(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $action = new AddItemAction($domain);
        $action($serviceOrder->id, $user->id, $item);
    }
}
