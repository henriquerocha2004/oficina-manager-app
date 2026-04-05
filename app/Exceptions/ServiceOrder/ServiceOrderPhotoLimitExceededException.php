<?php

namespace App\Exceptions\ServiceOrder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceOrderPhotoLimitExceededException extends HttpException
{
    private const string MESSAGE = 'Limite de 5 fotos por ordem de serviço excedido.';

    public function __construct()
    {
        parent::__construct(
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: self::MESSAGE
        );
    }
}
