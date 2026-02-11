<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Product\CreateProductAction;
use App\Dto\ProductDto;
use App\Exceptions\Product\ProductAlreadyExistsException;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Throwable;

class CreateProductActionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     */
    public function testCreatesProductWhenNotExists(): void
    {
        $data = [
            'name' => 'Brake Pad',
            'category' => 'brakes',
            'unit' => 'unit',
            'unit_price' => 150.00,
            'sku' => 'BRK-001',
        ];

        $productDto = ProductDto::fromArray($data);

        $action = new CreateProductAction;
        $result = $action($productDto);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertDatabaseHas('products', ['sku' => $data['sku']]);
    }

    /**
     * @throws Throwable
     */
    public function testThrowsWhenProductWithSkuAlreadyExists(): void
    {
        $data = [
            'name' => 'Brake Pad',
            'category' => 'brakes',
            'unit' => 'unit',
            'unit_price' => 150.00,
            'sku' => 'BRK-001',
        ];

        Product::create($data);

        $productDto = ProductDto::fromArray($data);

        $this->expectException(ProductAlreadyExistsException::class);

        (new CreateProductAction)($productDto);
    }

    /**
     * @throws Throwable
     */
    public function testCreatesProductWithoutSkuSuccessfully(): void
    {
        $data = [
            'name' => 'Oil Filter',
            'category' => 'filters',
            'unit' => 'unit',
            'unit_price' => 25.50,
        ];

        $productDto = ProductDto::fromArray($data);

        $action = new CreateProductAction;
        $result = $action($productDto);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertDatabaseHas('products', ['name' => $data['name']]);
    }
}
