<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Exceptions\ServiceOrder\ServiceOrderItemNotFoundException;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderItem;
use Throwable;

readonly class RemoveItemAction
{
    public function __construct(
        private ServiceOrderDomain $domain
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(string $serviceOrderId, string $itemId, int $userId): ServiceOrder
    {
        $serviceOrder = ServiceOrder::query()->find($serviceOrderId);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        $item = ServiceOrderItem::query()->find($itemId);

        throw_if(is_null($item), ServiceOrderItemNotFoundException::class);

        throw_if(
            $item->service_order_id !== $serviceOrder->id,
            ServiceOrderItemNotFoundException::class
        );

        $serviceOrder = $this->domain->removeItem($serviceOrder, $userId, $item);
        $serviceOrder->load(['client', 'vehicle', 'items']);

        return $serviceOrder;
    }
}
