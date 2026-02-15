<?php

namespace Tests\Unit\Actions\Product;

use App\Actions\Tenant\Product\AttachSupplierToProductAction;
use App\Actions\Tenant\Product\DetachSupplierFromProductAction;
use App\Actions\Tenant\Product\UpdateProductSupplierAction;
use App\Dto\ProductSupplierDto;
use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Product\ProductSupplierAlreadyExistsException;
use App\Exceptions\Product\ProductSupplierNotFoundException;
use App\Exceptions\Supplier\SupplierNotFoundException;
use App\Models\Tenant\Product;
use App\Models\Tenant\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;
use Throwable;

class ProductSupplierActionsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Throwable
     */
    public function testAttachSupplierToProductSuccessfully(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $dto = new ProductSupplierDto(
            supplier_id: $supplier->id,
            cost_price: 80.00,
            supplier_sku: 'SUP-SKU-001',
            lead_time_days: 5,
            min_order_quantity: 10,
            is_preferred: true,
            notes: 'Test notes',
        );

        $action = new AttachSupplierToProductAction;
        $result = $action($dto, Ulid::fromString($product->id));

        $this->assertInstanceOf(Product::class, $result);
        $this->assertDatabaseHas('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'cost_price' => 80.00,
            'is_preferred' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testAttachSupplierThrowsWhenProductNotFound(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $dto = new ProductSupplierDto(
            supplier_id: $supplier->id,
            cost_price: 80.00,
        );

        $this->expectException(ProductNotFoundException::class);

        $action = new AttachSupplierToProductAction;
        $action($dto, Ulid::fromString(Ulid::generate()));
    }

    /**
     * @throws Throwable
     */
    public function testAttachSupplierThrowsWhenSupplierNotFound(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $dto = new ProductSupplierDto(
            supplier_id: Ulid::generate(),
            cost_price: 80.00,
        );

        $this->expectException(SupplierNotFoundException::class);

        $action = new AttachSupplierToProductAction;
        $action($dto, Ulid::fromString($product->id));
    }

    /**
     * @throws Throwable
     */
    public function testAttachSupplierThrowsWhenAlreadyAttached(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $product->suppliers()->attach($supplier->id, [
            'id' => Ulid::generate(),
            'cost_price' => 70.00,
        ]);

        $dto = new ProductSupplierDto(
            supplier_id: $supplier->id,
            cost_price: 80.00,
        );

        $this->expectException(ProductSupplierAlreadyExistsException::class);

        $action = new AttachSupplierToProductAction;
        $action($dto, Ulid::fromString($product->id));
    }

    /**
     * @throws Throwable
     */
    public function testUpdateProductSupplierSuccessfully(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $product->suppliers()->attach($supplier->id, [
            'id' => Ulid::generate(),
            'cost_price' => 70.00,
            'is_preferred' => false,
        ]);

        $dto = new ProductSupplierDto(
            supplier_id: $supplier->id,
            cost_price: 90.00,
            is_preferred: true,
        );

        $action = new UpdateProductSupplierAction;
        $result = $action($dto, Ulid::fromString($product->id));

        $this->assertInstanceOf(Product::class, $result);
        $this->assertDatabaseHas('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'cost_price' => 90.00,
            'is_preferred' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testUpdateProductSupplierThrowsWhenNotAttached(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $dto = new ProductSupplierDto(
            supplier_id: $supplier->id,
            cost_price: 90.00,
        );

        $this->expectException(ProductSupplierNotFoundException::class);

        $action = new UpdateProductSupplierAction;
        $action($dto, Ulid::fromString($product->id));
    }

    /**
     * @throws Throwable
     */
    public function testDetachSupplierFromProductSuccessfully(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $product->suppliers()->attach($supplier->id, [
            'id' => Ulid::generate(),
            'cost_price' => 70.00,
        ]);

        $action = new DetachSupplierFromProductAction;
        $action(Ulid::fromString($product->id), Ulid::fromString($supplier->id));

        $this->assertDatabaseMissing('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testDetachSupplierThrowsWhenNotAttached(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $this->expectException(ProductSupplierNotFoundException::class);

        $action = new DetachSupplierFromProductAction;
        $action(Ulid::fromString($product->id), Ulid::fromString($supplier->id));
    }
}
