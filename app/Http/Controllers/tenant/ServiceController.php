<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\Service\CreateServiceAction;
use App\Actions\Tenant\Service\DeleteServiceAction;
use App\Actions\Tenant\Service\FindOneServiceAction;
use App\Actions\Tenant\Service\SearchServiceAction;
use App\Actions\Tenant\Service\UpdateServiceAction;
use App\Constants\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindRequest;
use App\Http\Requests\tenant\ServiceRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class ServiceController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('Tenant/Services/Index');
    }

    public function store(ServiceRequest $request, CreateServiceAction $createServiceAction): JsonResponse
    {
        try {
            $service = $createServiceAction(
                serviceDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::SERVICE_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['service' => $service],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_SERVICE, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_SERVICE,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function find(FindRequest $request, SearchServiceAction $searchServiceAction): JsonResponse
    {
        try {
            $services = $searchServiceAction(
                searchDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::SERVICES_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['services' => $services],
            );
        } catch (Exception $exception) {
            dd($exception);
            Log::error(Messages::ERROR_FETCHING_SERVICES, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SERVICES,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(ServiceRequest $request, string $id, UpdateServiceAction $updateServiceAction): JsonResponse
    {
        try {
            $serviceId = Ulid::fromString($id);
            $updateServiceAction(
                serviceDto: $request->toDto(),
                serviceId: $serviceId,
            );

            return $this->setResponse(
                message: Messages::SERVICE_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(string $id, DeleteServiceAction $deleteServiceAction): JsonResponse
    {
        try {
            $serviceId = Ulid::fromString($id);
            $deleteServiceAction($serviceId);

            return $this->setResponse(
                message: Messages::SERVICE_DELETED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DELETING_SERVICE, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DELETING_SERVICE,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(string $id, FindOneServiceAction $findOneServiceAction): JsonResponse
    {
        try {
            $serviceId = Ulid::fromString($id);
            $service = $findOneServiceAction($serviceId);

            return $this->setResponse(
                message: Messages::SERVICES_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['service' => $service],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_SERVICES, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SERVICES,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
