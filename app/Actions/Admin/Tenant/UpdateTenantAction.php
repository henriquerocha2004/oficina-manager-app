<?php

namespace App\Actions\Admin\Tenant;

use App\Dto\Admin\TenantUpdateDto;
use App\Exceptions\Admin\TenantNotFoundException;
use App\Models\Admin\Tenant;

class UpdateTenantAction
{
    public function __invoke(TenantUpdateDto $dto, int $id): void
    {
        $tenant = Tenant::query()->find($id);

        if (is_null($tenant)) {
            throw new TenantNotFoundException();
        }

        $tenant->update([
            'name' => $dto->name,
            'email' => $dto->email,
            'domain' => $dto->domain,
            'status' => $dto->status,
            'client_id' => $dto->client_id,
            'trial_until' => $dto->trial_until,
        ]);
    }
}
