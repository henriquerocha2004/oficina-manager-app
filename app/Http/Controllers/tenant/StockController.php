<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\Stock\MoveStockAction;
use App\Constants\Messages;
use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Stock\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\tenant\StockMovementRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StockController extends Controller
{
    /**
     * @throws Throwable
     */
    public function move(
        StockMovementRequest $request,
        string $productId,
        MoveStockAction $moveStockAction
    ): JsonResponse {
        try {
            $moveStockAction($request->toDto());

            return $this->setResponse(
                message: Messages::STOCK_MOVEMENT_SUCCESS,
                code: Response::HTTP_CREATED,
            );
        } catch (ProductNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (InsufficientStockException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_STOCK_MOVEMENT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_STOCK_MOVEMENT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
