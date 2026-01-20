<?php

namespace App\Http\Controllers\tenant;

use App\Actions\tenant\Client\CreateClientAction;
use App\Constants\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\tenant\ClientRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ClientController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(ClientRequest $request, CreateClientAction $createClientAction): JsonResponse
    {
        try {
            $client = $createClientAction(
                clientDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::CLIENT_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['client' => $client],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_CLIENT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_CLIENT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function index(): string
    {
        return "Login Teanant";
    }
}
