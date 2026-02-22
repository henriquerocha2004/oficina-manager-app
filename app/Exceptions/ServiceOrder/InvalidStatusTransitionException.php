<?php

namespace App\Exceptions\ServiceOrder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidStatusTransitionException extends HttpException
{
    private const string MESSAGE = 'Invalid status transition for service order.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: self::MESSAGE
        );
    }
}
