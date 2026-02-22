<?php

namespace App\Dto;

class ServiceOrderDto
{
    public function __construct(
        public string $client_id,
        public string $vehicle_id,
        public int $created_by,
        public ?string $diagnosis = null,
        public ?string $observations = null,
        public ?int $technician_id = null,
        public float $discount = 0.0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            client_id: $data['client_id'],
            vehicle_id: $data['vehicle_id'],
            created_by: $data['created_by'],
            diagnosis: $data['diagnosis'] ?? null,
            observations: $data['observations'] ?? null,
            technician_id: isset($data['technician_id']) ? (int) $data['technician_id'] : null,
            discount: isset($data['discount']) ? (float) $data['discount'] : 0.0,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'client_id' => $this->client_id,
            'vehicle_id' => $this->vehicle_id,
            'created_by' => $this->created_by,
            'diagnosis' => $this->diagnosis,
            'observations' => $this->observations,
            'technician_id' => $this->technician_id,
            'discount' => $this->discount,
        ], fn ($value) => ! is_null($value));
    }
}
