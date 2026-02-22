<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use Throwable;

class DeleteServiceOrderAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(string $id): void
    {
        $serviceOrder = ServiceOrder::query()->find($id);

        throw_if(
            is_null($serviceOrder),
            ServiceOrderNotFoundException::class
        );

        $serviceOrder->delete();
    }
}
