<?php

namespace App\Actions\Tenant\Vehicle;

use App\Dto\VehicleDto;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\Vehicle;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateVehicleAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(VehicleDto $vehicleDto, string $clientId): Vehicle
    {
        return DB::connection(config('database.connections_names.tenant'))->transaction(function () use ($vehicleDto, $clientId): Vehicle {
            $existingVehicle = Vehicle::query()
                ->whereLicensePlate($vehicleDto->license_plate)
                ->first();

            if (! is_null($existingVehicle)) {
                ClientVehicle::query()
                    ->where('vehicle_id', $existingVehicle->id)
                    ->update(['current_owner' => false]);
                ClientVehicle::query()->updateOrCreate(
                    [
                        'vehicle_id' => $existingVehicle->id,
                        'client_id' => $clientId,
                    ],
                    ['current_owner' => true]
                );

                return $existingVehicle->fresh();
            }

            $vehicle = Vehicle::query()->create($vehicleDto->toArray());

            ClientVehicle::query()->create([
                'vehicle_id' => $vehicle->id,
                'client_id' => $clientId,
                'current_owner' => true,
            ]);

            return $vehicle->fresh();
        });
    }
}
