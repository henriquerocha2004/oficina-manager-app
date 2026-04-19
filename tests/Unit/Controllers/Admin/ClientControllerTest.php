<?php

namespace Tests\Unit\Controllers\Admin;

use App\Actions\Admin\Client\DeleteClientAction;
use App\Actions\Admin\Client\UpdateClientAction;
use App\Dto\Admin\ClientDto;
use App\Http\Controllers\admin\ClientController;
use App\Http\Requests\ClientTenantRequest;
use App\Http\Requests\admin\ClientRequest;
use App\Models\Admin\Client;
use App\Models\Admin\Tenant;
use App\Services\Admin\ClientTenantService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\AdminTestCase;

class ClientControllerTest extends AdminTestCase
{
    use MockeryPHPUnitIntegration;

    public function testStoreReturnsCreatedResponse(): void
    {
        $dto = ClientDto::fromArray([
            'name' => 'Empresa Teste',
            'email' => 'empresa@teste.com',
            'document' => '12345678901234',
        ]);

        /** @var ClientTenantRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(ClientTenantRequest::class);
        $requestMock->shouldReceive('toClientDto')->andReturn($dto);
        $requestMock->shouldReceive('tenantDomain')->andReturn('empresa-teste');
        $requestMock->shouldReceive('tenantStatus')->andReturn('active');
        $requestMock->shouldReceive('tenantTrialUntil')->andReturn(null);

        $createdClient = new Client();
        $createdTenant = new Tenant();

        /** @var ClientTenantService&MockInterface $clientTenantService */
        $clientTenantService = Mockery::mock(ClientTenantService::class);
        $clientTenantService->shouldReceive('create')->andReturn([
            'client' => $createdClient,
            'tenant' => $createdTenant,
        ]);

        $controller = new ClientController();
        $response = $controller->store($requestMock, $clientTenantService);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('client', $payload['data']);
        $this->assertArrayHasKey('tenant', $payload['data']);
    }

    public function testUpdateReturnsOkResponse(): void
    {
        $dto = ClientDto::fromArray([
            'name' => 'Atualizado',
            'email' => 'atualizado@teste.com',
            'document' => '11111111111111',
        ]);

        /** @var ClientRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(ClientRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($dto);

        /** @var UpdateClientAction&MockInterface $updateAction */
        $updateAction = Mockery::mock(UpdateClientAction::class);
        $updateAction->shouldReceive('__invoke')->andReturn(null);

        $controller = new ClientController();
        $response = $controller->update($requestMock, 'some-id', $updateAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDeleteReturnsOkResponse(): void
    {
        /** @var DeleteClientAction&MockInterface $deleteAction */
        $deleteAction = Mockery::mock(DeleteClientAction::class);
        $deleteAction->shouldReceive('__invoke')->andReturn(null);

        $controller = new ClientController();
        $response = $controller->delete('some-id', $deleteAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
