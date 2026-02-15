<?php

namespace App\Actions\Tenant\Product;

use App\Exceptions\Product\ProductSupplierNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\Ulid;
use Throwable;

class DetachSupplierFromProductAction
{
    use ValidatesProductSupplier;

    /**
     * @throws Throwable
     */
    public function __invoke(Ulid $productId, Ulid $supplierId): void
    {
        DB::transaction(function () use ($productId, $supplierId) {
            [
                'product' => $product,
                'supplierIdStr' => $supplierIdStr,
            ] = $this->validateProductAndSupplier($productId, $supplierId->toRfc4122());

            throw_if(
                ! $this->supplierIsAttached($product, $supplierIdStr),
                ProductSupplierNotFoundException::class
            );

            $product->suppliers()->detach($supplierIdStr);
        });
    }
}
