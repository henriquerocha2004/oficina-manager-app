<?php

namespace App\Actions\Tenant\Service;

use App\Dto\ServiceDto;
use App\Exceptions\Service\ServiceNotFoundException;
use App\Models\Tenant\Service;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class UpdateServiceAction
{
    public function __invoke(ServiceDto $serviceDto, Ulid $serviceId): void
    {
        $idStr = ulid_db($serviceId);
        $service = Service::query()->find($idStr);

        if (is_null($service)) {
            throw new ServiceNotFoundException();
        }

        $service->update([
            'name' => $serviceDto->name,
            'description' => $serviceDto->description,
            'base_price' => $serviceDto->base_price,
            'category' => $serviceDto->category,
            'estimated_time' => $serviceDto->estimated_time,
            'is_active' => $serviceDto->is_active,
        ]);
    }
}
