<?php

namespace App\Enum\Tenant\ServiceOrder;

enum ServiceOrderEventTypeEnum: string
{
    case STATUS_CHANGED = 'status_changed';
    case ITEM_ADDED = 'item_added';
    case ITEM_REMOVED = 'item_removed';
    case DIAGNOSIS_UPDATED = 'diagnosis_updated';
    case PAYMENT_RECEIVED = 'payment_received';
    case NOTE_ADDED = 'note_added';
}
