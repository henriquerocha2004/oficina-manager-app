<?php

namespace App\Console\Commands;

use App\Actions\admin\CreateTenantAction;
use App\Dto\TenantCreateDto;
use Illuminate\Console\Command;
use Throwable;

class CreateTenantCommand extends Command
{
    protected $signature = 'app:create-tenant-command';

    protected $description = 'Comando para criar um novo tenant';

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->info('Vamos iniciar a criação de um novo tenant.');

        $tenantName = $this->ask("Informe o nome do Tenant: ");
        $tenantEmail = $this->ask("Informe o Email do Tenant: ");
        $tenantDocument = $this->ask("Informe o Documento do Tenant: ");
        $tenantSubdomain = $this->ask("Informe o Subdomínio do Tenant: ");

        $dto = new TenantCreateDto(
            name: $tenantName,
            document: $tenantDocument,
            email: $tenantEmail,
            domain: $tenantSubdomain
        );

        $tenantCreateAction = app(CreateTenantAction::class);
        $tenantCreateAction($dto);

        $this->info("Tenant criado com sucesso!");
    }
}
