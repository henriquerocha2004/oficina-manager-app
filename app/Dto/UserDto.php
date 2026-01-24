<?php

namespace App\Dto;

class UserDto
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
    ) {
    }
}
