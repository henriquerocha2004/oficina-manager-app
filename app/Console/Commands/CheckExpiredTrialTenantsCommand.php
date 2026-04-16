<?php

namespace App\Console\Commands;

use App\Enum\Admin\TenantStatus;
use App\Models\Admin\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredTrialTenantsCommand extends Command
{
    protected $signature = 'app:check-expired-trial-tenants';

    protected $description = 'Verifica tenants em trial expirados e os desativa';

    public function handle(): void
    {
        $count = Tenant::query()
            ->where('status', TenantStatus::Trial->value)
            ->where('trial_until', '<', now())
            ->update(['status' => TenantStatus::Inactive->value]);

        Log::info("CheckExpiredTrialTenants: {$count} tenant(s) desativado(s) por expiração de trial.");
        $this->info("{$count} tenant(s) desativado(s) por expiração de trial.");
    }
}
