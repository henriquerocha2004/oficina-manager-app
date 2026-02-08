<?php

namespace App\Exceptions\Supplier;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SupplierNotFoundException extends HttpException
{
    private const string MESSAGE = 'Supplier not found.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_NOT_FOUND,
            message: self::MESSAGE
        );
    }
}
