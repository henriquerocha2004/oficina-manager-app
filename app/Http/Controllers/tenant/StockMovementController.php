<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\StockMovement\GetStockMovementStatsAction;
use App\Actions\Tenant\StockMovement\SearchStockMovementAction;
use App\Constants\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class StockMovementController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Tenant/StockMovements/Index');
    }

    public function find(FindRequest $request, SearchStockMovementAction $searchStockMovementAction): JsonResponse
    {
        try {
            $movements = $searchStockMovementAction(
                searchDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::STOCK_MOVEMENTS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['movements' => $movements],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_STOCK_MOVEMENTS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_STOCK_MOVEMENTS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function stats(GetStockMovementStatsAction $getStockMovementStatsAction): JsonResponse
    {
        try {
            $stats = $getStockMovementStatsAction();

            return $this->setResponse(
                message: Messages::STOCK_MOVEMENT_STATS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: $stats,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_STOCK_MOVEMENT_STATS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_STOCK_MOVEMENT_STATS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
