<?php

namespace App\Dto;

class VehicleDto
{
    public function __construct(
        public string $license_plate,
        public string $brand,
        public string $model,
        public int $year,
        public string $client_id,
        public ?string $color = null,
        public ?string $vin = null,
        public ?string $fuel = null,
        public ?string $transmission = null,
        public ?int $mileage = null,
        public ?string $cilinder_capacity = null,
        public ?string $vehicle_type = 'car',
        public ?string $observations = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            license_plate: $data['license_plate'],
            brand: $data['brand'],
            model: $data['model'],
            year: $data['year'],
            client_id: $data['client_id'],
            color: $data['color'] ?? null,
            vin: $data['vin'] ?? null,
            fuel: $data['fuel'] ?? null,
            transmission: $data['transmission'] ?? null,
            mileage: $data['mileage'] ?? null,
            cilinder_capacity: $data['cilinder_capacity'] ?? null,
            vehicle_type: $data['vehicle_type'] ?? 'car',
            observations: $data['observations'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'license_plate' => $this->license_plate,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'client_id' => $this->client_id,
            'color' => $this->color,
            'vin' => $this->vin,
            'fuel' => $this->fuel,
            'transmission' => $this->transmission,
            'mileage' => $this->mileage,
            'cilinder_capacity' => $this->cilinder_capacity,
            'vehicle_type' => $this->vehicle_type,
            'observations' => $this->observations,
        ], fn($value) => !is_null($value));
    }
}
