<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\Supplier\CreateSupplierAction;
use App\Actions\Tenant\Supplier\DeleteSupplierAction;
use App\Actions\Tenant\Supplier\FindOneSupplierAction;
use App\Actions\Tenant\Supplier\GetSupplierStatsAction;
use App\Actions\Tenant\Supplier\SearchSupplierAction;
use App\Actions\Tenant\Supplier\UpdateSupplierAction;
use App\Constants\Messages;
use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindRequest;
use App\Http\Requests\tenant\SupplierRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;
use Throwable;

class SupplierController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Tenant/Suppliers/Index');
    }

    /**
     * @throws Throwable
     */
    public function store(SupplierRequest $request, CreateSupplierAction $createSupplierAction): JsonResponse
    {
        try {
            $supplier = $createSupplierAction(
                supplierDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::SUPPLIER_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['supplier' => $supplier],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_SUPPLIER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_SUPPLIER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function find(FindRequest $request, SearchSupplierAction $searchSupplierAction): JsonResponse
    {
        try {
            $suppliers = $searchSupplierAction(
                searchDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::SUPPLIERS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['suppliers' => $suppliers],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_SUPPLIERS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SUPPLIERS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(SupplierRequest $request, string $id, UpdateSupplierAction $updateSupplierAction): JsonResponse
    {
        try {
            $supplierId = Ulid::fromString($id);
            $updateSupplierAction(
                supplierDto: $request->toDto(),
                supplierId: $supplierId,
            );

            return $this->setResponse(
                message: Messages::SUPPLIER_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (SupplierNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SUPPLIER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SUPPLIER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(string $id, DeleteSupplierAction $deleteSupplierAction): JsonResponse
    {
        try {
            $supplierId = Ulid::fromString($id);
            $deleteSupplierAction($supplierId);

            return $this->setResponse(
                message: Messages::SUPPLIER_DELETED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (SupplierNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DELETING_SUPPLIER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DELETING_SUPPLIER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(string $id, FindOneSupplierAction $findOneAction): JsonResponse
    {
        try {
            $supplierId = Ulid::fromString($id);
            $supplier = $findOneAction($supplierId);

            return $this->setResponse(
                message: Messages::SUPPLIERS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['supplier' => $supplier],
            );
        } catch (SupplierNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_SUPPLIERS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SUPPLIERS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function stats(GetSupplierStatsAction $getSupplierStatsAction): JsonResponse
    {
        try {
            $stats = $getSupplierStatsAction();

            return $this->setResponse(
                message: Messages::SUPPLIER_STATS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['stats' => $stats],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_SUPPLIER_STATS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SUPPLIER_STATS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
