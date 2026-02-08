<?php

namespace App\Actions\Tenant\Supplier;

use App\Dto\SupplierDto;
use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Models\Tenant\Supplier;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class UpdateSupplierAction
{
    public function __invoke(SupplierDto $supplierDto, Ulid $supplierId): void
    {
        $idStr = ulid_db($supplierId);
        $supplier = Supplier::query()->find($idStr);

        if (is_null($supplier)) {
            throw new SupplierNotFoundException;
        }

        $supplier->update($supplierDto->toArray());
    }
}
