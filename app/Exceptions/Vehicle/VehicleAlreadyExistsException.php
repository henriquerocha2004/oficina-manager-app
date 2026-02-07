<?php

namespace App\Exceptions\Vehicle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VehicleAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'Vehicle already exists.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_CONFLICT, message: self::MESSAGE);
    }
}
