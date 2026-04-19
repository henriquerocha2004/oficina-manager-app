<?php

namespace App\Http\Controllers\admin;

use App\Actions\Admin\Tenant\DeleteTenantAction;
use App\Actions\Admin\Tenant\FindOneTenantAction;
use App\Actions\Admin\Tenant\SearchTenantAction;
use App\Actions\Admin\Tenant\UpdateTenantAction;
use App\Constants\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\TenantRequest;
use App\Http\Requests\FindRequest;
use App\Services\Admin\TenantProvisioningService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TenantController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Admin/Tenants/Index');
    }

    /**
     * @throws Throwable
     */
    public function store(TenantRequest $request, TenantProvisioningService $tenantProvisioningService): JsonResponse
    {
        try {
            $tenant = $tenantProvisioningService->create($request->toCreateDto());

            return $this->setResponse(
                message: Messages::ADMIN_TENANT_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['tenant' => $tenant],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_TENANT_CREATION_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_TENANT_CREATION_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function find(FindRequest $request, SearchTenantAction $searchTenantAction): JsonResponse
    {
        try {
            $tenants = $searchTenantAction($request->toDto());

            return $this->setResponse(
                message: Messages::ADMIN_TENANTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['tenants' => $tenants],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_TENANTS_FETCH_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_TENANTS_FETCH_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(int $id, FindOneTenantAction $findOneTenantAction): JsonResponse
    {
        try {
            $tenant = $findOneTenantAction($id);

            return $this->setResponse(
                message: Messages::ADMIN_TENANTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['tenant' => $tenant],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_TENANTS_FETCH_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_TENANTS_FETCH_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(TenantRequest $request, int $id, UpdateTenantAction $updateTenantAction): JsonResponse
    {
        try {
            $updateTenantAction($request->toUpdateDto(), $id);

            return $this->setResponse(
                message: Messages::ADMIN_TENANT_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_TENANT_UPDATE_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_TENANT_UPDATE_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(int $id, DeleteTenantAction $deleteTenantAction): JsonResponse
    {
        try {
            $deleteTenantAction($id);

            return $this->setResponse(
                message: Messages::ADMIN_TENANT_DELETED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ADMIN_TENANT_DELETE_ERROR, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ADMIN_TENANT_DELETE_ERROR,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
