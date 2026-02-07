<?php

namespace App\Actions\Tenant\Vehicle;

use App\Exceptions\Vehicle\VehicleNotFoundException;
use App\Models\Tenant\Vehicle;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class FindOneAction
{
    public function __invoke(Ulid $id): Vehicle
    {
        $idStr = ulid_db($id);
        $vehicle = Vehicle::query()->find($idStr);
        if ($vehicle === null) {
            throw new VehicleNotFoundException();
        }

        return $vehicle;
    }
}
