<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use Throwable;

class FindOneServiceOrderAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(string $id): ServiceOrder
    {
        $serviceOrder = ServiceOrder::query()
            ->with(['client', 'vehicle', 'creator', 'technician', 'items', 'payments', 'events.user'])
            ->find($id);

        throw_if(
            is_null($serviceOrder),
            ServiceOrderNotFoundException::class
        );

        return $serviceOrder;
    }
}
