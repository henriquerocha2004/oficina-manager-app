<?php

namespace App\Dto;

class ServiceDto
{
    public function __construct(
        public string $name,
        public float $base_price,
        public string $category,
        public ?string $description = null,
        public ?int $estimated_time = null,
        public ?bool $is_active = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            base_price: $data['base_price'],
            category: $data['category'],
            description: $data['description'] ?? null,
            estimated_time: $data['estimated_time'] ?? null,
            is_active: $data['is_active'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'category' => $this->category,
            'estimated_time' => $this->estimated_time,
            'is_active' => $this->is_active,
        ], fn($value) => !is_null($value));
    }
}
