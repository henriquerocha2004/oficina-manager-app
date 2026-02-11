<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\Product\CreateProductAction;
use App\Actions\Tenant\Product\DeleteProductAction;
use App\Actions\Tenant\Product\FindOneProductAction;
use App\Actions\Tenant\Product\SearchProductAction;
use App\Actions\Tenant\Product\UpdateProductAction;
use App\Constants\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindRequest;
use App\Http\Requests\tenant\ProductRequest;
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
}
