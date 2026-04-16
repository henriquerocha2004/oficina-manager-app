<?php

namespace App\Exceptions\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClientAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'Client already exists.';

    public function __construct()
    {
        parent::__construct(Response::HTTP_CONFLICT, self::MESSAGE);
    }
}
