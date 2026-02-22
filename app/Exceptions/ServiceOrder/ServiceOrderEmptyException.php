<?php

namespace App\Exceptions\ServiceOrder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceOrderEmptyException extends HttpException
{
    private const string MESSAGE = 'Service order must have at least one item before approval.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: self::MESSAGE
        );
    }
}
