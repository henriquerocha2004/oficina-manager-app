<?php

namespace App\Exceptions\Product;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'Product with this SKU already exists.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_CONFLICT,
            message: self::MESSAGE
        );
    }
}
