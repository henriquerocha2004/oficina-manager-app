<?php

namespace App\Services\Admin;

use App\Actions\Admin\Client\CreateClientAction;
use App\Dto\Admin\ClientDto;
use App\Dto\TenantCreateDto;
use App\Models\Admin\Client;
use App\Models\Admin\Tenant;
use App\Constants\Messages;
use Exception;
use Throwable;

class ClientTenantService
{
    public function __construct(
        private readonly CreateClientAction $createClientAction,
        private readonly TenantProvisioningService $tenantProvisioningService,
    ) {
    }

    /**
     * @return array{client: Client, tenant: Tenant}
     * @throws Throwable
     */
    public function create(
        ClientDto $clientDto,
        string $domain,
        string $status,
        ?string $trialUntil,
    ): array {
        $client = ($this->createClientAction)($clientDto);

        try {
            $tenantDto = new TenantCreateDto(
                name: $clientDto->name,
                document: $clientDto->document,
                email: $clientDto->email,
                domain: $domain,
                status: $status,
                trial_until: $trialUntil,
                client_id: $client->id,
            );

            $tenant = $this->tenantProvisioningService->create($tenantDto);

            if (is_null($tenant)) {
                throw new Exception(Messages::ADMIN_TENANT_CREATION_ERROR);
            }
        } catch (Throwable $exception) {
            $client->delete();

            throw $exception;
        }

        return [
            'client' => $client,
            'tenant' => $tenant,
        ];
    }
}
