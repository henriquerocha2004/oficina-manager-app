<?php

namespace Tests\Feature\Admin;

use App\Models\Admin\Client;
use Illuminate\Auth\Middleware\Authenticate;
use Tests\AdminTestCase;

class ClientControllerTest extends AdminTestCase
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

    public function testStoreCreatesClient(): void
    {
        $uniqueId = uniqid();
        $data = [
            'name' => 'Empresa Feature Test',
            'email' => "feature-{$uniqueId}@empresa.com",
            'document' => substr($uniqueId . '00000000000000', 0, 14),
            'phone' => '11999990000',
        ];

        $response = $this->adminRequest('POST', '/clients', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('clients', [
            'email' => $data['email'],
            'document' => $data['document'],
        ], $this->adminConnection);
    }

    public function testStoreValidatesRequiredFields(): void
    {
        $response = $this->adminRequest('POST', '/clients', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'document']);
    }

    public function testUpdateChangesClientData(): void
    {
        $client = Client::factory()->create(['name' => 'Nome Original']);

        $data = [
            'name' => 'Nome Atualizado',
            'email' => $client->email,
            'document' => $client->document,
        ];

        $response = $this->adminRequest('PUT', "/clients/{$client->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Nome Atualizado',
        ], $this->adminConnection);
    }

    public function testDeleteSoftDeletesClient(): void
    {
        $client = Client::factory()->create();

        $response = $this->adminRequest('DELETE', "/clients/{$client->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('clients', ['id' => $client->id], $this->adminConnection);
    }

    public function testSearchReturnsFilteredPaginatedResults(): void
    {
        Client::factory()->create(['name' => 'Alfa Corp']);
        Client::factory()->create(['name' => 'Beta Corp']);

        $response = $this->adminRequest('GET', '/clients/search?search=Alfa');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'clients' => ['data', 'total', 'current_page'],
            ],
        ]);
    }

    public function testFindOneReturnsClient(): void
    {
        $client = Client::factory()->create();

        $response = $this->adminRequest('GET', "/clients/{$client->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.client.id', $client->id);
    }
}
