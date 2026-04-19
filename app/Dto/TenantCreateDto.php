<?php

namespace App\Dto;

class TenantCreateDto
{
    public function __construct(
        public string $name,
        public string $document,
        public string $email,
        public string $domain,
        public string $status = 'active',
        public ?string $trade_name = null,
        public ?string $trial_until = null,
        public ?string $client_id = null,
    ) {
    }
}
