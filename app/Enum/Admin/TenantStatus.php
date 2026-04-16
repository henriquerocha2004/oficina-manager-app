<?php

namespace App\Enum\Admin;

enum TenantStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Trial = 'trial';
}
