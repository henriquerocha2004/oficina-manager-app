<?php

namespace App\Actions\Tenant\Product;

use App\Dto\ProductDto;
use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Tenant\Product;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class UpdateProductAction
{
    public function __invoke(ProductDto $productDto, Ulid $productId): void
    {
        $idStr = ulid_db($productId);
        $product = Product::query()->find($idStr);

        if (is_null($product)) {
            throw new ProductNotFoundException;
        }

        $product->update($productDto->toArray());
    }
}
