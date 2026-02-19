<?php

namespace Tests\Unit\Models;

use App\Actions\Tenant\Stock\GetCurrentStockAction;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\User;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    public function test_belongs_to_product(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Product::class, $movement->product);
        $this->assertEquals($product->id, $movement->product->id);
    }

    public function test_belongs_to_user(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $movement->user);
        $this->assertEquals($user->id, $movement->user->id);
    }

    public function test_allows_null_user(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'initial',
            'user_id' => null,
        ]);

        $this->assertNull($movement->user_id);
        $this->assertNull($movement->user);
    }

    public function test_casts_quantity_to_integer(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => '50',
            'balance_after' => '50',
            'reason' => 'purchase',
        ]);

        $this->assertIsInt($movement->quantity);
        $this->assertEquals(50, $movement->quantity);
    }

    public function test_stores_all_reason_types(): void
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
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'IN',
                'quantity' => 10,
                'balance_after' => 10,
                'reason' => $reason,
            ]);

            $this->assertEquals($reason, $movement->reason);
        }
    }

    public function test_product_has_stock_movements_relationship(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
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

        $this->assertCount(2, $product->stockMovements);
        $this->assertInstanceOf(StockMovement::class, $product->stockMovements->first());
    }

    public function test_get_current_stock_calculates_correctly(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $this->assertEquals(0, (new GetCurrentStockAction)($product));

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 100,
            'balance_after' => 100,
            'reason' => 'initial',
            'user_id' => $user->id,
        ]);

        $this->assertEquals(100, (new GetCurrentStockAction)($product));

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'quantity' => 30,
            'balance_after' => 70,
            'reason' => 'sale',
            'user_id' => $user->id,
        ]);

        $this->assertEquals(70, (new GetCurrentStockAction)($product));

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 50,
            'balance_after' => 120,
            'reason' => 'purchase',
            'user_id' => $user->id,
        ]);

        $this->assertEquals(120, (new GetCurrentStockAction)($product));
    }

    public function test_allows_null_balance_after(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => null,
            'reason' => 'purchase',
        ]);

        $this->assertNull($movement->balance_after);
    }

    public function test_allows_null_reference_fields(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'reference_type' => null,
            'reference_id' => null,
        ]);

        $this->assertNull($movement->reference_type);
        $this->assertNull($movement->reference_id);
    }

    public function test_stores_reference_fields(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'quantity' => 10,
            'balance_after' => 90,
            'reason' => 'sale',
            'reference_type' => 'App\\Models\\ServiceOrder',
            'reference_id' => '01JCTEST0000000000000001',
        ]);

        $this->assertEquals('App\\Models\\ServiceOrder', $movement->reference_type);
        $this->assertEquals('01JCTEST0000000000000001', $movement->reference_id);
    }
}
