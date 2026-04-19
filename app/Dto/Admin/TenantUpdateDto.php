<?php

namespace App\Dto\Admin;

class TenantUpdateDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $domain,
        public string $status,
        public ?string $trade_name = null,
        public ?string $client_id = null,
        public ?string $trial_until = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            domain: $data['domain'],
            status: $data['status'],
            trade_name: $data['trade_name'] ?? null,
            client_id: $data['client_id'] ?? null,
            trial_until: $data['trial_until'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'trade_name' => $this->trade_name,
            'email' => $this->email,
            'domain' => $this->domain,
            'status' => $this->status,
            'client_id' => $this->client_id,
            'trial_until' => $this->trial_until,
        ], fn($value) => !is_null($value));
    }
}
