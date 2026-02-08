<?php

namespace Tests\Feature\tenant;

use App\Models\Tenant\Supplier;
use Illuminate\Auth\Middleware\Authenticate;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    /**
     * Helper to make requests with tenant subdomain
     */
    protected function tenantRequest(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        // Simulate tenant subdomain request by setting the host
        // Disable authentication but keep tenant middleware to remove subdomain parameter
        return match (strtoupper($method)) {
            'GET' => $this->withoutMiddleware([Authenticate::class])->getJson(
                'http://test-tenant.localhost'.$uri
            ),
            'POST' => $this->withoutMiddleware([Authenticate::class])->postJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            'PUT' => $this->withoutMiddleware([Authenticate::class])->putJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            'DELETE' => $this->withoutMiddleware([Authenticate::class])->deleteJson(
                'http://test-tenant.localhost'.$uri,
                $data
            ),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: $method")
        };
    }

    public function testStoreCreatesSupplierSuccessfully(): void
    {
        $data = [
            'name' => 'Fornecedor Sucesso Ltda',
            'document_number' => '12345678000195',
            'trade_name' => 'Fornecedor Sucesso',
            'email' => 'sucesso@fornecedor.com',
            'phone' => '11987654321',
            'is_active' => true,
        ];

        $response = $this->tenantRequest('POST', '/suppliers', $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'supplier' => [
                    'id',
                    'name',
                    'document_number',
                ],
            ],
        ]);

        $this->assertDatabaseHas('supplier', [
            'document_number' => $data['document_number'],
        ]);
    }

    public function testStoreReturnsValidationErrorWhenNameMissing(): void
    {
        $data = [
            'document_number' => '98765432000100',
            'email' => 'teste@fornecedor.com',
        ];

        $response = $this->tenantRequest('POST', '/suppliers', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function testStoreReturnsValidationErrorWhenCnpjInvalid(): void
    {
        $data = [
            'name' => 'Fornecedor Teste',
            'document_number' => '12345',
            'email' => 'teste@fornecedor.com',
        ];

        $response = $this->tenantRequest('POST', '/suppliers', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_number']);
    }

    public function testStoreReturnsDuplicateErrorWhenCnpjExists(): void
    {
        Supplier::create([
            'name' => 'Fornecedor Existente',
            'document_number' => '11111111000111',
            'email' => 'existente@fornecedor.com',
        ]);

        $data = [
            'name' => 'Outro Fornecedor',
            'document_number' => '11111111000111',
            'email' => 'outro@fornecedor.com',
        ];

        $response = $this->tenantRequest('POST', '/suppliers', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_number']);
    }

    public function testFindReturnsSuppliersList(): void
    {
        Supplier::create([
            'name' => 'Fornecedor Lista 1',
            'document_number' => '22222222000122',
            'email' => 'lista1@fornecedor.com',
        ]);

        Supplier::create([
            'name' => 'Fornecedor Lista 2',
            'document_number' => '33333333000133',
            'email' => 'lista2@fornecedor.com',
        ]);

        $response = $this->tenantRequest('GET', '/suppliers/search');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'suppliers' => [
                    'data',
                    'current_page',
                    'total',
                ],
            ],
        ]);
    }

    public function testFindWithSearchParameter(): void
    {
        Supplier::create([
            'name' => 'Fornecedor Especial',
            'document_number' => '44444444000144',
            'email' => 'especial@fornecedor.com',
        ]);

        Supplier::create([
            'name' => 'Outro Fornecedor',
            'document_number' => '55555555000155',
            'email' => 'outro@fornecedor.com',
        ]);

        $response = $this->tenantRequest('GET', '/suppliers/search?search=Especial');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.suppliers.data')));
    }

    public function testFindOneReturnsSupplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Individual',
            'document_number' => '66666666000166',
            'email' => 'individual@fornecedor.com',
        ]);

        $response = $this->tenantRequest('GET', "/suppliers/{$supplier->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'supplier' => [
                    'id',
                    'name',
                    'document_number',
                ],
            ],
        ]);
    }

    public function testFindOneReturnsNotFound(): void
    {
        $fakeId = '01HQVX9K3ZYZ123456789ABCDE';

        $response = $this->tenantRequest('GET', "/suppliers/{$fakeId}");

        $response->assertStatus(404);
    }

    public function testUpdateUpdatesSupplierSuccessfully(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Original',
            'document_number' => '11222333000181',
            'email' => 'original@fornecedor.com',
        ]);

        $updateData = [
            'name' => 'Fornecedor Atualizado',
            'document_number' => '11222333000181',
            'email' => 'atualizado@fornecedor.com',
            'phone' => '11999998888',
        ];

        $response = $this->tenantRequest('PUT', "/suppliers/{$supplier->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('supplier', [
            'id' => $supplier->id,
            'name' => 'Fornecedor Atualizado',
        ]);
    }

    public function testUpdateReturnsNotFoundWhenSupplierDoesNotExist(): void
    {
        $fakeId = '01HQVX9K3ZYZ123456789ABCDE';

        $updateData = [
            'name' => 'Fornecedor Inexistente',
            'document_number' => '11222333000181',
            'email' => 'inexistente@fornecedor.com',
        ];

        $response = $this->tenantRequest('PUT', "/suppliers/{$fakeId}", $updateData);

        $response->assertStatus(404);
    }

    public function testDeleteRemovesSupplierSuccessfully(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Para Deletar',
            'document_number' => '99999999000199',
            'email' => 'deletar@fornecedor.com',
        ]);

        $response = $this->tenantRequest('DELETE', "/suppliers/{$supplier->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('supplier', [
            'id' => $supplier->id,
        ]);
    }

    public function testDeleteReturnsNotFoundWhenSupplierDoesNotExist(): void
    {
        $fakeId = '01HQVX9K3ZYZ123456789ABCDE';

        $response = $this->tenantRequest('DELETE', "/suppliers/{$fakeId}");

        $response->assertStatus(404);
    }
}
