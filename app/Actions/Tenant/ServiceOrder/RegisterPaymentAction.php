<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Dto\PaymentDto;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderPayment;
use App\Services\Tenant\ServiceOrder\PaymentService;
use Throwable;

readonly class RegisterPaymentAction
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(string $serviceOrderId, int $userId, PaymentDto $dto): ServiceOrderPayment
    {
        $serviceOrder = ServiceOrder::query()->find($serviceOrderId);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        $payment = $this->paymentService->registerPayment(
            serviceOrder: $serviceOrder,
            userId: $userId,
            amount: $dto->amount,
            paymentMethod: $dto->payment_method,
            notes: $dto->notes
        );

        $serviceOrder->refresh();
        $serviceOrder->load(['payments', 'items', 'client', 'vehicle']);

        return $payment;
    }
}
