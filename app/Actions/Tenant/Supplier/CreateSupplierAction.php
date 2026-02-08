<?php

namespace App\Actions\Tenant\Supplier;

use App\Dto\SupplierDto;
use App\Exceptions\Supplier\SupplierAlreadyExistsException;
use App\Models\Tenant\Supplier;
use Throwable;

class CreateSupplierAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(SupplierDto $supplierDto): Supplier
    {
        $supplier = Supplier::query()
            ->whereDocumentNumber($supplierDto->document_number)
            ->first();

        throw_if(!is_null($supplier), SupplierAlreadyExistsException::class);

        return Supplier::query()->create($supplierDto->toArray());
    }
}
