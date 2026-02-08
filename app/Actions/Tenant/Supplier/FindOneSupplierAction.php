<?php

namespace App\Actions\Tenant\Supplier;

use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Models\Tenant\Supplier;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class FindOneSupplierAction
{
    public function __invoke(Ulid $id): Supplier
    {
        $idStr = ulid_db($id);
        $supplier = Supplier::query()->find($idStr);

        if ($supplier === null) {
            throw new SupplierNotFoundException;
        }

        return $supplier;
    }
}
