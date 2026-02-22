<?php

namespace App\Services\Tenant\ServiceOrder;

use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderEventTypeEnum;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCancelledException;
use App\Exceptions\ServiceOrder\ServiceOrderAlreadyCompletedException;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderEvent;
use App\Models\Tenant\ServiceOrderPayment;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentService
{
    /**
     * @throws Throwable
     */
    public function registerPayment(
        ServiceOrder $serviceOrder,
        int $userId,
        float $amount,
        PaymentMethodEnum $paymentMethod,
        ?string $notes = null
    ): ServiceOrderPayment {
        return DB::transaction(function () use ($serviceOrder, $userId, $amount, $paymentMethod, $notes) {
            $this->ensureCanReceivePayment($serviceOrder);

            $serviceOrder = $this->lockServiceOrder($serviceOrder->id);

            $payment = $this->createPaymentRecord(
                serviceOrder: $serviceOrder,
                userId: $userId,
                amount: $amount,
                paymentMethod: $paymentMethod,
                notes: $notes
            );

            $previousPaidAmount = $serviceOrder->paid_amount;

            $this->updateServiceOrderFinances($serviceOrder, $amount);

            $this->logPaymentReceived(
                serviceOrder: $serviceOrder,
                userId: $userId,
                payment: $payment,
                previousPaidAmount: $previousPaidAmount
            );

            $this->tryAutoComplete($serviceOrder, $userId);

            return $payment;
        });
    }

    /**
     * @throws Throwable
     */
    public function refundPayment(
        ServiceOrder $serviceOrder,
        ServiceOrderPayment $payment,
        int $userId,
        string $reason
    ): ServiceOrder {
        return DB::transaction(function () use ($serviceOrder, $payment, $userId, $reason) {
            $this->ensurePaymentCanBeRefunded($payment);

            $serviceOrder = $this->lockServiceOrder($serviceOrder->id);

            $refundedAmount = $payment->amount;

            $this->markPaymentAsRefunded($payment, $userId, $reason);

            $previousPaidAmount = $serviceOrder->paid_amount;

            $this->subtractFromServiceOrderFinances($serviceOrder, $refundedAmount);

            $statusChanged = $this->reopenIfNecessary($serviceOrder);

            $this->logPaymentRefunded(
                serviceOrder: $serviceOrder,
                userId: $userId,
                payment: $payment,
                previousPaidAmount: $previousPaidAmount,
                statusChanged: $statusChanged
            );

            return $serviceOrder;
        });
    }

    public function getTotalPaid(ServiceOrder $serviceOrder): float
    {
        return $serviceOrder->payments()
            ->whereNull('refunded_at')
            ->sum('amount');
    }

    public function getOutstandingBalance(ServiceOrder $serviceOrder): float
    {
        return max(0, $serviceOrder->total - $serviceOrder->paid_amount);
    }

    public function canBeCompleted(ServiceOrder $serviceOrder): bool
    {
        $serviceFinished = $serviceOrder->status === ServiceOrderStatusEnum::WAITING_PAYMENT;
        $fullyPaid = $serviceOrder->outstanding_balance <= 0;

        return $serviceFinished && $fullyPaid;
    }

    /**
     * @throws Throwable
     */
    private function ensureCanReceivePayment(ServiceOrder $serviceOrder): void
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
    private function ensurePaymentCanBeRefunded(ServiceOrderPayment $payment): void
    {
        throw_if(
            $payment->refunded_at !== null,
            new Exception('Payment has already been refunded')
        );

        throw_if(
            $payment->serviceOrder->status === ServiceOrderStatusEnum::CANCELLED,
            ServiceOrderAlreadyCancelledException::class
        );
    }

    private function lockServiceOrder(string $serviceOrderId): ServiceOrder
    {
        return ServiceOrder::query()
            ->lockForUpdate()
            ->findOrFail($serviceOrderId);
    }

    private function createPaymentRecord(
        ServiceOrder $serviceOrder,
        int $userId,
        float $amount,
        PaymentMethodEnum $paymentMethod,
        ?string $notes
    ): ServiceOrderPayment {
        return ServiceOrderPayment::query()->create([
            'service_order_id' => $serviceOrder->id,
            'received_by' => $userId,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'paid_at' => Carbon::now(),
            'notes' => $notes,
        ]);
    }

    private function updateServiceOrderFinances(ServiceOrder $serviceOrder, float $amount): void
    {
        $serviceOrder->paid_amount += $amount;
        $serviceOrder->outstanding_balance = $serviceOrder->total - $serviceOrder->paid_amount;
        $serviceOrder->save();
    }

    private function subtractFromServiceOrderFinances(ServiceOrder $serviceOrder, float $amount): void
    {
        $serviceOrder->paid_amount -= $amount;
        $serviceOrder->outstanding_balance = $serviceOrder->total - $serviceOrder->paid_amount;
        $serviceOrder->save();
    }

    private function markPaymentAsRefunded(ServiceOrderPayment $payment, int $userId, string $reason): void
    {
        $payment->refunded_at = Carbon::now();
        $payment->refunded_by = $userId;
        $payment->refund_reason = $reason;
        $payment->save();
    }

    private function tryAutoComplete(ServiceOrder $serviceOrder, int $userId): void
    {
        if (!$this->canBeCompleted($serviceOrder)) {
            return;
        }

        $serviceOrder->status = ServiceOrderStatusEnum::COMPLETED;
        $serviceOrder->completed_at = Carbon::now();
        $serviceOrder->save();

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::STATUS_CHANGED,
            description: 'Service order auto-completed (work finished and fully paid)',
            metadata: [
                'from' => 'waiting_payment',
                'to' => 'completed',
                'trigger' => 'auto_completion',
                'outstanding_balance' => 0
            ]
        );
    }

    private function reopenIfNecessary(ServiceOrder $serviceOrder): bool
    {
        if ($serviceOrder->status !== ServiceOrderStatusEnum::COMPLETED) {
            return false;
        }

        if ($serviceOrder->outstanding_balance <= 0) {
            return false;
        }

        $serviceOrder->status = ServiceOrderStatusEnum::WAITING_PAYMENT;
        $serviceOrder->completed_at = null;
        $serviceOrder->save();

        return true;
    }

    private function logPaymentReceived(
        ServiceOrder $serviceOrder,
        int $userId,
        ServiceOrderPayment $payment,
        float $previousPaidAmount
    ): void {
        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::PAYMENT_RECEIVED,
            description: sprintf('Payment received: R$ %.2f via %s', $payment->amount, $payment->payment_method->value),
            metadata: [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method->value,
                'previous_paid_amount' => $previousPaidAmount,
                'new_paid_amount' => $serviceOrder->paid_amount,
                'outstanding_balance' => $serviceOrder->outstanding_balance,
                'has_credit' => $serviceOrder->outstanding_balance < 0,
            ]
        );
    }

    private function logPaymentRefunded(
        ServiceOrder $serviceOrder,
        int $userId,
        ServiceOrderPayment $payment,
        float $previousPaidAmount,
        bool $statusChanged
    ): void {
        $metadata = [
            'payment_id' => $payment->id,
            'refunded_amount' => $payment->amount,
            'reason' => $payment->refund_reason,
            'previous_paid_amount' => $previousPaidAmount,
            'new_paid_amount' => $serviceOrder->paid_amount,
            'outstanding_balance' => $serviceOrder->outstanding_balance,
            'status_changed' => $statusChanged,
        ];

        if ($statusChanged) {
            $metadata['from_status'] = 'completed';
            $metadata['to_status'] = 'waiting_payment';
        }

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::PAYMENT_REFUNDED,
            description: sprintf('Payment refunded: R$ %.2f - %s', $payment->amount, $payment->refund_reason),
            metadata: $metadata
        );
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
}
