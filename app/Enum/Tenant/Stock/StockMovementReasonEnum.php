<?php

namespace App\Enum\Tenant\Stock;

enum StockMovementReasonEnum: string
{
    case REASON_PURCHASE = 'purchase';
    case REASON_SALE = 'sale';
    case REASON_ADJUSTMENT = 'adjustment';
    case REASON_LOSS = 'loss';
    case REASON_RETURN = 'return';
    case REASON_TRANSFER = 'transfer';
    case REASON_INITIAL = 'initial';
    case REASON_OTHER = 'other';
}
