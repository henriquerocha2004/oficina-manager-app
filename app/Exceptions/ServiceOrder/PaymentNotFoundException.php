<?php

namespace App\Exceptions\ServiceOrder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentNotFoundException extends HttpException
{
    private const string MESSAGE = 'Payment not found.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_NOT_FOUND,
            message: self::MESSAGE
        );
    }
}
