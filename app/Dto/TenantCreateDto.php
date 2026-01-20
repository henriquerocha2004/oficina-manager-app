<?php

namespace App\Dto;

class TenantCreateDto
{
    public function __construct(
        public string $name,
        public string $document,
        public string $email,
        public string $domain,
    ) {
    }
}
