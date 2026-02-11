<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Product\FindOneProductAction;
use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

class FindOneProductActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testFindsProductById(): void
    {
        $product = Product::create([
            'name' => 'Specific Product',
            'category' => 'tools',
            'unit' => 'unit',
            'unit_price' => 99.99,
            'sku' => 'TOOL-123',
        ]);

        $action = new FindOneProductAction;
        $result = $action(Ulid::fromString($product->id));

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($product->id, $result->id);
        $this->assertEquals('Specific Product', $result->name);
        $this->assertEquals('TOOL-123', $result->sku);
    }

    public function testThrowsWhenProductNotFound(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $fakeUlid = Ulid::fromString(Ulid::generate());
        (new FindOneProductAction)($fakeUlid);
    }
}
