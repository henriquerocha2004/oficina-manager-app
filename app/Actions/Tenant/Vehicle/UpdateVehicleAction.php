<?php

namespace App\Actions\Tenant\Vehicle;

use App\Dto\VehicleDto;
use App\Exceptions\Vehicle\VehicleNotFoundException;
use App\Models\Tenant\Vehicle;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class UpdateVehicleAction
{
    public function __invoke(VehicleDto $vehicleDto, Ulid $vehicleId): void
    {
        $idStr = ulid_db($vehicleId);
        $vehicle = Vehicle::query()->find($idStr);

        if (is_null($vehicle)) {
            throw new VehicleNotFoundException();
        }

        $vehicle->update([
            'license_plate' => $vehicleDto->license_plate,
            'brand' => $vehicleDto->brand,
            'model' => $vehicleDto->model,
            'year' => $vehicleDto->year,
            'color' => $vehicleDto->color,
            'vin' => $vehicleDto->vin,
            'fuel' => $vehicleDto->fuel,
            'transmission' => $vehicleDto->transmission,
            'mileage' => $vehicleDto->mileage,
            'cilinder_capacity' => $vehicleDto->cilinder_capacity,
            'client_id' => $vehicleDto->client_id,
            'vehicle_type' => $vehicleDto->vehicle_type,
            'observations' => $vehicleDto->observations,
        ]);
    }
}
