<?php

namespace App\Exceptions\Product;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductSupplierAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'This supplier is already attached to this product.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_CONFLICT, message: self::MESSAGE);
    }
}
