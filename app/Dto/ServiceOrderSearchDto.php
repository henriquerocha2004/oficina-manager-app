<?php

namespace App\Dto;

use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;

class ServiceOrderSearchDto
{
    public function __construct(
        public ?string $search = null,
        public int $per_page = 15,
        public string $sort_by = 'created_at',
        public string $sort_direction = 'desc',
        public ?ServiceOrderStatusEnum $status = null,
        public ?string $client_id = null,
        public ?string $vehicle_id = null,
        public ?int $technician_id = null,
        public ?string $order_number = null,
        public ?string $date_from = null,
        public ?string $date_to = null,
        public ?string $client_name = null,
        public ?string $plate = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            per_page: isset($data['per_page']) ? (int) $data['per_page'] : 15,
            sort_by: $data['sort_by'] ?? 'created_at',
            sort_direction: $data['sort_direction'] ?? 'desc',
            status: isset($data['status']) ? ServiceOrderStatusEnum::from($data['status']) : null,
            client_id: $data['client_id'] ?? null,
            vehicle_id: $data['vehicle_id'] ?? null,
            technician_id: isset($data['technician_id']) ? (int) $data['technician_id'] : null,
            order_number: $data['order_number'] ?? null,
            date_from: $data['date_from'] ?? null,
            date_to: $data['date_to'] ?? null,
            client_name: $data['client_name'] ?? null,
            plate: $data['plate'] ?? null,
        );
    }
}
