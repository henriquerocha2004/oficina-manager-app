<?php

namespace App\Actions\Tenant\Supplier;

use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Models\Tenant\Supplier;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class DeleteSupplierAction
{
    public function __invoke(Ulid $id): void
    {
        $idStr = ulid_db($id);
        $supplier = Supplier::query()->find($idStr);

        if ($supplier === null) {
            throw new SupplierNotFoundException;
        }

        $supplier->delete();
    }
}
