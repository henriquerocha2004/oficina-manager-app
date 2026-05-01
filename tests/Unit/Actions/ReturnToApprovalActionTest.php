<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\ApproveAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\FinishWorkAction;
use App\Actions\Tenant\ServiceOrder\ReturnToApprovalAction;
use App\Actions\Tenant\ServiceOrder\SendForApprovalAction;
use App\Actions\Tenant\ServiceOrder\StartWorkAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\InvalidStatusTransitionException;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
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

class ReturnToApprovalActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createWaitingPaymentServiceOrder(): ServiceOrder
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain(
            $this->app->make(PaymentService::class)
        );

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $serviceOrder = (new CreateServiceOrderAction($domain))($dto);

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $serviceOrder = (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);
        $serviceOrder = (new SendForApprovalAction($domain))($serviceOrder->id, $user->id);
        $serviceOrder = (new ApproveAction($domain))($serviceOrder->id, $user->id);
        $serviceOrder = (new StartWorkAction($domain))($serviceOrder->id, $user->id);

        return (new FinishWorkAction($domain))($serviceOrder->id, $user->id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testReturnsToApprovalFromWaitingPayment(): void
    {
        $serviceOrder = $this->createWaitingPaymentServiceOrder();
        $user = User::factory()->create();

        $this->assertEquals(ServiceOrderStatusEnum::WAITING_PAYMENT, $serviceOrder->status);

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));
        $action = new ReturnToApprovalAction($domain);

        $result = $action($serviceOrder->id, $user->id);

        $this->assertEquals(ServiceOrderStatusEnum::WAITING_APPROVAL, $result->status);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testThrowsExceptionWhenServiceOrderNotFound(): void
    {
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));
        $action = new ReturnToApprovalAction($domain);

        $this->expectException(ServiceOrderNotFoundException::class);

        $action('non-existent-id', $user->id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testThrowsExceptionWhenStatusIsNotWaitingPayment(): void
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->app->make(PaymentService::class));

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        $serviceOrder = (new CreateServiceOrderAction($domain))($dto);

        $this->expectException(InvalidStatusTransitionException::class);

        $action = new ReturnToApprovalAction($domain);
        $action($serviceOrder->id, $user->id);
    }
}
