<?php

namespace App\Actions\Tenant\Product;

use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Tenant\Product;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class FindOneProductAction
{
    public function __invoke(Ulid $id): Product
    {
        $idStr = ulid_db($id);
        $product = Product::query()->find($idStr);

        if ($product === null) {
            throw new ProductNotFoundException;
        }

        return $product;
    }
}
