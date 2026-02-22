<?php

namespace App\Enum\Tenant\ServiceOrder;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PIX = 'pix';
    case BANK_TRANSFER = 'bank_transfer';
    case CHECK = 'check';
}
