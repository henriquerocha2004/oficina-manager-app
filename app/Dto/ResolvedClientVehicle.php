<?php

namespace App\Dto;

class ResolvedClientVehicle
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $vehicleId,
    ) {
    }
}
