<?php

namespace App\Actions\Tenant\Service;

use App\Dto\ServiceDto;
use App\Exceptions\Service\ServiceAlreadyExistsException;
use App\Models\Tenant\Service;
use Throwable;

class CreateServiceAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(ServiceDto $serviceDto): Service
    {
        $service = Service::query()
            ->where('name', $serviceDto->name)
            ->first();

        throw_if(!is_null($service), ServiceAlreadyExistsException::class);

        return Service::query()->create($serviceDto->toArray());
    }
}
