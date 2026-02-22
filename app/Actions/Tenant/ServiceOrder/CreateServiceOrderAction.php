<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Domain\Tenant\ServiceOrder\ServiceOrderDomain;
use App\Dto\ServiceOrderDto;
use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Models\Tenant\ServiceOrder;
use Illuminate\Support\Carbon;
use Throwable;

readonly class CreateServiceOrderAction
{
    public function __construct(
        private ServiceOrderDomain $domain
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(ServiceOrderDto $dto): ServiceOrder
    {
        $orderNumber = $this->domain->generateOrderNumber(Carbon::now()->year);

        return ServiceOrder::query()->create([
            'order_number' => $orderNumber,
            'client_id' => $dto->client_id,
            'vehicle_id' => $dto->vehicle_id,
            'created_by' => $dto->created_by,
            'technician_id' => $dto->technician_id,
            'status' => ServiceOrderStatusEnum::DRAFT,
            'diagnosis' => $dto->diagnosis,
            'observations' => $dto->observations,
            'discount' => $dto->discount,
            'total_parts' => 0,
            'total_services' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'outstanding_balance' => 0,
        ]);
    }
}
