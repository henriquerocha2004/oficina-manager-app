<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\ApproveAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\SendForApprovalAction;
use App\Actions\Tenant\ServiceOrder\StartWorkAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\InvalidStatusTransitionException;
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

class StartWorkActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createApprovedServiceOrder(): ServiceOrder
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

        $sendAction = new SendForApprovalAction($domain);
        $serviceOrder = $sendAction($serviceOrder->id, $user->id);

        $approveAction = new ApproveAction($domain);

        return $approveAction($serviceOrder->id, $user->id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_starts_work_on_approved_service_order(): void
    {
        $serviceOrder = $this->createApprovedServiceOrder();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $action = new StartWorkAction($domain);
        $result = $action($serviceOrder->id, $user->id);

        $this->assertEquals(ServiceOrderStatusEnum::IN_PROGRESS, $result->status);
        $this->assertNotNull($result->started_at);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_starts_work_with_technician(): void
    {
        $serviceOrder = $this->createApprovedServiceOrder();
        $user = User::factory()->create();
        $technician = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $action = new StartWorkAction($domain);
        $result = $action($serviceOrder->id, $user->id, $technician->id);

        $this->assertEquals(ServiceOrderStatusEnum::IN_PROGRESS, $result->status);
        $this->assertEquals($technician->id, $result->technician_id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function test_throws_exception_when_not_approved(): void
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

        $this->expectException(InvalidStatusTransitionException::class);

        $action = new StartWorkAction($domain);
        $action($serviceOrder->id, $user->id);
    }
}
