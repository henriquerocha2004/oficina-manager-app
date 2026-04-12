<?php

namespace App\Dto;

class UserDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $role,
        public ?string $password = null,
        public ?bool $is_active = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            role: $data['role'],
            password: $data['password'] ?? null,
            is_active: $data['is_active'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'password' => $this->password,
            'is_active' => $this->is_active,
        ], fn ($value) => !is_null($value));
    }
}
