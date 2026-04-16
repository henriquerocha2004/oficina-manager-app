<?php

namespace App\Actions\Admin\Tenant;

use App\Exceptions\Admin\TenantNotFoundException;
use App\Models\Admin\Tenant;

class DeleteTenantAction
{
    public function __invoke(int $id): void
    {
        $tenant = Tenant::query()->find($id);

        if ($tenant === null) {
            throw new TenantNotFoundException();
        }

        $tenant->delete();
    }
}
