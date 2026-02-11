<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Product\DeleteProductAction;
use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

class DeleteProductActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testDeletesProductWhenFound(): void
    {
        $product = Product::create([
            'name' => 'Product to Delete',
            'category' => 'filters',
            'unit' => 'unit',
            'unit_price' => 50.00,
        ]);

        $action = new DeleteProductAction;
        $action(Ulid::fromString($product->id));

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function testThrowsWhenProductNotFound(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $fakeUlid = Ulid::fromString(Ulid::generate());
        (new DeleteProductAction)($fakeUlid);
    }
}
