<?php

namespace App\Exceptions\ServiceOrder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VehicleOwnershipConflictException extends HttpException
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $currentOwnerId,
        public readonly string $currentOwnerName,
    ) {
        parent::__construct(
            Response::HTTP_CONFLICT,
            'Vehicle belongs to another client. Confirm transfer to proceed.'
        );
    }
}
