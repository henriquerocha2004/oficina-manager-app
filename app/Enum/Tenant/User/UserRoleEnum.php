<?php

namespace App\Enum\Tenant\User;

enum UserRoleEnum: string
{
    case ADMINISTRATOR = 'administrator';
    case RECEPTION = 'reception';
    case MECHANIC = 'mechanic';
}
