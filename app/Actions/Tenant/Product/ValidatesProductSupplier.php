<?php

namespace App\Actions\Tenant\Product;

use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Models\Tenant\Product;
use App\Models\Tenant\Supplier;
use Symfony\Component\Uid\Ulid;

use Throwable;
use function ulid_db;

trait ValidatesProductSupplier
{
    /**
     * Validates that product and supplier exist, and returns them with ULID strings.
     *
     * @return array{product: Product, supplier: Supplier, productIdStr: string, supplierIdStr: string}
     *
     * @throws ProductNotFoundException
     * @throws SupplierNotFoundException
     * @throws Throwable
     */
    private function validateProductAndSupplier(Ulid $productId, string $supplierId): array
    {
        $productIdStr = ulid_db($productId);
        $supplierIdStr = ulid_db(Ulid::fromString($supplierId));

        $product = Product::query()->find($productIdStr);
        throw_if(is_null($product), ProductNotFoundException::class);

        $supplier = Supplier::query()->find($supplierIdStr);
        throw_if(is_null($supplier), SupplierNotFoundException::class);

        return [
            'product' => $product,
            'supplier' => $supplier,
            'productIdStr' => $productIdStr,
            'supplierIdStr' => $supplierIdStr,
        ];
    }

    /**
     * Checks if a supplier is already attached to a product.
     */
    private function supplierIsAttached(Product $product, string $supplierIdStr): bool
    {
        return $product->suppliers()
            ->where('supplier_id', $supplierIdStr)
            ->exists();
    }
}
