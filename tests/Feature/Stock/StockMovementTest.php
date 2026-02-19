<?php

namespace Tests\Feature\Stock;

use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\User;
use Illuminate\Auth\Middleware\Authenticate;
use InvalidArgumentException;
use Tests\TestCase;

class StockMovementTest extends TestCase
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

    public function testMoveStockInSuccessfully(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $data = [
            'movement_type' => 'IN',
            'quantity' => 50,
            'reason' => 'purchase',
            'notes' => 'Purchase order #123',
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Stock movement recorded successfully.',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 50,
            'balance_after' => 50,
            'reason' => 'purchase',
            'notes' => 'Purchase order #123',
        ]);
    }

    public function testMoveStockOutSuccessfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 100,
            'balance_after' => 100,
            'reason' => 'initial',
            'user_id' => $user->id,
        ]);

        $data = [
            'movement_type' => 'OUT',
            'quantity' => 30,
            'reason' => 'sale',
            'notes' => 'Sale to customer',
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'quantity' => 30,
            'balance_after' => 70,
            'reason' => 'sale',
        ]);
    }

    public function testReturnsErrorWhenInsufficientStock(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'initial',
            'user_id' => $user->id,
        ]);

        $data = [
            'movement_type' => 'OUT',
            'quantity' => 50,
            'reason' => 'sale',
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Insufficient stock for this operation.',
        ]);
    }

    public function testReturnsErrorWhenProductNotFound(): void
    {
        $data = [
            'movement_type' => 'IN',
            'quantity' => 10,
            'reason' => 'purchase',
        ];

        $response = $this->tenantRequest('POST', '/stock/move/01JCTEST0000000000000000', $data);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Product not found.',
        ]);
    }

    public function testValidatesRequiredFields(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $data = [];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['movement_type', 'quantity', 'reason']);
    }

    public function testValidatesMovementTypeEnum(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $data = [
            'movement_type' => 'INVALID',
            'quantity' => 10,
            'reason' => 'purchase',
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['movement_type']);
    }

    public function testValidatesQuantityMinimum(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $data = [
            'movement_type' => 'IN',
            'quantity' => 0,
            'reason' => 'purchase',
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['quantity']);
    }

    public function testValidatesReasonEnum(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $data = [
            'movement_type' => 'IN',
            'quantity' => 10,
            'reason' => 'invalid_reason',
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    public function testValidatesNotesMaxLength(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $data = [
            'movement_type' => 'IN',
            'quantity' => 10,
            'reason' => 'purchase',
            'notes' => str_repeat('a', 501),
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['notes']);
    }

    public function testAllowsNullNotes(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $data = [
            'movement_type' => 'IN',
            'quantity' => 10,
            'reason' => 'purchase',
        ];

        $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'notes' => null,
        ]);
    }

    public function testFindOneProductIncludesStockMovements(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 7; $i++) {
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'IN',
                'quantity' => $i * 10,
                'balance_after' => $i * 10,
                'reason' => 'purchase',
                'user_id' => $user->id,
            ]);
        }

        $response = $this->tenantRequest('GET', "/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'product' => [
                    'id',
                    'name',
                    'stock_movements',
                ],
            ],
        ]);

        $response->assertJsonCount(5, 'data.product.stock_movements');
    }

    public function testAllReasonTypesAreAccepted(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $reasons = ['purchase', 'sale', 'adjustment', 'loss', 'return', 'transfer', 'initial', 'other'];

        foreach ($reasons as $reason) {
            $data = [
                'movement_type' => 'IN',
                'quantity' => 10,
                'reason' => $reason,
            ];

            $response = $this->tenantRequest('POST', "/stock/move/{$product->id}", $data);

            $response->assertStatus(201);
            $this->assertDatabaseHas('stock_movements', [
                'product_id' => $product->id,
                'reason' => $reason,
            ]);
        }
    }
}
