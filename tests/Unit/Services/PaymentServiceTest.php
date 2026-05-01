<?php

namespace Tests\Unit\Services;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\ApproveAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\FinishWorkAction;
use App\Actions\Tenant\ServiceOrder\SendForApprovalAction;
use App\Actions\Tenant\ServiceOrder\StartWorkAction;
use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
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
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class PaymentServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws BindingResolutionException
     */
    private function makeService(): PaymentService
    {
        return $this->app->make(PaymentService::class);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createWaitingPaymentOrder(): ServiceOrder
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $domain = new ServiceOrderDomain($this->makeService());

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
            'unit_price' => 300.00,
            'subtotal' => 300.00,
        ]);

        $serviceOrder = (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);
        $serviceOrder = (new SendForApprovalAction($domain))($serviceOrder->id, $user->id);
        $serviceOrder = (new ApproveAction($domain))($serviceOrder->id, $user->id);
        $serviceOrder = (new StartWorkAction($domain))($serviceOrder->id, $user->id);

        return (new FinishWorkAction($domain))($serviceOrder->id, $user->id);
    }

    // ─── getTotalPaid ──────────────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testGetTotalPaidCalculatesCorrectly(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $user = User::factory()->create();
        $service = $this->makeService();

        $service->registerPayment($serviceOrder, $user->id, 100.00, PaymentMethodEnum::CASH);

        $this->assertEquals(100.00, $service->getTotalPaid($serviceOrder->fresh()));
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testGetTotalPaidSubtractsRefunds(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $user = User::factory()->create();
        $service = $this->makeService();

        $service->registerPayment($serviceOrder, $user->id, 200.00, PaymentMethodEnum::CASH);
        $service->refundPayment($serviceOrder->fresh(), $user->id, 50.00, PaymentMethodEnum::CASH, 'Overcharge');

        $this->assertEquals(150.00, $service->getTotalPaid($serviceOrder->fresh()));
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testGetTotalPaidReturnsZeroWhenNothingPaid(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $service = $this->makeService();

        $this->assertEquals(0.0, $service->getTotalPaid($serviceOrder));
    }

    // ─── getOutstandingBalance ─────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testGetOutstandingBalanceCalculatesCorrectly(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $user = User::factory()->create();
        $service = $this->makeService();

        $service->registerPayment($serviceOrder, $user->id, 100.00, PaymentMethodEnum::CASH);

        $balance = $service->getOutstandingBalance($serviceOrder->fresh());

        $this->assertEquals(200.00, $balance);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testGetOutstandingBalanceIsZeroWhenFullyPaid(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $user = User::factory()->create();
        $service = $this->makeService();

        $service->registerPayment($serviceOrder, $user->id, 300.00, PaymentMethodEnum::CASH);

        $balance = $service->getOutstandingBalance($serviceOrder->fresh());

        $this->assertEquals(0.0, $balance);
    }

    // ─── canBeCompleted ────────────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCanBeCompletedReturnsTrueWhenWaitingPaymentAndFullyPaid(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $serviceOrder->outstanding_balance = 0;
        $serviceOrder->save();

        $service = $this->makeService();

        $this->assertTrue($service->canBeCompleted($serviceOrder));
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCanBeCompletedReturnsFalseWhenNotWaitingPayment(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::IN_PROGRESS;
        $serviceOrder->outstanding_balance = 0;
        $serviceOrder->save();

        $service = $this->makeService();

        $this->assertFalse($service->canBeCompleted($serviceOrder));
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCanBeCompletedReturnsFalseWhenHasOutstandingBalance(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();

        $service = $this->makeService();

        $this->assertFalse($service->canBeCompleted($serviceOrder));
    }

    // ─── registerPayment — guard conditions ───────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCannotRegisterPaymentOnCancelledOrder(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $service = $this->makeService();

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $service->registerPayment($serviceOrder, $user->id, 100.00, PaymentMethodEnum::CASH);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCannotRegisterPaymentOnCompletedOrder(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $service = $this->makeService();

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $service->registerPayment($serviceOrder, $user->id, 100.00, PaymentMethodEnum::CASH);
    }

    // ─── refundPayment — guard conditions ─────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCannotRefundMoreThanPaidAmount(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $user = User::factory()->create();
        $service = $this->makeService();

        $service->registerPayment($serviceOrder, $user->id, 100.00, PaymentMethodEnum::CASH);

        $this->expectException(Exception::class);

        $service->refundPayment($serviceOrder->fresh(), $user->id, 200.00, PaymentMethodEnum::CASH, 'Excess refund');
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCannotRefundOnCancelledOrder(): void
    {
        $serviceOrder = $this->createWaitingPaymentOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->paid_amount = 100.00;
        $serviceOrder->save();

        $user = User::factory()->create();
        $service = $this->makeService();

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $service->refundPayment($serviceOrder, $user->id, 50.00, PaymentMethodEnum::CASH, 'reason');
    }
}
