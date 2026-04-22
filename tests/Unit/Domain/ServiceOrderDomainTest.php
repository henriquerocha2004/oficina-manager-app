<?php

namespace Tests\Unit\Domain;

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

class ServiceOrderDomainTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function makeDomain(): ServiceOrderDomain
    {
        return new ServiceOrderDomain($this->app->make(PaymentService::class));
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createDraftOrder(): ServiceOrder
    {
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $dto = new ServiceOrderDto(
            client_id: $client->id,
            vehicle_id: $vehicle->id,
            created_by: $user->id,
        );

        return (new CreateServiceOrderAction($this->makeDomain()))($dto);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    private function createInProgressOrder(): ServiceOrder
    {
        $serviceOrder = $this->createDraftOrder();
        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Test service',
            'quantity' => 1,
            'unit_price' => 200.00,
            'subtotal' => 200.00,
        ]);

        $serviceOrder = (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);
        $serviceOrder = (new SendForApprovalAction($domain))($serviceOrder->id, $user->id);
        $serviceOrder = (new ApproveAction($domain))($serviceOrder->id, $user->id);

        return (new StartWorkAction($domain))($serviceOrder->id, $user->id);
    }

    // ─── generateOrderNumber ───────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testGeneratesFirstOrderNumberOfYear(): void
    {
        $year = 2099;
        $domain = $this->makeDomain();

        $number = $domain->generateOrderNumber($year);

        $this->assertEquals('2099-0001', $number);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testGeneratesIncrementalOrderNumber(): void
    {
        $year = 2099;
        $domain = $this->makeDomain();

        ServiceOrder::query()->create([
            'order_number' => '2099-0005',
            'client_id' => Client::factory()->create()->id,
            'vehicle_id' => Vehicle::factory()->create()->id,
            'created_by' => User::factory()->create()->id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'discount' => 0, 'total_parts' => 0, 'total_services' => 0,
            'total' => 0, 'paid_amount' => 0, 'outstanding_balance' => 0,
        ]);

        $number = $domain->generateOrderNumber($year);

        $this->assertEquals('2099-0006', $number);
    }

    // ─── recalculateTotals ─────────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testRecalculatesTotalsCorrectly(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $serviceItem = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Oil change',
            'quantity' => 1,
            'unit_price' => 100.00,
            'subtotal' => 100.00,
        ]);

        $partItem = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::PART,
            'description' => 'Oil filter',
            'quantity' => 2,
            'unit_price' => 30.00,
            'subtotal' => 60.00,
        ]);

        (new AddItemAction($domain))($serviceOrder->id, $user->id, $serviceItem);
        (new AddItemAction($domain))($serviceOrder->id, $user->id, $partItem);

        $serviceOrder->refresh();

        $this->assertEquals(100.00, $serviceOrder->total_services);
        $this->assertEquals(60.00, $serviceOrder->total_parts);
        $this->assertEquals(160.00, $serviceOrder->total);
        $this->assertEquals(160.00, $serviceOrder->outstanding_balance);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testDiscountIsSubtractedFromTotal(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Brake service',
            'quantity' => 1,
            'unit_price' => 200.00,
            'subtotal' => 200.00,
        ]);

        (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);
        $domain->updateDiscount($serviceOrder->fresh(), $user->id, 50.00);

        $serviceOrder->refresh();

        $this->assertEquals(200.00, $serviceOrder->total_services);
        $this->assertEquals(150.00, $serviceOrder->total);
    }

    // ─── finishWork — auto-complete ────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testFinishWorkThrowsWhenNotInProgress(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $this->expectException(InvalidStatusTransitionException::class);

        $domain->finishWork($serviceOrder, $user->id);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testFinishWorkMovesToWaitingPaymentWhenUnpaid(): void
    {
        $serviceOrder = $this->createInProgressOrder();
        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $result = $domain->finishWork($serviceOrder, $user->id);

        $this->assertEquals(ServiceOrderStatusEnum::WAITING_PAYMENT, $result->status);
    }

    // ─── cancel ────────────────────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCancelThrowsWhenAlreadyCompleted(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $domain->cancel($serviceOrder, $user->id, 'reason');
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCancelThrowsWhenAlreadyCancelled(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $domain->cancel($serviceOrder, $user->id, 'reason');
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testCancelSetsCancelledStatusAndTimestamp(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $result = $domain->cancel($serviceOrder, $user->id, 'Client request');

        $this->assertEquals(ServiceOrderStatusEnum::CANCELLED, $result->status);
        $this->assertNotNull($result->cancelled_at);
    }

    // ─── ensureCanBeModified ───────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testUpdateDiagnosisThrowsWhenCompleted(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $domain->updateDiagnosis($serviceOrder, $user->id, 'new diagnosis');
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testUpdateDiagnosisThrowsWhenCancelled(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $domain->updateDiagnosis($serviceOrder, $user->id, 'new diagnosis');
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testAddItemThrowsWhenCompleted(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'New service',
            'quantity' => 1,
            'unit_price' => 50.00,
            'subtotal' => 50.00,
        ]);

        $this->expectException(ServiceOrderAlreadyCompletedException::class);

        $domain->addItem($serviceOrder, $user->id, $item);
    }

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testAddItemThrowsWhenCancelled(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->save();

        $user = User::factory()->create();
        $domain = $this->makeDomain();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'New service',
            'quantity' => 1,
            'unit_price' => 50.00,
            'subtotal' => 50.00,
        ]);

        $this->expectException(ServiceOrderAlreadyCancelledException::class);

        $domain->addItem($serviceOrder, $user->id, $item);
    }

    // ─── startWork ─────────────────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testStartWorkSetsTechnicianId(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $user = User::factory()->create();
        $technician = User::factory()->create();
        $domain = $this->makeDomain();

        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Alignment',
            'quantity' => 1,
            'unit_price' => 80.00,
            'subtotal' => 80.00,
        ]);

        (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);
        (new SendForApprovalAction($domain))($serviceOrder->id, $user->id);
        (new ApproveAction($domain))($serviceOrder->id, $user->id);

        $result = $domain->startWork($serviceOrder->fresh(), $user->id, $technician->id);

        $this->assertEquals($technician->id, $result->technician_id);
        $this->assertNotNull($result->started_at);
    }

    // ─── sendForApproval ───────────────────────────────────────────────────

    /**
     * @throws Throwable
     * @throws BindingResolutionException
     */
    public function testSendForApprovalThrowsWhenAlreadyApproved(): void
    {
        $serviceOrder = $this->createDraftOrder();
        $user = User::factory()->create();
        $domain = $this->makeDomain();

        // Add an item so ensureHasItems passes and the status guard is reached
        $item = new ServiceOrderItem([
            'type' => ServiceOrderItemTypeEnum::SERVICE,
            'description' => 'Test',
            'quantity' => 1,
            'unit_price' => 50.00,
            'subtotal' => 50.00,
        ]);
        (new AddItemAction($domain))($serviceOrder->id, $user->id, $item);

        $serviceOrder->status = ServiceOrderStatusEnum::APPROVED;
        $serviceOrder->save();

        $this->expectException(InvalidStatusTransitionException::class);

        $domain->sendForApproval($serviceOrder->fresh(), $user->id);
    }
}
