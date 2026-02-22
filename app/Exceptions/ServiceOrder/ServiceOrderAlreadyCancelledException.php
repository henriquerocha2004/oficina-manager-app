<?php

namespace App\Exceptions\ServiceOrder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceOrderAlreadyCancelledException extends HttpException
{
    private const string MESSAGE = 'Service order is already cancelled and cannot be modified.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: self::MESSAGE
        );
    }
}
