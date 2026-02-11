<?php

namespace App\Dto;

class ProductDto
{
    public function __construct(
        public string $name,
        public string $category,
        public string $unit,
        public float $unit_price,
        public ?string $description = null,
        public ?string $sku = null,
        public ?string $barcode = null,
        public ?string $manufacturer = null,
        public ?int $min_stock_level = null,
        public ?float $suggested_price = null,
        public ?bool $is_active = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            category: $data['category'],
            unit: $data['unit'],
            unit_price: (float) $data['unit_price'],
            description: $data['description'] ?? null,
            sku: $data['sku'] ?? null,
            barcode: $data['barcode'] ?? null,
            manufacturer: $data['manufacturer'] ?? null,
            min_stock_level: isset($data['min_stock_level']) ? (int) $data['min_stock_level'] : null,
            suggested_price: isset($data['suggested_price']) ? (float) $data['suggested_price'] : null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'manufacturer' => $this->manufacturer,
            'category' => $this->category,
            'min_stock_level' => $this->min_stock_level,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'suggested_price' => $this->suggested_price,
            'is_active' => $this->is_active,
        ], fn ($value) => ! is_null($value));
    }
}
