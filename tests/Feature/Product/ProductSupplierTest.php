<?php

namespace Tests\Feature\Product;

use App\Models\Tenant\Product;
use App\Models\Tenant\Supplier;
use Illuminate\Auth\Middleware\Authenticate;
use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

class ProductSupplierTest extends TestCase
{
    protected function tenantRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        return match (strtoupper($method)) {
            'GET' => $this->withoutMiddleware([Authenticate::class])->getJson(
                'http://test-tenant.localhost'.$uri
            ),
            'POST' => $this->withoutMiddleware([Authenticate::class])->postJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            'PUT' => $this->withoutMiddleware([Authenticate::class])->putJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            'DELETE' => $this->withoutMiddleware([Authenticate::class])->deleteJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            default => throw new InvalidArgumentException("Unsupported HTTP method: $method")
        };
    }

    public function testAttachSupplierToProductSuccessfully(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $data = [
            'supplier_id' => $supplier->id,
            'cost_price' => 80.00,
            'supplier_sku' => 'SUP-SKU-123',
            'lead_time_days' => 5,
            'min_order_quantity' => 10,
            'is_preferred' => true,
            'notes' => 'Test notes',
        ];

        $response = $this->tenantRequest('POST', "/products/{$product->id}/suppliers", $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'product' => [
                    'id',
                    'name',
                    'suppliers',
                ],
            ],
        ]);

        $this->assertDatabaseHas('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'cost_price' => 80.00,
            'is_preferred' => true,
        ]);
    }

    public function testUpdateProductSupplierSuccessfully(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $product->suppliers()->attach($supplier->id, [
            'id' => Ulid::generate(),
            'cost_price' => 80.00,
            'supplier_sku' => 'OLD-SKU',
            'is_preferred' => false,
        ]);

        $data = [
            'cost_price' => 90.00,
            'supplier_sku' => 'NEW-SKU',
            'is_preferred' => true,
        ];

        $response = $this->tenantRequest('PUT', "/products/{$product->id}/suppliers/{$supplier->id}", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'cost_price' => 90.00,
            'supplier_sku' => 'NEW-SKU',
            'is_preferred' => true,
        ]);
    }

    public function testDetachSupplierFromProductSuccessfully(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'document_number' => '12345678000195',
            'is_active' => true,
        ]);

        $product->suppliers()->attach($supplier->id, [
            'id' => Ulid::generate(),
            'cost_price' => 80.00,
        ]);

        $response = $this->tenantRequest('DELETE', "/products/{$product->id}/suppliers/{$supplier->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    public function testFindOneProductReturnsActiveSuppliers(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $activeSupplier = Supplier::create([
            'name' => 'Active Supplier',
            'document_number' => '11111111000111',
            'is_active' => true,
        ]);

        $inactiveSupplier = Supplier::create([
            'name' => 'Inactive Supplier',
            'document_number' => '22222222000122',
            'is_active' => false,
        ]);

        $product->suppliers()->attach($activeSupplier->id, [
            'id' => Ulid::generate(),
            'cost_price' => 80.00,
        ]);
        $product->suppliers()->attach($inactiveSupplier->id, [
            'id' => Ulid::generate(),
            'cost_price' => 70.00,
        ]);

        $response = $this->tenantRequest('GET', "/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.product.suppliers.0.name', 'Active Supplier');
        $response->assertJsonCount(1, 'data.product.suppliers');
    }
}
