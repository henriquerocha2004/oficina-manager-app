<?php

namespace App\Dto;

use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;

class ServiceOrderItemDto
{
    public function __construct(
        public ServiceOrderItemTypeEnum $type,
        public string $description,
        public int $quantity,
        public float $unit_price,
        public ?string $service_id = null,
        public ?string $product_id = null,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: ServiceOrderItemTypeEnum::from($data['type']),
            description: $data['description'],
            quantity: (int) $data['quantity'],
            unit_price: (float) $data['unit_price'],
            service_id: $data['service_id'] ?? null,
            product_id: $data['product_id'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->quantity * $this->unit_price,
            'service_id' => $this->service_id,
            'product_id' => $this->product_id,
            'notes' => $this->notes,
        ], fn ($value) => ! is_null($value));
    }
}
