<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\SendForApprovalAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\InvalidStatusTransitionException;
use App\Exceptions\ServiceOrder\ServiceOrderEmptyException;
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

class SendForApprovalActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
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
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_sends_for_approval_when_draft_with_items(): void
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

        $addAction = new AddItemAction($domain);
        $serviceOrder = $addAction($serviceOrder->id, $user->id, $item);

        $action = new SendForApprovalAction($domain);
        $result = $action($serviceOrder->id, $user->id);

        $this->assertEquals(ServiceOrderStatusEnum::WAITING_APPROVAL, $result->status);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_throws_exception_when_no_items(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $this->expectException(ServiceOrderEmptyException::class);

        $action = new SendForApprovalAction($domain);
        $action($serviceOrder->id, $user->id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_throws_exception_when_not_draft(): void
    {
        $serviceOrder = $this->createServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $addAction = new AddItemAction($domain);
        $serviceOrder = $addAction($serviceOrder->id, $user->id, $item);

        $serviceOrder->status = ServiceOrderStatusEnum::APPROVED;
        $serviceOrder->save();

        $this->expectException(InvalidStatusTransitionException::class);

        $action = new SendForApprovalAction($domain);
        $action($serviceOrder->id, $user->id);
    }
}
