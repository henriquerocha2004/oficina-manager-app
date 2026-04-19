<?php

namespace App\Services\Admin;

use App\Actions\Admin\CreateTenantAction;
use App\Constants\Messages;
use App\Dto\TenantCreateDto;
use App\Models\Admin\Tenant;
use App\Notifications\Admin\TenantAccessCreatedNotification;
use Exception;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class TenantProvisioningService
{
    public function __construct(
        private readonly CreateTenantAction $createTenantAction,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function create(TenantCreateDto $tenantCreateDto): Tenant
    {
        $password = Str::random(12);
        $tenant = ($this->createTenantAction)($tenantCreateDto, $password);

        if (is_null($tenant)) {
            throw new Exception(Messages::ADMIN_TENANT_CREATION_ERROR);
        }

        Notification::route('mail', $tenant->email)
            ->notify(new TenantAccessCreatedNotification($tenant, $password));

        return $tenant;
    }
}
