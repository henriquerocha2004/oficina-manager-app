<?php

namespace Tests\Unit\Actions\Stock;

use App\Actions\Tenant\Stock\GetCurrentStockAction;
use App\Actions\Tenant\Stock\MoveStockAction;
use App\Dto\StockMovementDto;
use App\Enum\Tenant\Stock\MovementTypeEnum;
use App\Enum\Tenant\Stock\StockMovementReasonEnum;
use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Stock\InsufficientStockException;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Throwable;

class MoveStockActionTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name' => 'Test Product',
            'category' => 'engine',
            'unit' => 'unit',
            'unit_price' => 100.00,
            'is_active' => true,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_moves_stock_in_successfully(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $dto = new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::IN,
            quantity: 10,
            reason: StockMovementReasonEnum::REASON_PURCHASE,
            notes: 'Initial stock'
        );

        $action = new MoveStockAction;
        $action($dto);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 10,
            'balance_after' => 10,
            'reason' => 'purchase',
            'notes' => 'Initial stock',
            'user_id' => $user->id,
        ]);
    }

    public function test_moves_stock_out_successfully(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 20,
            'balance_after' => 20,
            'reason' => 'initial',
            'user_id' => $user->id,
        ]);

        $dto = new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::OUT,
            quantity: 5,
            reason: StockMovementReasonEnum::REASON_SALE,
            notes: 'Sale order #123'
        );

        $action = new MoveStockAction;
        $action($dto);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'OUT',
            'quantity' => 5,
            'balance_after' => 15,
            'reason' => 'sale',
            'notes' => 'Sale order #123',
        ]);

        $this->assertEquals(15, (new GetCurrentStockAction)($this->product));
    }

    public function test_throws_insufficient_stock_exception_when_stock_is_low(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'IN',
            'quantity' => 5,
            'balance_after' => 5,
            'reason' => 'initial',
            'user_id' => $user->id,
        ]);

        $dto = new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::OUT,
            quantity: 10,
            reason: StockMovementReasonEnum::REASON_SALE
        );

        $this->expectException(InsufficientStockException::class);

        $action = new MoveStockAction;
        $action($dto);
    }

    public function test_throws_product_not_found_exception_when_product_does_not_exist(): void
    {
        $dto = new StockMovementDto(
            productId: '01JCTEST0000000000000000',
            movement: MovementTypeEnum::IN,
            quantity: 10,
            reason: StockMovementReasonEnum::REASON_PURCHASE
        );

        $this->expectException(ProductNotFoundException::class);

        $action = new MoveStockAction;
        $action($dto);
    }

    public function test_calculates_balance_after_correctly_for_multiple_movements(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $action = new MoveStockAction;

        $action(new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::IN,
            quantity: 100,
            reason: StockMovementReasonEnum::REASON_INITIAL
        ));

        $this->assertEquals(100, (new GetCurrentStockAction)($this->product));

        $action(new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::OUT,
            quantity: 30,
            reason: StockMovementReasonEnum::REASON_SALE
        ));

        $this->assertEquals(70, (new GetCurrentStockAction)($this->product));

        $action(new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::IN,
            quantity: 50,
            reason: StockMovementReasonEnum::REASON_PURCHASE
        ));

        $this->assertEquals(120, (new GetCurrentStockAction)($this->product));

        $action(new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::OUT,
            quantity: 20,
            reason: StockMovementReasonEnum::REASON_LOSS
        ));

        $this->assertEquals(100, (new GetCurrentStockAction)($this->product));
    }

    public function test_stores_user_id_correctly(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $dto = new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::IN,
            quantity: 10,
            reason: StockMovementReasonEnum::REASON_PURCHASE
        );

        $action = new MoveStockAction;
        $action($dto);

        $movement = StockMovement::where('product_id', $this->product->id)->first();

        $this->assertNotNull($movement->user_id);
        $this->assertEquals($user->id, $movement->user_id);
    }

    public function test_allows_null_notes(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $dto = new StockMovementDto(
            productId: $this->product->id,
            movement: MovementTypeEnum::IN,
            quantity: 10,
            reason: StockMovementReasonEnum::REASON_PURCHASE,
            notes: null
        );

        $action = new MoveStockAction;
        $action($dto);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'notes' => null,
        ]);
    }
}
