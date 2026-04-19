<?php

namespace App\Http\Controllers\admin;

use App\Actions\Admin\Client\DeleteClientAction;
use App\Actions\Admin\Client\FindOneClientAction;
use App\Actions\Admin\Client\SearchClientAction;
use App\Actions\Admin\Client\UpdateClientAction;
use App\Constants\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\ClientRequest;
use App\Http\Requests\ClientTenantRequest;
use App\Http\Requests\FindRequest;
use App\Services\Admin\ClientTenantService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Admin/Clients/Index');
    }

    public function store(ClientTenantRequest $request, ClientTenantService $clientTenantService): JsonResponse
    {
        try {
            $result = $clientTenantService->create(
                clientDto: $request->toClientDto(),
                domain: $request->tenantDomain(),
                status: $request->tenantStatus(),
                trialUntil: $request->tenantTrialUntil(),
            );

            return $this->setResponse(
                message: Messages::ADMIN_CLIENT_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: $result,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_CLIENT_CREATION_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENT_CREATION_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function find(FindRequest $request, SearchClientAction $searchClientAction): JsonResponse
    {
        try {
            $clients = $searchClientAction($request->toDto());

            return $this->setResponse(
                message: Messages::ADMIN_CLIENTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['clients' => $clients],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_CLIENTS_FETCH_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENTS_FETCH_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(string $id, FindOneClientAction $findOneClientAction): JsonResponse
    {
        try {
            $client = $findOneClientAction($id);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['client' => $client],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_CLIENTS_FETCH_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENTS_FETCH_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(ClientRequest $request, string $id, UpdateClientAction $updateClientAction): JsonResponse
    {
        try {
            $updateClientAction($request->toDto(), $id);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENT_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_CLIENT_UPDATE_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENT_UPDATE_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(string $id, DeleteClientAction $deleteClientAction): JsonResponse
    {
        try {
            $deleteClientAction($id);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENT_DELETED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_CLIENT_DELETE_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_CLIENT_DELETE_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
