<?php

namespace App\Exceptions\Vehicle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VehicleNotFoundException extends HttpException
{
    private const string MESSAGE = 'Vehicle not found.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_NOT_FOUND, message: self::MESSAGE);
    }
}
