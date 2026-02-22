<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Exceptions\ServiceOrder\PaymentNotFoundException;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderPayment;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Throwable;

readonly class RefundPaymentAction
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(
        string $serviceOrderId,
        string $paymentId,
        int $userId,
        string $reason
    ): ServiceOrder {
        $serviceOrder = ServiceOrder::query()->find($serviceOrderId);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        $payment = ServiceOrderPayment::query()->find($paymentId);

        throw_if(is_null($payment), PaymentNotFoundException::class);

        throw_if(
            $payment->service_order_id !== $serviceOrder->id,
            PaymentNotFoundException::class
        );

        $serviceOrder = $this->paymentService->refundPayment(
            serviceOrder: $serviceOrder,
            payment: $payment,
            userId: $userId,
            reason: $reason
        );

        $serviceOrder->load(['payments', 'items', 'client', 'vehicle']);

        return $serviceOrder;
    }
}
