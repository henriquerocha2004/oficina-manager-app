<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Tenant;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Throwable;

/**
 * Separate test class for tenant creation because CreateTenantAction runs
 * CREATE DATABASE, which PostgreSQL forbids inside a transaction block.
 * AdminTestCase wraps manager_test in a transaction, so this test extends
 * TestCase (which only wraps the default tenant_test connection) and relies
 * on tearDown for manual cleanup.
 */
class TenantStoreIntegrationTest extends TestCase
{
    private string $createdDatabaseName = '';
    private int|string|null $createdTenantId = null;

    protected function adminRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        $url = 'http://localhost/admin' . $uri;

        return match (strtoupper($method)) {
            'GET' => $this->withoutMiddleware([Authenticate::class])->getJson($url),
            'POST' => $this->withoutMiddleware([Authenticate::class])->postJson($url, $data),
            'PUT' => $this->withoutMiddleware([Authenticate::class])->putJson($url, $data),
            'DELETE' => $this->withoutMiddleware([Authenticate::class])->deleteJson($url),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method"),
        };
    }

    public function testStoreCreatesNewTenant(): void
    {
        $uniqueId = uniqid();
        $data = [
            'name' => 'Oficina Integration Test',
            'email' => "integration-{$uniqueId}@test.com",
            'domain' => 'int-' . $uniqueId,
            'document' => substr($uniqueId . '00000000000000', 0, 14),
        ];

        $response = $this->adminRequest('POST', '/tenants', $data);

        $response->assertStatus(201);

        $adminConnection = config('database.connections_names.admin');

        $tenant = Tenant::on($adminConnection)->where('domain', $data['domain'])->first();
        $this->assertNotNull($tenant, 'Tenant should have been created');

        $this->createdTenantId = $tenant->id;
        $this->createdDatabaseName = $tenant->database_name;
    }

    protected function tearDown(): void
    {
        $adminConnection = config('database.connections_names.admin');
        $tenantConnection = config('database.connections_names.tenant');

        DB::disconnect($tenantConnection);
        DB::purge($tenantConnection);

        if ($this->createdDatabaseName) {
            try {
                DB::connection($adminConnection)->statement("
                    SELECT pg_terminate_backend(pg_stat_activity.pid)
                    FROM pg_stat_activity
                    WHERE pg_stat_activity.datname = '{$this->createdDatabaseName}'
                      AND pid <> pg_backend_pid()
                ");
                DB::connection($adminConnection)->statement("DROP DATABASE IF EXISTS \"{$this->createdDatabaseName}\"");
            } catch (Throwable) {
            }
        }

        if ($this->createdTenantId) {
            try {
                DB::connection($adminConnection)->table('tenant')->where('id', $this->createdTenantId)->delete();
            } catch (Throwable) {
            }
        }

        parent::tearDown();
    }
}
