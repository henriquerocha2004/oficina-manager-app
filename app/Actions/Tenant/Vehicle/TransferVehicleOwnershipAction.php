<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Vehicle;

use App\Exceptions\Vehicle\VehicleNotFoundException;
use App\Models\Tenant\ClientVehicle;
use App\Models\Tenant\Vehicle;
use Illuminate\Support\Facades\DB;

class TransferVehicleOwnershipAction
{
    /**
     * Transfere a propriedade de um veículo para um novo cliente.
     *
     * @throws VehicleNotFoundException
     */
    public function __invoke(string $vehicleId, string $newClientId): ClientVehicle
    {
        return DB::connection(config('database.connections_names.tenant'))
            ->transaction(function () use ($vehicleId, $newClientId): ClientVehicle {
                $vehicle = Vehicle::query()->find($vehicleId);

                throw_if(
                    is_null($vehicle),
                    VehicleNotFoundException::class
                );

                ClientVehicle::query()
                ->where('vehicle_id', $vehicleId)
                ->where('current_owner', true)
                ->update(['current_owner' => false]);

                $existingRelation = ClientVehicle::query()
                ->where('vehicle_id', $vehicleId)
                ->where('client_id', $newClientId)
                ->first();

                if (! is_null($existingRelation)) {
                    $existingRelation->update(['current_owner' => true]);

                    return $existingRelation->fresh();
                }

                return ClientVehicle::query()->create([
                'vehicle_id' => $vehicleId,
                'client_id' => $newClientId,
                'current_owner' => true,
                ]);
            });
    }
}
