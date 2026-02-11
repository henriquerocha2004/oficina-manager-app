<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Product\UpdateProductAction;
use App\Dto\ProductDto;
use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

class UpdateProductActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testUpdatesProductWhenExists(): void
    {
        $product = Product::create([
            'name' => 'Old Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $updatedData = [
            'name' => 'Updated Product',
            'category' => 'brakes',
            'unit' => 'box',
            'unit_price' => 200.00,
        ];

        $productDto = ProductDto::fromArray($updatedData);
        $action = new UpdateProductAction;

        $action($productDto, Ulid::fromString($product->id));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'category' => 'brakes',
            'unit_price' => 200.00,
        ]);
    }

    public function testThrowsWhenProductNotFound(): void
    {
        $productDto = ProductDto::fromArray([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $this->expectException(ProductNotFoundException::class);

        $fakeUlid = Ulid::fromString((string) Ulid::generate());
        (new UpdateProductAction)($productDto, $fakeUlid);
    }
}
