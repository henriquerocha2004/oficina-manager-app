<?php

namespace App\Exceptions\Product;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductSupplierNotFoundException extends HttpException
{
    private const string MESSAGE = 'This supplier is not attached to this product.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_NOT_FOUND, message: self::MESSAGE);
    }
}
