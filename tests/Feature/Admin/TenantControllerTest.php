<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Client;
use App\Models\Admin\Tenant;
use Illuminate\Auth\Middleware\Authenticate;
use Tests\AdminTestCase;

class TenantControllerTest extends AdminTestCase
{

    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

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

    public function testUpdateTenantStatus(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);

        $data = [
            'name' => $tenant->name,
            'email' => $tenant->email,
            'domain' => $tenant->domain,
            'document' => $tenant->document,
            'status' => 'inactive',
        ];

        $response = $this->adminRequest('PUT', "/tenants/{$tenant->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tenant', [
            'id' => $tenant->id,
            'status' => 'inactive',
        ], $this->adminConnection);
    }

    public function testUpdateTenantValidatesStatus(): void
    {
        $tenant = Tenant::factory()->create();

        $data = [
            'name' => $tenant->name,
            'email' => $tenant->email,
            'domain' => $tenant->domain,
            'document' => $tenant->document,
            'status' => 'invalido',
        ];

        $response = $this->adminRequest('PUT', "/tenants/{$tenant->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function testDeleteSoftDeletesTenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->adminRequest('DELETE', "/tenants/{$tenant->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tenant', ['id' => $tenant->id], $this->adminConnection);
    }

    public function testSearchReturnsTenantsWithClient(): void
    {
        Tenant::factory()->count(3)->create();

        $response = $this->adminRequest('GET', '/tenants/search');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'tenants' => ['data', 'total', 'current_page'],
            ],
        ]);
    }

    public function testSearchFiltersByStatus(): void
    {
        Tenant::factory()->create(['status' => 'active']);
        Tenant::factory()->inactive()->create();

        $response = $this->adminRequest('GET', '/tenants/search?filters[status]=inactive');

        $response->assertStatus(200);
        $data = $response->json('data.tenants.data');
        foreach ($data as $item) {
            $this->assertSame('inactive', $item['status']);
        }
    }

    public function testUpdateTenantClientId(): void
    {
        $client = Client::factory()->create();
        $tenant = Tenant::factory()->create(['client_id' => null]);

        $data = [
            'name' => $tenant->name,
            'email' => $tenant->email,
            'domain' => $tenant->domain,
            'document' => $tenant->document,
            'status' => $tenant->status,
            'client_id' => $client->id,
        ];

        $response = $this->adminRequest('PUT', "/tenants/{$tenant->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tenant', [
            'id' => $tenant->id,
            'client_id' => $client->id,
        ], $this->adminConnection);
    }

    public function testFindOneReturnsTenant(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->adminRequest('GET', "/tenants/{$tenant->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.tenant.id', $tenant->id);
    }
}
