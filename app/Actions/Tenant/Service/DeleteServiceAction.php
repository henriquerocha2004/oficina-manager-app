<?php

namespace App\Actions\Tenant\Service;

use App\Exceptions\Service\ServiceNotFoundException;
use App\Models\Tenant\Service;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class DeleteServiceAction
{
    public function __invoke(Ulid $id): void
    {
        $idStr = ulid_db($id);
        $service = Service::query()->find($idStr);

        if ($service === null) {
            throw new ServiceNotFoundException();
        }

        $service->delete();
    }
}
