<?php

namespace App\Domain\Tenant\ServiceOrder;

use App\Enum\Tenant\ServiceOrder\ServiceOrderEventTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\InvalidStatusTransitionException;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCancelledException;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCompletedException;
use App\Exceptions\ServiceOrder\ServiceOrderEmptyException;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderEvent;
use App\Models\Tenant\ServiceOrderItem;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Illuminate\Support\Carbon;
use Throwable;

class ServiceOrderDomain
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function sendForApproval(ServiceOrder $serviceOrder, int $userId): ServiceOrder
    {
        $this->ensureCanBeModified($serviceOrder);
        $this->ensureHasItems($serviceOrder);

        throw_if(
            $serviceOrder->status !== ServiceOrderStatusEnum::DRAFT,
            InvalidStatusTransitionException::class
        );

        $serviceOrder->status = ServiceOrderStatusEnum::WAITING_APPROVAL;
        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::STATUS_CHANGED,
            description: 'Service order sent for client approval',
            metadata: ['from' => 'draft', 'to' => 'waiting_approval']
        );

        return $serviceOrder;
    }

    /**
     * @throws Throwable
     */
    public function approve(ServiceOrder $serviceOrder, int $userId): ServiceOrder
    {
        $this->ensureCanBeModified($serviceOrder);

        throw_if(
            $serviceOrder->status !== ServiceOrderStatusEnum::WAITING_APPROVAL,
            InvalidStatusTransitionException::class
        );

        $serviceOrder->status = ServiceOrderStatusEnum::APPROVED;
        $serviceOrder->approved_at = Carbon::now();
        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::STATUS_CHANGED,
            description: 'Service order approved by client',
            metadata: ['from' => 'waiting_approval', 'to' => 'approved']
        );

        return $serviceOrder;
    }

    /**
     * @throws Throwable
     */
    public function startWork(ServiceOrder $serviceOrder, int $userId, ?int $technicianId = null): ServiceOrder
    {
        $this->ensureCanBeModified($serviceOrder);

        throw_if(
            $serviceOrder->status !== ServiceOrderStatusEnum::APPROVED,
            InvalidStatusTransitionException::class
        );

        $serviceOrder->status = ServiceOrderStatusEnum::IN_PROGRESS;
        $serviceOrder->started_at = Carbon::now();

        if ($technicianId !== null) {
            $serviceOrder->technician_id = $technicianId;
        }

        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::STATUS_CHANGED,
            description: 'Service work started',
            metadata: [
                'from' => 'approved',
                'to' => 'in_progress',
                'technician_id' => $serviceOrder->technician_id,
            ]
        );

        return $serviceOrder;
    }

    /**
     * @throws Throwable
     */
    public function finishWork(ServiceOrder $serviceOrder, int $userId): ServiceOrder
    {
        $this->ensureCanBeModified($serviceOrder);

        throw_if(
            $serviceOrder->status !== ServiceOrderStatusEnum::IN_PROGRESS,
            InvalidStatusTransitionException::class
        );

        if ($this->paymentService->canBeCompleted($serviceOrder)) {
            return $this->completeServiceOrderImmediately($serviceOrder, $userId);
        }

        return $this->moveToWaitingPayment($serviceOrder, $userId);
    }

    /**
     * @throws Throwable
     */
    public function cancel(ServiceOrder $serviceOrder, int $userId, string $reason): ServiceOrder
    {
        throw_if(
            $serviceOrder->status === ServiceOrderStatusEnum::COMPLETED,
            ServiceOrderAlreadyCompletedException::class
        );

        throw_if(
            $serviceOrder->status === ServiceOrderStatusEnum::CANCELLED,
            ServiceOrderAlreadyCancelledException::class
        );

        $oldStatus = $serviceOrder->status->value;
        $serviceOrder->status = ServiceOrderStatusEnum::CANCELLED;
        $serviceOrder->cancelled_at = Carbon::now();
        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::STATUS_CHANGED,
            description: 'Service order cancelled: '.$reason,
            metadata: ['from' => $oldStatus, 'to' => 'cancelled', 'reason' => $reason]
        );

        return $serviceOrder;
    }

    /**
     * @throws Throwable
     */
    public function updateDiagnosis(ServiceOrder $serviceOrder, int $userId, string $diagnosis): ServiceOrder
    {
        $this->ensureCanBeModified($serviceOrder);

        $oldDiagnosis = $serviceOrder->diagnosis;
        $serviceOrder->diagnosis = $diagnosis;
        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::DIAGNOSIS_UPDATED,
            description: 'Diagnosis updated',
            metadata: ['old' => $oldDiagnosis, 'new' => $diagnosis]
        );

        return $serviceOrder;
    }

    /**
     * @throws Throwable
     */
    public function addItem(
        ServiceOrder $serviceOrder,
        int $userId,
        ServiceOrderItem $item
    ): ServiceOrder {
        $this->ensureCanBeModified($serviceOrder);

        $serviceOrder->items()->save($item);
        $this->recalculateTotals($serviceOrder);

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::ITEM_ADDED,
            description: sprintf('Item added: %s (Qty: %d, R$ %.2f)', $item->description, $item->quantity, $item->subtotal),
            metadata: [
                'item_id' => $item->id,
                'type' => $item->type->value,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ]
        );

        return $serviceOrder;
    }

    /**
     * @throws Throwable
     */
    public function removeItem(ServiceOrder $serviceOrder, int $userId, ServiceOrderItem $item): ServiceOrder
    {
        $this->ensureCanBeModified($serviceOrder);

        $description = $item->description;
        $item->delete();

        $this->recalculateTotals($serviceOrder);

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::ITEM_REMOVED,
            description: 'Item removed: '.$description,
            metadata: ['item_id' => $item->id, 'description' => $description]
        );

        return $serviceOrder;
    }

    /**
     * @throws Throwable
     */
    public function updateDiscount(ServiceOrder $serviceOrder, int $userId, float $discount): ServiceOrder
    {
        $this->ensureCanBeModified($serviceOrder);

        $oldDiscount = $serviceOrder->discount;
        $serviceOrder->discount = $discount;
        $this->recalculateTotals($serviceOrder);

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::NOTE_ADDED,
            description: sprintf('Discount updated from R$ %.2f to R$ %.2f', $oldDiscount, $discount),
            metadata: ['old_discount' => $oldDiscount, 'new_discount' => $discount]
        );

        return $serviceOrder;
    }

    public function recalculateTotals(ServiceOrder $serviceOrder): void
    {
        $serviceOrder->load('items');

        $totalServices = 0;
        $totalParts = 0;

        foreach ($serviceOrder->items as $item) {
            if ($item->type->value === 'service') {
                $totalServices += $item->subtotal;
            }

            if ($item->type->value === 'part') {
                $totalParts += $item->subtotal;
            }
        }

        $serviceOrder->total_services = $totalServices;
        $serviceOrder->total_parts = $totalParts;
        $serviceOrder->total = ($totalServices + $totalParts) - $serviceOrder->discount;
        $serviceOrder->outstanding_balance = $serviceOrder->total - $serviceOrder->paid_amount;
        $serviceOrder->save();
    }

    public function generateOrderNumber(int $year): string
    {
        $lastOrder = ServiceOrder::query()
            ->where('order_number', 'like', $year.'-%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder === null) {
            return sprintf('%d-0001', $year);
        }

        $lastNumber = (int) substr($lastOrder->order_number, -4);
        $nextNumber = $lastNumber + 1;

        return sprintf('%d-%04d', $year, $nextNumber);
    }

    private function completeServiceOrderImmediately(ServiceOrder $serviceOrder, int $userId): ServiceOrder
    {
        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->completed_at = Carbon::now();
        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::STATUS_CHANGED,
            description: 'Service work finished and auto-completed (already fully paid)',
            metadata: [
                'from' => 'in_progress',
                'to' => 'completed',
                'trigger' => 'prepaid_completion',
            ]
        );

        return $serviceOrder;
    }

    private function moveToWaitingPayment(ServiceOrder $serviceOrder, int $userId): ServiceOrder
    {
        $serviceOrder->status = ServiceOrderStatusEnum::WAITING_PAYMENT;
        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::STATUS_CHANGED,
            description: 'Service work finished, waiting for payment',
            metadata: [
                'from' => 'in_progress',
                'to' => 'waiting_payment',
                'outstanding_balance' => $serviceOrder->outstanding_balance,
            ]
        );

        return $serviceOrder;
    }

    private function logEvent(
        ServiceOrder $serviceOrder,
        int $userId,
        ServiceOrderEventTypeEnum $eventType,
        string $description,
        ?array $metadata = null
    ): void {
        ServiceOrderEvent::query()->create([
            'service_order_id' => $serviceOrder->id,
            'user_id' => $userId,
            'event_type' => $eventType,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * @throws Throwable
     */
    private function ensureCanBeModified(ServiceOrder $serviceOrder): void
    {
        throw_if(
            $serviceOrder->status === ServiceOrderStatusEnum::COMPLETED,
            ServiceOrderAlreadyCompletedException::class
        );

        throw_if(
            $serviceOrder->status === ServiceOrderStatusEnum::CANCELLED,
            ServiceOrderAlreadyCancelledException::class
        );
    }

    /**
     * @throws Throwable
     */
    private function ensureHasItems(ServiceOrder $serviceOrder): void
    {
        $itemCount = $serviceOrder->items()->count();

        throw_if(
            $itemCount === 0,
            ServiceOrderEmptyException::class
        );
    }
}
