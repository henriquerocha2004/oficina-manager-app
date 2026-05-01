<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Models\Tenant\ServiceOrder;
use Throwable;

readonly class CreateServiceOrderAction
{
    public function __construct(
        private ServiceOrderDomain $domain
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(ServiceOrderDto $dto): ServiceOrder
    {
        return $this->domain->create(
            clientId: $dto->client_id,
            vehicleId: $dto->vehicle_id,
            createdBy: $dto->created_by,
            diagnosis: $dto->diagnosis,
            observations: $dto->observations,
            technicianId: $dto->technician_id,
            discount: $dto->discount,
        );
    }
}
