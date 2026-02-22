<?php

namespace App\Exceptions\ServiceOrder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceOrderNotFoundException extends HttpException
{
    private const string MESSAGE = 'Service order not found.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_NOT_FOUND,
            message: self::MESSAGE
        );
    }
}
