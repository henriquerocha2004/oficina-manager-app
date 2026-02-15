<?php

namespace App\Actions\Tenant\Product;

use App\Models\Tenant\Product;

trait ManagesPreferredSuppliers
{
    /**
     * Unmarks all suppliers for a product as preferred except the specified supplier.
     */
    private function unmarkOtherPreferredSuppliers(Product $product, ?string $exceptSupplierId = null): void
    {
        $query = $product->suppliers();

        if ($exceptSupplierId !== null) {
            $query->where('supplier_id', '!=', $exceptSupplierId);
        }

        $supplierIds = $query->pluck('supplier_id')->toArray();

        foreach ($supplierIds as $id) {
            $product->suppliers()->updateExistingPivot($id, ['is_preferred' => false]);
        }
    }
}
