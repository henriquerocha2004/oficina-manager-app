<?php

namespace App\Exceptions\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserNotFoundException extends HttpException
{
    private const string MESSAGE = 'User not found.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_NOT_FOUND,
            message: self::MESSAGE,
        );
    }
}
