<?php

namespace App\Exceptions\Product;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductNotFoundException extends HttpException
{
    private const string MESSAGE = 'Product not found.';

    public function __construct()
    {
        parent::__construct(statusCode: Response::HTTP_NOT_FOUND, message: self::MESSAGE);
    }
}
