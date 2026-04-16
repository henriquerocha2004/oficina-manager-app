<?php

namespace App\Dto\Admin;

class ClientDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $document,
        public ?string $phone = null,
        public ?string $street = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zip_code = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            document: $data['document'],
            phone: $data['phone'] ?? null,
            street: $data['street'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zip_code: $data['zip_code'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'document' => $this->document,
            'phone' => $this->phone,
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
        ], fn($value) => !is_null($value));
    }
}
