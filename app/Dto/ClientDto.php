<?php

namespace App\Dto;

class ClientDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $document_number,
        public string $phone,
        public ?string $street = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zip_code = null,
        public ?string $observations = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            document_number: $data['document_number'],
            phone: $data['phone'],
            street: $data['street'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zip_code: $data['zip_code'] ?? null,
            observations: $data['observations'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'document_number' => $this->document_number,
            'phone' => $this->phone,
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'observations' => $this->observations,
        ], fn($value) => !is_null($value));
    }
}
