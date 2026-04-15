<?php

namespace App\Exceptions\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClientNotFoundException extends HttpException
{
    private const string MESSAGE = 'Client not found.';

    public function __construct()
    {
        parent::__construct(Response::HTTP_NOT_FOUND, self::MESSAGE);
    }
}
