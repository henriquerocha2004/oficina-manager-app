<?php

namespace App\Actions\Tenant\Stock;

use App\Dto\StockMovementDto;
use App\Enum\Tenant\Stock\MovementTypeEnum;
use App\Enum\Tenant\Stock\StockMovementReasonEnum;
use App\Exceptions\Product\ProductNotFoundException;
use App\Exceptions\Stock\InsufficientStockException;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class MoveStockAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(StockMovementDto $movementDto): void
    {
        $product = Product::query()->whereId($movementDto->productId)->first();
        if (is_null($product)) {
            throw new ProductNotFoundException();
        }

        DB::transaction(function () use ($product, $movementDto) {
            match ($movementDto->movement) {
                MovementTypeEnum::IN => $this->in($product, $movementDto->quantity, $movementDto->reason, $movementDto->notes),
                MovementTypeEnum::OUT => $this->out($product, $movementDto->quantity, $movementDto->reason, $movementDto->notes)
            };
        });
    }

    private function in(Product $product, int $quantity, StockMovementReasonEnum $reason, ?string $notes): void
    {
        $getCurrentStockAction = app(GetCurrentStockAction::class);
        $currentStock = $getCurrentStockAction($product);
        $balanceAfter = $currentStock + $quantity;

        StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => MovementTypeEnum::IN->value,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'reason' => $reason->value,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);
    }

    private function out(Product $product, int $quantity, StockMovementReasonEnum $reason, ?string $notes): void
    {
        $getCurrentStockAction = app(GetCurrentStockAction::class);
        $currentStock = $getCurrentStockAction($product);

        if ($currentStock < $quantity) {
            throw new InsufficientStockException();
        }

        $balanceAfter = $currentStock - $quantity;

        StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => MovementTypeEnum::OUT->value,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'reason' => $reason->value,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);
    }
}
