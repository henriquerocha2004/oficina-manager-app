<?php

namespace Tests\Feature\tenant;

use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\User;
use Illuminate\Auth\Middleware\Authenticate;
use Tests\TestCase;

class StockMovementControllerTest extends TestCase
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
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method")
        };
    }

    public function test_index_returns_inertia_view(): void
    {
        $response = $this->withoutMiddleware([Authenticate::class])
            ->get('http://test-tenant.localhost/stock/movements');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Tenant/StockMovements/Index'));
    }

    public function test_find_returns_movements_list(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        $response = $this->tenantRequest('GET', '/stock/movements/search');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'movements' => [
                    'data',
                    'current_page',
                    'total',
                ],
            ],
        ]);
    }

    public function test_find_with_product_filter(): void
    {
        $product1 = Product::create([
            'name' => 'Product 1',
            'sku' => 'PROD-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'name' => 'Product 2',
            'sku' => 'PROD-002',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 50.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product1->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        StockMovement::create([
            'product_id' => $product2->id,
            'movement_type' => 'IN',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        $response = $this->tenantRequest('GET', '/stock/movements/search?filters[product_id]='.$product1->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.movements.data'));
    }

    public function test_find_with_type_filter(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'sale',
            'user_id' => $user->id,
        ]);

        $response = $this->tenantRequest('GET', '/stock/movements/search?filters[movement_type]=IN');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.movements.data')));
    }

    public function test_find_with_reason_filter(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 5,
            'balance_after' => 15,
            'reason' => 'adjustment',
            'user_id' => $user->id,
        ]);

        $response = $this->tenantRequest('GET', '/stock/movements/search?filters[reason]=purchase');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.movements.data')));
    }

    public function test_find_with_date_range_filter(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        $dateFrom = now()->subDays(1)->format('Y-m-d');
        $dateTo = now()->addDays(1)->format('Y-m-d');

        $response = $this->tenantRequest('GET', "/stock/movements/search?filters[date_from]={$dateFrom}&filters[date_to]={$dateTo}");

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.movements.data')));
    }

    public function test_find_with_search_parameter(): void
    {
        $product = Product::create([
            'name' => 'Unique Product Name',
            'sku' => 'UNIQUE-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        $response = $this->tenantRequest('GET', '/stock/movements/search?search=Unique');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.movements.data')));
    }

    public function test_find_with_multiple_filters(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'sale',
            'user_id' => $user->id,
        ]);

        $response = $this->tenantRequest('GET', '/stock/movements/search?filters[movement_type]=IN&filters[reason]=purchase');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.movements.data')));
    }

    public function test_stats_returns_statistics(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'sale',
            'user_id' => $user->id,
        ]);

        $response = $this->tenantRequest('GET', '/stock/movements/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'total_movements' => ['current', 'previous', 'growth'],
                'total_in' => ['current', 'previous', 'growth'],
                'total_out' => ['current', 'previous', 'growth'],
                'most_common_reason',
            ],
        ]);
    }
}
