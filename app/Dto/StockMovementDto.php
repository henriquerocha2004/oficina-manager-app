<?php

namespace App\Dto;

use App\Enum\Tenant\Stock\MovementTypeEnum;
use App\Enum\Tenant\Stock\StockMovementReasonEnum;

readonly class StockMovementDto
{
    public function __construct(
        public string $productId,
        public MovementTypeEnum $movement,
        public int $quantity,
        public StockMovementReasonEnum $reason,
        public ?string $notes = null
    ) {
    }
}
