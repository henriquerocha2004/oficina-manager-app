<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Enum\Tenant\ServiceOrder\ServiceOrderItemTypeEnum;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderItem;
use Throwable;

readonly class SendForApprovalAction
{
    public function __construct(
        private ServiceOrderDomain $domain
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        string $serviceOrderId,
        int $userId,
        ?string $diagnosis = null,
        ?array $items = null
    ): ServiceOrder {
        $serviceOrder = ServiceOrder::query()->find($serviceOrderId);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        if ($diagnosis !== null) {
            $this->domain->updateDiagnosis($serviceOrder, $userId, $diagnosis);
        }

        if (!empty($items)) {
            foreach ($items as $itemData) {
                $item = new ServiceOrderItem([
                    'type' => ServiceOrderItemTypeEnum::from($itemData['type']),
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                    'service_id' => $itemData['service_id'] ?? null,
                    'product_id' => $itemData['product_id'] ?? null,
                ]);

                $this->domain->addItem($serviceOrder, $userId, $item);
            }
        }

        $serviceOrder = $this->domain->sendForApproval($serviceOrder, $userId);
        $serviceOrder->load(['client', 'vehicle', 'items']);

        return $serviceOrder;
    }
}
