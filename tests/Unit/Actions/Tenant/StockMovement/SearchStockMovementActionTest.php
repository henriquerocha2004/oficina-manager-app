<?php

namespace Tests\Unit\Actions\Tenant\StockMovement;

use App\Actions\Tenant\StockMovement\SearchStockMovementAction;
use App\Dto\SearchDto;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\User;
use Tests\TestCase;

class SearchStockMovementActionTest extends TestCase
{
    private Product $product;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $this->user = User::factory()->create();
    }

    public function test_returns_all_movements_when_no_filters(): void
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'sale',
            'user_id' => $this->user->id,
        ]);

        $searchDto = new SearchDto(
            search: null,
            per_page: 15,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: []
        );

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertCount(2, $result->items());
    }

    public function test_filters_movements_by_product(): void
    {
        $product2 = Product::create([
            'name' => 'Another Product',
            'sku' => 'TEST-002',
            'category' => Product::CATEGORY_ENGINE,
            'unit' => Product::UNIT_UNIT,
            'unit_price' => 50.00,
            'is_active' => true,
        ]);

        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $product2->id,
            'movement_type' => 'IN',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        $searchDto = new SearchDto(
            filters: ['product_id' => $this->product->id]
        );

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertCount(1, $result->items());
        $this->assertEquals($this->product->id, $result->items()[0]->product_id);
    }

    public function test_filters_movements_by_type(): void
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'sale',
            'user_id' => $this->user->id,
        ]);

        $searchDto = new SearchDto(
            filters: ['movement_type' => 'IN']
        );

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertCount(1, $result->items());
        $this->assertEquals('IN', $result->items()[0]->movement_type);
    }

    public function test_filters_movements_by_reason(): void
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 5,
            'balance_after' => 15,
            'reason' => 'adjustment',
            'user_id' => $this->user->id,
        ]);

        $searchDto = new SearchDto(
            filters: ['reason' => 'purchase']
        );

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertCount(1, $result->items());
        $this->assertEquals('purchase', $result->items()[0]->reason);
    }

    public function test_filters_movements_by_date_range(): void
    {
        $oldMovement = StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);
        $oldMovement->created_at = now()->subDays(10);
        $oldMovement->save();

        $recentMovement = StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'sale',
            'user_id' => $this->user->id,
        ]);

        $searchDto = new SearchDto(
            filters: [
                'date_from' => now()->subDays(5)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]
        );

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertCount(1, $result->items());
        $this->assertEquals($recentMovement->id, $result->items()[0]->id);
    }

    public function test_searches_product_name(): void
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        $searchDto = new SearchDto(
            search: 'Test Product'
        );

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertCount(1, $result->items());
    }

    public function test_orders_by_created_at_desc(): void
    {
        $firstMovement = StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        sleep(1);

        $secondMovement = StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'sale',
            'user_id' => $this->user->id,
        ]);

        $searchDto = new SearchDto(
            sort_by: 'created_at',
            sort_direction: 'desc'
        );

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertEquals($secondMovement->id, $result->items()[0]->id);
        $this->assertEquals($firstMovement->id, $result->items()[1]->id);
    }

    public function test_includes_deleted_products(): void
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $this->user->id,
        ]);

        $this->product->delete();

        $searchDto = new SearchDto;

        $action = new SearchStockMovementAction;
        $result = $action($searchDto);

        $this->assertCount(1, $result->items());
        $this->assertNotNull($result->items()[0]->product);
    }
}
