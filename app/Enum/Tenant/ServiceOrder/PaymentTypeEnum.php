<?php

namespace App\Enum\Tenant\ServiceOrder;

enum PaymentTypeEnum: string
{
    case PAYMENT = 'payment';
    case REFUND  = 'refund';
}
