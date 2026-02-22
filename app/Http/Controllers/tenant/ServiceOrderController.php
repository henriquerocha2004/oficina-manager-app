<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Constants\Messages;
use App\Dto\ServiceOrderDto;
use App\Exceptions\ServiceOrder\VehicleOwnershipConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\tenant\StoreServiceOrderRequest;
use App\Services\Tenant\ServiceOrder\ClientVehicleResolverService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ServiceOrderController extends Controller
{
    public function store(
        StoreServiceOrderRequest $request,
        ClientVehicleResolverService $resolverService,
        CreateServiceOrderAction $createAction
    ): JsonResponse {
        try {
            return DB::connection(config('database.connections_names.tenant'))->transaction(
                function () use ($request, $resolverService, $createAction) {
                    // 1. Resolve cliente e veículo (cria/transfere se necessário)
                    $resolved = $resolverService->resolve($request->validated());

                    // 2. Cria DTO da OS
                    $dto = new ServiceOrderDto(
                        client_id: $resolved->clientId,
                        vehicle_id: $resolved->vehicleId,
                        created_by: auth()->id(),
                        diagnosis: $request->input('diagnosis'),
                        observations: $request->input('observations'),
                        technician_id: $request->input('technician_id'),
                        discount: (float) $request->input('discount', 0),
                    );

                    // 3. Cria a OS
                    $serviceOrder = $createAction($dto);

                    // 4. Carrega relacionamentos para retornar
                    $serviceOrder->load(['client', 'vehicle', 'creator', 'technician']);

                    return $this->setResponse(
                        message: Messages::SERVICE_ORDER_CREATED_SUCCESS,
                        code: Response::HTTP_CREATED,
                        data: ['service_order' => $serviceOrder],
                    );
                }
            );
        } catch (VehicleOwnershipConflictException $exception) {
            // Exceção específica: veículo pertence a outro cliente
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_CONFLICT,
                data: [
                    'vehicle_id' => $exception->vehicleId,
                    'current_owner' => [
                        'id' => $exception->currentOwnerId,
                        'name' => $exception->currentOwnerName,
                    ],
                ],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
