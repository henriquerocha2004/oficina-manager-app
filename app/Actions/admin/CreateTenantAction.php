<?php

namespace App\Actions\admin;

use App\Constants\Messages;
use App\Dto\TenantCreateDto;
use App\Exceptions\Tenant\TenantAlreadyExistsException;
use App\Models\Tenant;
use App\Services\admin\TenantManager;
use Database\Seeders\AdminTenantUsers;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CreateTenantAction
{
    private const string PREFIX_DATABASE = 'tenant_%s';

    public function __construct(
       private readonly TenantManager $tenantManager,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(TenantCreateDto $tenantCreateDto): ?Tenant
    {
        $tenant = Tenant::whereDomain($tenantCreateDto->domain)->first();
        throw_if(!is_null($tenant), TenantAlreadyExistsException::class);

        $tenantCreated =  Tenant::query()->create([
            'name' => $tenantCreateDto->name,
            'document' => $tenantCreateDto->document,
            'domain' => $tenantCreateDto->domain,
            'email' => $tenantCreateDto->email,
        ]);

        try {
            $databaseName = sprintf(self::PREFIX_DATABASE, $tenantCreated->id);
            $this->createDatabase($databaseName);
            $tenantCreated->update(['database_name' => $databaseName]);
            $this->connectToTenantDatabaseCreated($tenantCreated);
            $this->runMigrations();
            $this->createUser($tenantCreated);

            return $tenantCreated;
        } catch (Throwable $exception) {
            Log::error(Messages::FAILED_TO_CREATE_DATABASE_TENANT, [
                'error' => $exception->getMessage(),
                'tenant' => $tenantCreated->toArray(),
            ]);
            $tenantCreated->delete();
            $this->dropDatabase($databaseName);

            throw new Exception(
                Messages::FAILED_TO_CREATE_DATABASE_TENANT,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function createDatabase(string $databaseName): void
    {
        $dataBaseCreateSQL = sprintf('
                CREATE DATABASE "%s"
                WITH ENCODING \'UTF8\'
                LC_COLLATE \'en_US.UTF-8\'
                LC_CTYPE \'en_US.UTF-8\'
                TEMPLATE template0
            ', $databaseName);

        DB::connection('manager')->statement($dataBaseCreateSQL);
    }

    private function dropDatabase(string $database): void
    {
        DB::connection('manager')
            ->statement(sprintf('DROP DATABASE IF EXISTS "%s"', $database));
    }

    private function connectToTenantDatabaseCreated(Tenant $tenant): void
    {
        $this->tenantManager->setTenant($tenant);
        $this->tenantManager->switchTenant($tenant);
    }

    private function runMigrations(): void
    {
        Artisan::call(
            command: 'migrate',
            parameters: [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenants',
                '--force' => true,
            ]
        );
    }

    private function createUser(Tenant $tenant): void
    {
        /** @var AdminTenantUsers $userSeed */
        $userSeed = app(AdminTenantUsers::class);
        $userSeed->run($tenant->name, $tenant->email);
    }
}
