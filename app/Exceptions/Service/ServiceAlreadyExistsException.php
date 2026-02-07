<?php

namespace App\Exceptions\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'Service already exists.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_CONFLICT,
            message: self::MESSAGE
        );
    }
}
