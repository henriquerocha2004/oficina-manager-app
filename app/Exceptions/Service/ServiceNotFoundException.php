<?php

namespace App\Exceptions\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceNotFoundException extends HttpException
{
    private const string MESSAGE = 'Service not found.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_NOT_FOUND, message: self::MESSAGE);
    }
}
