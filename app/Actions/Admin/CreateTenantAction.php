<?php

namespace App\Actions\Admin;

use App\Actions\Tenant\User\CreateUserAction;
use Exception;
use Throwable;
use App\Dto\UserDto;
use App\Constants\Messages;
use App\Dto\TenantCreateDto;
use App\Models\Admin\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\admin\TenantManager;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\Tenant\TenantAlreadyExistsException;

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
        $databaseName = "";
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

            $tenantConnection = config('database.connections_names.tenant');
            DB::disconnect($tenantConnection);
            DB::purge($tenantConnection);

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

        DB::connection(config('database.connections_names.admin'))->statement($dataBaseCreateSQL);
    }

    private function dropDatabase(string $database): void
    {
        DB::connection(config('database.connections_names.admin'))
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
                '--database' => config('database.connections_names.tenant'),
                '--path' => 'database/migrations/tenants',
                '--force' => true,
            ]
        );
    }

    private function createUser(Tenant $tenant): void
    {
        /** @var CreateUserAction $userSeed */
        $userSeed = app(CreateUserAction::class);
        $userSeed(new UserDto(
            name: $tenant->name,
            email: $tenant->email,
            password: 'password'
        ));
    }
}
