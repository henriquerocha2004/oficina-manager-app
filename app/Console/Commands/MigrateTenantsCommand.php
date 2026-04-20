<?php

namespace App\Console\Commands;

use App\Models\Admin\Tenant;
use App\Services\admin\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MigrateTenantsCommand extends Command
{
    protected $signature = 'app:migrate-tenants';

    protected $description = 'Roda as migrations para todos os tenants cadastrados';

    public function __construct(private readonly TenantManager $tenantManager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->info('Nenhum tenant encontrado. Cadastre um tenant para continuar.');
            return self::SUCCESS;
        }

        $connectionName = config('database.connections_names.tenant');
        $success = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            try {
                $this->tenantManager->switchTenant($tenant);

                Artisan::call('migrate', [
                    '--database' => $connectionName,
                    '--path' => 'database/migrations/tenants',
                    '--force' => true,
                ]);

                $this->info("Tenant [{$tenant->domain}] migrado com sucesso.");
                Log::info("MigrateTenants: tenant [{$tenant->domain}] migrado com sucesso.");
                $success++;
            } catch (\Throwable $e) {
                $this->error("Tenant [{$tenant->domain}] falhou: {$e->getMessage()}");
                Log::error("MigrateTenants: tenant [{$tenant->domain}] falhou.", ['error' => $e->getMessage()]);
                $failed++;
            }
        }

        $this->info("Concluído: {$success} sucesso(s), {$failed} falha(s).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
