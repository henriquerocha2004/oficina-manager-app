<?php

namespace App\Exceptions\Tenant;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantAlreadyExistsException extends HttpException
{
    private const string MESSAGE = 'Tenant already exists.';

    public function __construct()
    {
        parent::__construct(Response::HTTP_CONFLICT, self::MESSAGE);
    }
}
