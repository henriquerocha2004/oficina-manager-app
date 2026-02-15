<?php

namespace App\Actions\Tenant\Product;

use App\Dto\ProductSupplierDto;
use App\Exceptions\Product\ProductSupplierNotFoundException;
use App\Models\Tenant\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\Ulid;
use Throwable;

class UpdateProductSupplierAction
{
    use ManagesPreferredSuppliers;
    use ValidatesProductSupplier;

    /**
     * @throws Throwable
     */
    public function __invoke(ProductSupplierDto $productSupplierDto, Ulid $productId): Product
    {
        return DB::transaction(function () use ($productSupplierDto, $productId) {
            [
                'product' => $product,
                'supplierIdStr' => $supplierIdStr,
            ] = $this->validateProductAndSupplier($productId, $productSupplierDto->supplier_id);

            throw_if(
                ! $this->supplierIsAttached($product, $supplierIdStr),
                ProductSupplierNotFoundException::class
            );

            if ($productSupplierDto->is_preferred) {
                $this->unmarkOtherPreferredSuppliers($product, $supplierIdStr);
            }

            $updateData = array_filter([
                'supplier_sku' => $productSupplierDto->supplier_sku,
                'cost_price' => $productSupplierDto->cost_price,
                'lead_time_days' => $productSupplierDto->lead_time_days,
                'min_order_quantity' => $productSupplierDto->min_order_quantity,
                'is_preferred' => $productSupplierDto->is_preferred,
                'notes' => $productSupplierDto->notes,
            ], fn ($value) => ! is_null($value));

            $product->suppliers()->updateExistingPivot($supplierIdStr, $updateData);

            return $product->load(['suppliers' => function ($query) {
                $query->where('is_active', true);
            }]);
        });
    }
}
