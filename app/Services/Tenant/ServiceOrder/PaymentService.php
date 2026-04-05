<?php

namespace App\Services\Tenant\ServiceOrder;

use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
use App\Enum\Tenant\ServiceOrder\PaymentTypeEnum;
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
        ?string $notes = null,
        ?int $installments = null
    ): ServiceOrderPayment {
        return DB::transaction(function () use ($serviceOrder, $userId, $amount, $paymentMethod, $notes, $installments) {
            $this->ensureCanReceivePayment($serviceOrder);

            $serviceOrder = $this->lockServiceOrder($serviceOrder->id);

            $payment = $this->createPaymentRecord(
                serviceOrder: $serviceOrder,
                userId: $userId,
                amount: $amount,
                paymentMethod: $paymentMethod,
                notes: $notes,
                installments: $installments
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
        int $userId,
        float $amount,
        PaymentMethodEnum $returnMethod,
        ?string $reason = null
    ): ServiceOrder {
        return DB::transaction(function () use ($serviceOrder, $userId, $amount, $returnMethod, $reason) {
            $this->ensureCanRefund($serviceOrder, $amount);

            $serviceOrder = $this->lockServiceOrder($serviceOrder->id);

            $this->createRefundRecord(
                serviceOrder: $serviceOrder,
                userId: $userId,
                amount: $amount,
                returnMethod: $returnMethod,
                reason: $reason
            );

            $previousPaidAmount = $serviceOrder->paid_amount;

            $this->subtractFromServiceOrderFinances($serviceOrder, $amount);

            $statusChanged = $this->reopenIfNecessary($serviceOrder);

            $this->logPaymentRefunded(
                serviceOrder: $serviceOrder,
                userId: $userId,
                refundedAmount: $amount,
                returnMethod: $returnMethod,
                reason: $reason,
                previousPaidAmount: $previousPaidAmount,
                statusChanged: $statusChanged
            );

            return $serviceOrder;
        });
    }

    public function getTotalPaid(ServiceOrder $serviceOrder): float
    {
        $totalPayments = $serviceOrder->payments()
            ->where('type', PaymentTypeEnum::PAYMENT)
            ->sum('amount');

        $totalRefunds = $serviceOrder->payments()
            ->where('type', PaymentTypeEnum::REFUND)
            ->sum('amount');

        return max(0, $totalPayments - $totalRefunds);
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
    private function ensureCanRefund(ServiceOrder $serviceOrder, float $amount): void
    {
        throw_if(
            $serviceOrder->status === ServiceOrderStatusEnum::CANCELLED,
            ServiceOrderAlreadyCancelledException::class
        );

        throw_if(
            $serviceOrder->paid_amount < $amount,
            new Exception('Refund amount exceeds total paid amount.')
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
        ?string $notes,
        ?int $installments = null
    ): ServiceOrderPayment {
        return ServiceOrderPayment::query()->create([
            'type'             => PaymentTypeEnum::PAYMENT,
            'service_order_id' => $serviceOrder->id,
            'received_by'      => $userId,
            'payment_method'   => $paymentMethod,
            'amount'           => $amount,
            'installments'     => $installments,
            'paid_at'          => Carbon::now(),
            'notes'            => $notes,
        ]);
    }

    private function createRefundRecord(
        ServiceOrder $serviceOrder,
        int $userId,
        float $amount,
        PaymentMethodEnum $returnMethod,
        ?string $reason
    ): ServiceOrderPayment {
        return ServiceOrderPayment::query()->create([
            'type'             => PaymentTypeEnum::REFUND,
            'service_order_id' => $serviceOrder->id,
            'received_by'      => $userId,
            'payment_method'   => $returnMethod,
            'amount'           => $amount,
            'paid_at'          => Carbon::now(),
            'notes'            => $reason,
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
                'from'                => 'waiting_payment',
                'to'                  => 'completed',
                'trigger'             => 'auto_completion',
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
                'payment_id'          => $payment->id,
                'amount'              => $payment->amount,
                'payment_method'      => $payment->payment_method->value,
                'installments'        => $payment->installments,
                'previous_paid_amount' => $previousPaidAmount,
                'new_paid_amount'     => $serviceOrder->paid_amount,
                'outstanding_balance' => $serviceOrder->outstanding_balance,
                'has_credit'          => $serviceOrder->outstanding_balance < 0,
            ]
        );
    }

    private function logPaymentRefunded(
        ServiceOrder $serviceOrder,
        int $userId,
        float $refundedAmount,
        PaymentMethodEnum $returnMethod,
        ?string $reason,
        float $previousPaidAmount,
        bool $statusChanged
    ): void {
        $metadata = [
            'refunded_amount'     => $refundedAmount,
            'return_method'       => $returnMethod->value,
            'reason'              => $reason,
            'previous_paid_amount' => $previousPaidAmount,
            'new_paid_amount'     => $serviceOrder->paid_amount,
            'outstanding_balance' => $serviceOrder->outstanding_balance,
            'status_changed'      => $statusChanged,
        ];

        if ($statusChanged) {
            $metadata['from_status'] = 'completed';
            $metadata['to_status']   = 'waiting_payment';
        }

        $this->logEvent(
            serviceOrder: $serviceOrder,
            userId: $userId,
            eventType: ServiceOrderEventTypeEnum::PAYMENT_REFUNDED,
            description: sprintf('Payment refunded: R$ %.2f via %s', $refundedAmount, $returnMethod->value),
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
            'user_id'          => $userId,
            'event_type'       => $eventType,
            'description'      => $description,
            'metadata'         => $metadata,
        ]);
    }
}
