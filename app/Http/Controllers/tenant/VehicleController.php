<?php

namespace App\Http\Controllers\tenant;

use Exception;
use Throwable;
use App\Constants\Messages;
use Symfony\Component\Uid\Ulid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindRequest;
use App\Http\Requests\tenant\VehicleRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Actions\Tenant\Vehicle\CreateVehicleAction;
use App\Actions\Tenant\Vehicle\DeleteVehicleAction;
use App\Actions\Tenant\Vehicle\FindOneAction;
use App\Actions\Tenant\Vehicle\SearchVehicleAction;
use App\Actions\Tenant\Vehicle\UpdateVehicleAction;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class VehicleController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render("Tenant/Vehicles/Index");
    }

    /**
     * @throws Throwable
     */
    public function store(VehicleRequest $request, CreateVehicleAction $createVehicleAction): JsonResponse
    {
        try {
            $vehicle = $createVehicleAction(
                vehicleDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::VEHICLE_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['vehicle' => $vehicle],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_VEHICLE, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_VEHICLE,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function find(FindRequest $request, SearchVehicleAction $searchVehicleAction): JsonResponse
    {
        try {
            $vehicles = $searchVehicleAction(
                searchDto: $request->toDto(),
            );

            return $this->setResponse(
                message: Messages::VEHICLES_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['vehicles' => $vehicles],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_VEHICLES, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_VEHICLES,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(VehicleRequest $request, string $id, UpdateVehicleAction $updateVehicleAction): JsonResponse
    {
        try {
            $vehicleId =  Ulid::fromString($id);
            $updateVehicleAction(
                vehicleDto: $request->toDto(),
                vehicleId: $vehicleId,
            );

            return $this->setResponse(
                message: Messages::VEHICLE_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_VEHICLE, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_VEHICLE,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(string $id, DeleteVehicleAction $deleteVehicleAction): JsonResponse
    {
        try {
             $vehicleId =  Ulid::fromString($id);
             $deleteVehicleAction($vehicleId);

             return $this->setResponse(
                 message: Messages::VEHICLE_DELETED_SUCCESS,
                 code: Response::HTTP_OK,
             );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DELETING_VEHICLE, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DELETING_VEHICLE,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(string $id, FindOneAction $findOneAction): JsonResponse
    {
        try {
            $vehicleId = Ulid::fromString($id);
            $vehicle = $findOneAction($vehicleId);

            return $this->setResponse(
                message: Messages::VEHICLES_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['vehicle' => $vehicle],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_VEHICLES, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_VEHICLES,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
