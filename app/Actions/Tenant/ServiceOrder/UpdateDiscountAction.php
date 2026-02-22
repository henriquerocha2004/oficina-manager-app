<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use Throwable;

readonly class UpdateDiscountAction
{
    public function __construct(
        private ServiceOrderDomain $domain
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(string $serviceOrderId, int $userId, float $discount): ServiceOrder
    {
        $serviceOrder = ServiceOrder::query()->find($serviceOrderId);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        $serviceOrder = $this->domain->updateDiscount($serviceOrder, $userId, $discount);
        $serviceOrder->load(['client', 'vehicle', 'items']);

        return $serviceOrder;
    }
}
