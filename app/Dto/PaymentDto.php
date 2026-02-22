<?php

namespace App\Dto;

use App\Enum\Tenant\ServiceOrder\PaymentMethodEnum;

class PaymentDto
{
    public function __construct(
        public string $service_order_id,
        public PaymentMethodEnum $payment_method,
        public float $amount,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            service_order_id: $data['service_order_id'],
            payment_method: PaymentMethodEnum::from($data['payment_method']),
            amount: (float) $data['amount'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'service_order_id' => $this->service_order_id,
            'payment_method' => $this->payment_method->value,
            'amount' => $this->amount,
            'notes' => $this->notes,
        ], fn ($value) => ! is_null($value));
    }
}
