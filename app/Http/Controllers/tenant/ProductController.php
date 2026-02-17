<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\Product\AttachSupplierToProductAction;
use App\Actions\Tenant\Product\CreateProductAction;
use App\Actions\Tenant\Product\DeleteProductAction;
use App\Actions\Tenant\Product\DetachSupplierFromProductAction;
use App\Actions\Tenant\Product\FindOneProductAction;
use App\Actions\Tenant\Product\GetProductStatsAction;
use App\Actions\Tenant\Product\SearchProductAction;
use App\Actions\Tenant\Product\UpdateProductAction;
use App\Actions\Tenant\Product\UpdateProductSupplierAction;
use App\Constants\Messages;
use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Product\ProductSupplierAlreadyExistsException;
use App\Exceptions\Product\ProductSupplierNotFoundException;
use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindRequest;
use App\Http\Requests\tenant\AttachSupplierToProductRequest;
use App\Http\Requests\tenant\ProductRequest;
use App\Http\Requests\tenant\UpdateProductSupplierRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;
use Throwable;

class ProductController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Tenant/Products/Index');
    }

    /**
     * @throws Throwable
     */
    public function store(ProductRequest $request, CreateProductAction $createProductAction): JsonResponse
    {
        try {
            $product = $createProductAction(
                productDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::PRODUCT_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['product' => $product],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_PRODUCT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_PRODUCT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function find(FindRequest $request, SearchProductAction $searchProductAction): JsonResponse
    {
        try {
            $products = $searchProductAction(
                searchDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::PRODUCTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['products' => $products],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_PRODUCTS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_PRODUCTS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(ProductRequest $request, string $id, UpdateProductAction $updateProductAction): JsonResponse
    {
        try {
            $productId = Ulid::fromString($id);
            $updateProductAction(
                productDto: $request->toDto(),
                productId: $productId,
            );

            return $this->setResponse(
                message: Messages::PRODUCT_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_PRODUCT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_PRODUCT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(string $id, DeleteProductAction $deleteProductAction): JsonResponse
    {
        try {
            $productId = Ulid::fromString($id);
            $deleteProductAction($productId);

            return $this->setResponse(
                message: Messages::PRODUCT_DELETED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DELETING_PRODUCT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DELETING_PRODUCT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(string $id, FindOneProductAction $findOneProductAction): JsonResponse
    {
        try {
            $productId = Ulid::fromString($id);
            $product = $findOneProductAction($productId);

            return $this->setResponse(
                message: Messages::PRODUCTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['product' => $product],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_PRODUCTS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_PRODUCTS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function attachSupplier(
        AttachSupplierToProductRequest $request,
        string $id,
        AttachSupplierToProductAction $attachSupplierToProductAction
    ): JsonResponse {
        try {
            $productId = Ulid::fromString($id);
            $product = $attachSupplierToProductAction(
                productSupplierDto: $request->toDto(),
                productId: $productId,
            );

            return $this->setResponse(
                message: Messages::PRODUCT_SUPPLIER_ATTACHED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['product' => $product],
            );
        } catch (ProductNotFoundException|SupplierNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (ProductSupplierAlreadyExistsException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_CONFLICT,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_ATTACHING_SUPPLIER_TO_PRODUCT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_ATTACHING_SUPPLIER_TO_PRODUCT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function updateSupplier(
        UpdateProductSupplierRequest $request,
        string $productId,
        string $supplierId,
        UpdateProductSupplierAction $updateProductSupplierAction
    ): JsonResponse {
        try {
            $productIdUlid = Ulid::fromString($productId);
            $product = $updateProductSupplierAction(
                productSupplierDto: $request->toDto(),
                productId: $productIdUlid,
            );

            return $this->setResponse(
                message: Messages::PRODUCT_SUPPLIER_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['product' => $product],
            );
        } catch (ProductNotFoundException|SupplierNotFoundException|ProductSupplierNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_PRODUCT_SUPPLIER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_PRODUCT_SUPPLIER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function detachSupplier(
        string $productId,
        string $supplierId,
        DetachSupplierFromProductAction $detachSupplierFromProductAction
    ): JsonResponse {
        try {
            $productIdUlid = Ulid::fromString($productId);
            $supplierIdUlid = Ulid::fromString($supplierId);
            $detachSupplierFromProductAction($productIdUlid, $supplierIdUlid);

            return $this->setResponse(
                message: Messages::PRODUCT_SUPPLIER_DETACHED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (ProductNotFoundException|SupplierNotFoundException|ProductSupplierNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DETACHING_SUPPLIER_FROM_PRODUCT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DETACHING_SUPPLIER_FROM_PRODUCT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function stats(GetProductStatsAction $getProductStatsAction): JsonResponse
    {
        try {
            $stats = $getProductStatsAction();

            return $this->setResponse(
                message: Messages::PRODUCT_STATS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['stats' => $stats],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_PRODUCT_STATS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_PRODUCT_STATS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
