<?php

namespace App\Services\admin;

use App\Models\Tenant;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class TenantManager
{
    protected Tenant $tenant;

    public function setTenant(Tenant $tenant): self
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function getTenant(): Tenant
    {
        return $this->tenant;
    }

    public function loadTenant(string $domain): ?self
    {
        $tenant = Tenant::whereDomain($this->extractSubDomain($domain))->first();
        if (!$tenant) {
            return $this;
        }

        $this->setTenant($tenant);

        return $this;
    }

    public function switchTenant(int|Tenant $tenant): void
    {
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        config(['database.connections.tenant.database' => $tenant->database_name]);
        DB::purge('tenant');
        DB::connection('tenant')->reconnect();
        $this->setTenant($tenant);
        DB::setDefaultConnection('tenant');
        $this->setTenantGlobally();
    }

    private function setTenantGlobally(): void
    {
        App::singleton('tenant', fn () => $this->getTenant());
    }

    private function extractSubDomain(string $domain): string
    {
        return explode('.', $domain)[0];
    }
}
