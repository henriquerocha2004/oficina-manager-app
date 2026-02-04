<?php

namespace App\Exceptions\Client;

use Exception;
use Throwable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClientNotFoundException extends HttpException
{
    private const string  MESSAGE = 'Client not found.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_NOT_FOUND, message: self::MESSAGE);
    }
}
