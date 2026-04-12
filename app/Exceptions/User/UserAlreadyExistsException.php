<?php

namespace App\Exceptions\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'User already exists.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_CONFLICT,
            message: self::MESSAGE,
        );
    }
}
