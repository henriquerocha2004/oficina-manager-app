<?php

namespace App\Actions\Tenant\Stock;

use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;

class GetCurrentStockAction
{
    public function __invoke(Product|string $product): int
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return (int) StockMovement::query()
            ->where('product_id', $productId)
            ->selectRaw("SUM(CASE WHEN movement_type = 'IN' THEN quantity ELSE -quantity END) as balance")
            ->value('balance') ?? 0;
    }
}
