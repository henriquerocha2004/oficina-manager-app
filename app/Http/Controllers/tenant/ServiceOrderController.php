<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\ServiceOrder\AddItemAction;
use App\Actions\Tenant\ServiceOrder\ApproveAction;
use App\Actions\Tenant\ServiceOrder\CancelAction;
use App\Actions\Tenant\ServiceOrder\CreateServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\DeleteServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\FindOneServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\FinishWorkAction;
use App\Actions\Tenant\ServiceOrder\GetServiceOrderStatsAction;
use App\Actions\Tenant\ServiceOrder\RefundPaymentAction;
use App\Actions\Tenant\ServiceOrder\RegisterPaymentAction;
use App\Actions\Tenant\ServiceOrder\RemoveItemAction;
use App\Actions\Tenant\ServiceOrder\RequestNewApprovalAction;
use App\Actions\Tenant\ServiceOrder\SearchServiceOrderAction;
use App\Actions\Tenant\ServiceOrder\SendForApprovalAction;
use App\Actions\Tenant\ServiceOrder\StartWorkAction;
use App\Actions\Tenant\ServiceOrder\UpdateDiagnosisAction;
use App\Actions\Tenant\ServiceOrder\UpdateDiscountAction;
use App\Constants\Messages;
use App\Dto\ServiceOrderDto;
use App\Dto\ServiceOrderSearchDto;
use App\Exceptions\ServiceOrder\VehicleOwnershipConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\tenant\AddServiceOrderItemRequest;
use App\Http\Requests\tenant\CancelServiceOrderRequest;
use App\Http\Requests\tenant\RefundPaymentRequest;
use App\Http\Requests\tenant\RegisterPaymentRequest;
use App\Http\Requests\tenant\StoreServiceOrderRequest;
use App\Http\Requests\tenant\UpdateDiagnosisRequest;
use App\Http\Requests\tenant\UpdateDiscountRequest;
use App\Models\Tenant\ServiceOrderItem;
use App\Services\Tenant\ServiceOrder\ClientVehicleResolverService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
                    $resolved = $resolverService->resolve($request->validated());

                    $dto = new ServiceOrderDto(
                        client_id: $resolved->clientId,
                        vehicle_id: $resolved->vehicleId,
                        created_by: auth()->id(),
                        diagnosis: $request->input('diagnosis'),
                        observations: $request->input('observations'),
                        technician_id: $request->input('technician_id'),
                        discount: (float) $request->input('discount', 0),
                    );

                    $serviceOrder = $createAction($dto);
                    $serviceOrder->load(['client', 'vehicle', 'creator', 'technician']);

                    return $this->setResponse(
                        message: Messages::SERVICE_ORDER_CREATED_SUCCESS,
                        code: Response::HTTP_CREATED,
                        data: ['service_order' => $serviceOrder],
                    );
                }
            );
        } catch (VehicleOwnershipConflictException $exception) {
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

    public function find(Request $request, SearchServiceOrderAction $searchAction): JsonResponse
    {
        try {
            $searchDto = ServiceOrderSearchDto::fromArray($request->query());
            $serviceOrders = $searchAction($searchDto);

            return $this->setResponse(
                message: Messages::SERVICE_ORDERS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['service_orders' => $serviceOrders],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_SERVICE_ORDERS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SERVICE_ORDERS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(string $id, FindOneServiceOrderAction $findOneAction): JsonResponse
    {
        try {
            $serviceOrder = $findOneAction($id);

            return $this->setResponse(
                message: Messages::SERVICE_ORDERS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_SERVICE_ORDERS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SERVICE_ORDERS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(string $id, DeleteServiceOrderAction $deleteAction): JsonResponse
    {
        try {
            $deleteAction($id);

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_DELETED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DELETING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DELETING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function stats(GetServiceOrderStatsAction $statsAction): JsonResponse
    {
        try {
            $stats = $statsAction();

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_STATS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['stats' => $stats],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_SERVICE_ORDER_STATS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_SERVICE_ORDER_STATS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function sendForApproval(string $id, Request $request, SendForApprovalAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action(
                $id,
                auth()->id(),
                $request->input('diagnosis'),
                $request->input('items')
            );

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_SENT_FOR_APPROVAL,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function requestNewApproval(string $id, Request $request, RequestNewApprovalAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action(
                $id,
                auth()->id(),
                $request->input('diagnosis'),
                $request->input('items')
            );

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_SENT_FOR_APPROVAL,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function approve(string $id, ApproveAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action($id, auth()->id());

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_APPROVED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function startWork(string $id, Request $request, StartWorkAction $action): JsonResponse
    {
        try {
            $technicianId = $request->input('technician_id') ? (int) $request->input('technician_id') : null;
            $serviceOrder = $action($id, auth()->id(), $technicianId);

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_STARTED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function finishWork(string $id, FinishWorkAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action($id, auth()->id());

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_FINISHED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function cancel(string $id, CancelServiceOrderRequest $request, CancelAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action($id, auth()->id(), $request->input('reason'));

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_CANCELLED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function updateDiagnosis(string $id, UpdateDiagnosisRequest $request, UpdateDiagnosisAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action($id, auth()->id(), $request->input('diagnosis', ''));

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_DIAGNOSIS_UPDATED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function addItem(string $id, AddServiceOrderItemRequest $request, AddItemAction $action): JsonResponse
    {
        try {
            $itemDto = $request->toDto();

            $item = new ServiceOrderItem([
                'type' => $itemDto->type,
                'description' => $itemDto->description,
                'quantity' => $itemDto->quantity,
                'unit_price' => $itemDto->unit_price,
                'subtotal' => $itemDto->quantity * $itemDto->unit_price,
                'service_id' => $itemDto->service_id,
                'product_id' => $itemDto->product_id,
                'notes' => $itemDto->notes,
            ]);

            $serviceOrder = $action($id, auth()->id(), $item);

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_ITEM_ADDED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function removeItem(string $id, string $itemId, RemoveItemAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action($id, $itemId, auth()->id());

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_ITEM_REMOVED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function updateDiscount(string $id, UpdateDiscountRequest $request, UpdateDiscountAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action($id, auth()->id(), (float) $request->input('discount', 0));

            return $this->setResponse(
                message: Messages::SERVICE_ORDER_DISCOUNT_UPDATED,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_SERVICE_ORDER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_SERVICE_ORDER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function registerPayment(string $id, RegisterPaymentRequest $request, RegisterPaymentAction $action): JsonResponse
    {
        try {
            $dto = $request->toDto($id);

            $payment = $action($id, auth()->id(), $dto);

            return $this->setResponse(
                message: Messages::PAYMENT_REGISTERED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['payment' => $payment],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_REGISTERING_PAYMENT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_REGISTERING_PAYMENT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function refundPayment(string $id, string $paymentId, RefundPaymentRequest $request, RefundPaymentAction $action): JsonResponse
    {
        try {
            $serviceOrder = $action($id, $paymentId, auth()->id(), $request->input('reason'));

            return $this->setResponse(
                message: Messages::PAYMENT_REFUNDED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['service_order' => $serviceOrder],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_REFUNDING_PAYMENT, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_REFUNDING_PAYMENT,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
