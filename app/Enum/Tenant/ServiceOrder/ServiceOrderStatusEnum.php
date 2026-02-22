<?php

namespace App\Enum\Tenant\ServiceOrder;

enum ServiceOrderStatusEnum: string
{
    case DRAFT = 'draft';
    case WAITING_APPROVAL = 'waiting_approval';
    case APPROVED = 'approved';
    case IN_PROGRESS = 'in_progress';
    case WAITING_PAYMENT = 'waiting_payment';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
