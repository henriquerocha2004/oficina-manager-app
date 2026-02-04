<?php

namespace App\Actions\Tenant\Vehicle;

use App\Dto\VehicleDto;
use App\Exceptions\Vehicle\VehicleAlreadyExistsException;
use App\Models\Tenant\Vehicle;
use Throwable;

class CreateVehicleAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(VehicleDto $vehicleDto): Vehicle
    {
        $vehicle = Vehicle::query()
            ->whereLicensePlate($vehicleDto->license_plate)
            ->first();

        throw_if(!is_null($vehicle), VehicleAlreadyExistsException::class);

        return Vehicle::query()->create($vehicleDto->toArray());
    }
}
