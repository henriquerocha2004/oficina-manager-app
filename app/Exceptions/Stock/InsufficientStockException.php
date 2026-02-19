<?php

namespace App\Exceptions\Stock;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InsufficientStockException extends HttpException
{
    private const string MESSAGE = 'Insufficient stock for this operation.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_UNPROCESSABLE_ENTITY, message: self::MESSAGE);
    }
}
