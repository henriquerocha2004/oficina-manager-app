<?php

namespace App\Services\Tenant\ServiceOrder;

use App\Actions\Tenant\Client\CreateClientAction;
use App\Actions\Tenant\Vehicle\CreateVehicleAction;
use App\Actions\Tenant\Vehicle\TransferVehicleOwnershipAction;
use App\Dto\ClientDto;
use App\Dto\ResolvedClientVehicle;
use App\Dto\VehicleDto;
use App\Exceptions\ServiceOrder\VehicleOwnershipConflictException;
use App\Models\Tenant\ClientVehicle;
use Exception;
use Throwable;

readonly class ClientVehicleResolverService
{
    public function __construct(
        private CreateClientAction $createClient,
        private CreateVehicleAction $createVehicle,
        private TransferVehicleOwnershipAction $transferOwnership,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function resolve(array $requestData): ResolvedClientVehicle
    {
        $clientId = $this->resolveClient($requestData);
        $vehicleId = $this->resolveVehicle($requestData, $clientId);

        return new ResolvedClientVehicle(
            clientId: $clientId,
            vehicleId: $vehicleId
        );
    }

    /**
     * @throws Throwable
     */
    private function resolveClient(array $data): string
    {
        if (isset($data['client_id'])) {
            return $data['client_id'];
        }

        if (isset($data['new_client'])) {
            $clientDto = ClientDto::fromArray($data['new_client']);
            $client = $this->createClient->__invoke($clientDto);

            return $client->id;
        }

        throw new Exception('Client data is required.');
    }

    /**
     * @throws Throwable
     */
    private function resolveVehicle(array $data, string $clientId): string
    {
        if (isset($data['vehicle_id'])) {
            $vehicleId = $data['vehicle_id'];

            $currentOwnership = ClientVehicle::query()
                ->where('vehicle_id', $vehicleId)
                ->where('current_owner', true)
                ->with('client')
                ->first();

            if ($currentOwnership === null) {
                return $vehicleId;
            }

            if ($currentOwnership->client_id === $clientId) {
                return $vehicleId;
            }

            $transferVehicle = $data['transfer_vehicle'] ?? false;

            if (!$transferVehicle) {
                throw new VehicleOwnershipConflictException(
                    vehicleId: $vehicleId,
                    currentOwnerId: $currentOwnership->client_id,
                    currentOwnerName: $currentOwnership->client->name,
                );
            }

            $this->transferOwnership->__invoke($vehicleId, $clientId);

            return $vehicleId;
        }

        if (isset($data['new_vehicle'])) {
            $vehicleDto = VehicleDto::fromArray($data['new_vehicle']);
            $vehicle = $this->createVehicle->__invoke($vehicleDto, $clientId);

            return $vehicle->id;
        }

        throw new Exception('Vehicle data is required.');
    }
}
