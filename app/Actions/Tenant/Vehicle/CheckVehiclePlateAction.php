<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Vehicle;

use App\Models\Tenant\Vehicle;

class CheckVehiclePlateAction
{
    /**
     * Verifica se uma placa já existe e retorna informações do proprietário atual.
     */
    public function __invoke(string $licensePlate): array
    {
        $vehicle = Vehicle::query()
            ->with('currentOwner.client')
            ->whereLicensePlate($licensePlate)
            ->first();

        if (is_null($vehicle)) {
            return [
                'exists' => false,
                'vehicle_id' => null,
                'current_owner_id' => null,
                'current_owner_name' => null,
            ];
        }

        $currentOwnerRelation = $vehicle->currentOwner;

        if (is_null($currentOwnerRelation)) {
            return [
                'exists' => true,
                'vehicle_id' => $vehicle->id,
                'current_owner_id' => null,
                'current_owner_name' => null,
            ];
        }

        return [
            'exists' => true,
            'vehicle_id' => $vehicle->id,
            'current_owner_id' => $currentOwnerRelation->client->id,
            'current_owner_name' => $currentOwnerRelation->client->name,
        ];
    }
}
