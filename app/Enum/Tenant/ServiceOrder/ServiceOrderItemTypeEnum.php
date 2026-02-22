<?php

namespace App\Enum\Tenant\ServiceOrder;

enum ServiceOrderItemTypeEnum: string
{
    case SERVICE = 'service';
    case PART = 'part';
}
