<?php

namespace App\Actions\Admin\Tenant;

use App\Exceptions\Admin\TenantNotFoundException;
use App\Models\Admin\Tenant;

class FindOneTenantAction
{
    public function __invoke(int $id): Tenant
    {
        $tenant = Tenant::query()->with('client')->find($id);

        if ($tenant === null) {
            throw new TenantNotFoundException();
        }

        return $tenant;
    }
}
