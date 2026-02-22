<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use Throwable;

readonly class StartWorkAction
{
    public function __construct(
        private ServiceOrderDomain $domain
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(string $serviceOrderId, int $userId, ?int $technicianId = null): ServiceOrder
    {
        $serviceOrder = ServiceOrder::query()->find($serviceOrderId);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        $serviceOrder = $this->domain->startWork($serviceOrder, $userId, $technicianId);
        $serviceOrder->load(['client', 'vehicle', 'items', 'technician']);

        return $serviceOrder;
    }
}
