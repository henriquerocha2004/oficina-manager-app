<?php

namespace App\Actions\Tenant\Product;

use App\Dto\ProductDto;
use App\Exceptions\Product\ProductAlreadyExistsException;
use App\Models\Tenant\Product;
use Throwable;

class CreateProductAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(ProductDto $productDto): Product
    {
        if (!is_null($productDto->sku)) {
            $product = Product::query()
                ->whereSku($productDto->sku)
                ->first();

            throw_if(!is_null($product), ProductAlreadyExistsException::class);
        }

        return Product::query()->create($productDto->toArray());
    }
}
