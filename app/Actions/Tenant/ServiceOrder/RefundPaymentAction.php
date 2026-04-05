<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
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
        int $userId,
        float $amount,
        PaymentMethodEnum $returnMethod,
        ?string $reason = null
    ): ServiceOrder {
        $serviceOrder = ServiceOrder::query()->find($serviceOrderId);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        $serviceOrder = $this->paymentService->refundPayment(
            serviceOrder: $serviceOrder,
            userId: $userId,
            amount: $amount,
            returnMethod: $returnMethod,
            reason: $reason
        );

        $serviceOrder->load(['payments', 'items', 'client', 'vehicle', 'events.user']);

        return $serviceOrder;
    }
}
