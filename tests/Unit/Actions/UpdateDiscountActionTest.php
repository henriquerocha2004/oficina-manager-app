<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\UpdateDiscountAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCancelledException;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCompletedException;
use App\Models\Tenant\Client;
use App\Models\Tenant\ServiceOrderItem;
use App\Models\Tenant\User;
use App\Models\Tenant\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UpdateDiscountActionTest extends TestCase
{
    use DatabaseTransactions;

    private function createServiceOrderWithItem(): \App\Models\Tenant\ServiceOrder
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

        return $addAction($serviceOrder->id, $user->id, $item);
    }

    public function test_updates_discount(): void
    {
        $serviceOrder = $this->createServiceOrderWithItem();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $action = new UpdateDiscountAction($domain);
        $result = $action($serviceOrder->id, $user->id, 10.00);

        $this->assertEquals(10.00, $result->discount);
        $this->assertEquals(90.00, $result->total);
    }

    public function test_throws_exception_when_completed(): void
    {
        $serviceOrder = $this->createServiceOrderWithItem();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $action = new UpdateDiscountAction($domain);
        $action($serviceOrder->id, $user->id, 10.00);
    }

    public function test_throws_exception_when_cancelled(): void
    {
        $serviceOrder = $this->createServiceOrderWithItem();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(\App\Services\Tenant\ServiceOrder\PaymentService::class)
        );

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $action = new UpdateDiscountAction($domain);
        $action($serviceOrder->id, $user->id, 10.00);
    }
}
