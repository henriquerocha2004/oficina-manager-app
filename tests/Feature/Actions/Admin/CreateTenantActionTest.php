<?php

namespace Tests\Feature\Actions\Admin;

use App\Actions\Admin\CreateTenantAction;
use App\Dto\TenantCreateDto;
use App\Models\Admin\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Throwable;

class CreateTenantActionTest extends TestCase
{
    private string $createdDatabaseName = '';
    private ?int $tenantId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $adminConnection = config('database.connections_names.admin');

        Artisan::call('migrate', [
            '--force' => true,
            '--database' => $adminConnection,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testItCanCreateATenantWithDatabaseAndUser(): void
    {
        $dto = new TenantCreateDto(
            name: 'Test Tenant',
            document: '12345678901',
            email: 'admin@testtenant.com',
            domain: 'test-tenant-' . uniqid()
        );

        $action = app(CreateTenantAction::class);
        $adminConnection = config('database.connections_names.admin');
        $tenantConnection = config('database.connections_names.tenant');

        $tenant = $action($dto);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->tenantId = $tenant->id;
        $this->createdDatabaseName = $tenant->database_name;

        $this->assertDatabaseHas('tenant', [
            'id' => $tenant->id,
            'database_name' => $tenant->database_name
        ], $adminConnection);

        $this->assertTrue(Schema::connection($tenantConnection)->hasTable('users'));

        $this->assertDatabaseHas('users', [
            'email' => $dto->email,
            'name' => $dto->name,
        ], $tenantConnection);
    }

    protected function tearDown(): void
    {
        $adminConnection = config('database.connections_names.admin');
        $tenantConnection = config('database.connections_names.tenant');

        DB::disconnect($tenantConnection);
        DB::purge($tenantConnection);

        if ($this->createdDatabaseName) {
            try {
                DB::disconnect($tenantConnection);
                DB::connection($adminConnection)->statement("
                    SELECT pg_terminate_backend(pg_stat_activity.pid)
                    FROM pg_stat_activity
                    WHERE pg_stat_activity.datname = '{$this->createdDatabaseName}'
                      AND pid <> pg_backend_pid();
                ");
                DB::connection($adminConnection)->statement("DROP DATABASE IF EXISTS \"{$this->createdDatabaseName}\"");
            } catch (Throwable $e) {}
        }

        if ($this->tenantId) {
            try {
                DB::connection($adminConnection)->table('tenant')->where('id', $this->tenantId)->delete();
            } catch (Throwable $e) {}
        }

        parent::tearDown();
    }
}
