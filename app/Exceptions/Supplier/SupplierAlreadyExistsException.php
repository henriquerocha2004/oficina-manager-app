<?php

namespace App\Exceptions\Supplier;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SupplierAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'Supplier already exists.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_CONFLICT,
            message: self::MESSAGE
        );
    }
}
