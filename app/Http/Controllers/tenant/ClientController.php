<?php

namespace App\Http\Controllers\tenant;

use Exception;
use Throwable;
use App\Constants\Messages;
use Symfony\Component\Uid\Ulid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindClientsRequest;
use App\Http\Requests\tenant\ClientRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Actions\tenant\Client\CreateClientAction;
use App\Actions\Tenant\Client\DeleteClientAction;
use App\Actions\Tenant\Client\FindOneAction;
use App\Actions\Tenant\Client\SearchClientAction;
use App\Actions\Tenant\Client\UpdateClientAction;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ClientController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render("Tenant/Clients/Index");
    }

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

    public function find(FindClientsRequest $request, SearchClientAction $searchClientAction): JsonResponse
    {
        try {
            $clients = $searchClientAction(
                searchDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::CLIENTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['clients' => $clients],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_CLIENTS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_CLIENTS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(ClientRequest $request, int $id, UpdateClientAction $updateClientAction): JsonResponse
    {
        try {
            $clientId =  Ulid::fromString($id);
            $updateClientAction(
                clientDto: $request->toDto(),
                clientId: $clientId,
            );

            return $this->setResponse(
                message: Messages::CLIENT_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_CLIENT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_CLIENT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(int $id, DeleteClientAction $deleteClientAction): JsonResponse
    {
        try {
             $clientId =  Ulid::fromString($id);
             $deleteClientAction($clientId);

             return $this->setResponse(
                 message: Messages::CLIENT_DELETED_SUCCESS,
                 code: Response::HTTP_OK,
             );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DELETING_CLIENT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DELETING_CLIENT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(int $id, FindOneAction $findOneAction): JsonResponse
    {
        try {
            $clientId = Ulid::fromString($id);
            $client = $findOneAction($clientId);

            return $this->setResponse(
                message: Messages::CLIENTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['client' => $client],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_CLIENTS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_CLIENTS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
