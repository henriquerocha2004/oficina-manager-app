<?php

namespace App\Dto;

class ProductSupplierDto
{
    public function __construct(
        public string $supplier_id,
        public float $cost_price,
        public ?string $supplier_sku = null,
        public ?int $lead_time_days = null,
        public ?int $min_order_quantity = 1,
        public ?bool $is_preferred = false,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            supplier_id: $data['supplier_id'],
            cost_price: (float) $data['cost_price'],
            supplier_sku: $data['supplier_sku'] ?? null,
            lead_time_days: isset($data['lead_time_days']) ? (int) $data['lead_time_days'] : null,
            min_order_quantity: isset($data['min_order_quantity']) ? (int) $data['min_order_quantity'] : 1,
            is_preferred: isset($data['is_preferred']) && $data['is_preferred'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'supplier_id' => $this->supplier_id,
            'supplier_sku' => $this->supplier_sku,
            'cost_price' => $this->cost_price,
            'lead_time_days' => $this->lead_time_days,
            'min_order_quantity' => $this->min_order_quantity,
            'is_preferred' => $this->is_preferred,
            'notes' => $this->notes,
        ], fn ($value) => ! is_null($value));
    }
}
