<?php

namespace Tests\Unit\Controllers\Admin;

use App\Actions\Admin\Tenant\DeleteTenantAction;
use App\Actions\Admin\Tenant\UpdateTenantAction;
use App\Dto\Admin\TenantUpdateDto;
use App\Dto\TenantCreateDto;
use App\Http\Controllers\admin\TenantController;
use App\Http\Requests\admin\TenantRequest;
use App\Models\Admin\Tenant;
use App\Services\Admin\TenantProvisioningService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\AdminTestCase;

class TenantControllerTest extends AdminTestCase
{
    use MockeryPHPUnitIntegration;

    public function testStoreReturnsCreatedResponse(): void
    {
        $createDto = new TenantCreateDto(
            name: 'Tenant Teste',
            document: '12345678901234',
            email: 'tenant@teste.com',
            domain: 'tenant-teste',
        );

        /** @var TenantRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(TenantRequest::class);
        $requestMock->shouldReceive('toCreateDto')->andReturn($createDto);

        $createdTenant = new Tenant();

        /** @var TenantProvisioningService&MockInterface $tenantProvisioningService */
        $tenantProvisioningService = Mockery::mock(TenantProvisioningService::class);
        $tenantProvisioningService->shouldReceive('create')->andReturn($createdTenant);

        $controller = new TenantController();
        $response = $controller->store($requestMock, $tenantProvisioningService);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('tenant', $payload['data']);
    }

    public function testUpdateReturnsOkResponse(): void
    {
        $dto = new TenantUpdateDto(
            name: 'Atualizado',
            email: 'atualizado@teste.com',
            domain: 'atualizado',
            status: 'inactive',
        );

        /** @var TenantRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(TenantRequest::class);
        $requestMock->shouldReceive('toUpdateDto')->andReturn($dto);

        /** @var UpdateTenantAction&MockInterface $updateAction */
        $updateAction = Mockery::mock(UpdateTenantAction::class);
        $updateAction->shouldReceive('__invoke')->andReturn(null);

        $controller = new TenantController();
        $response = $controller->update($requestMock, 1, $updateAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDeleteReturnsOkResponse(): void
    {
        /** @var DeleteTenantAction&MockInterface $deleteAction */
        $deleteAction = Mockery::mock(DeleteTenantAction::class);
        $deleteAction->shouldReceive('__invoke')->andReturn(null);

        $controller = new TenantController();
        $response = $controller->delete(1, $deleteAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
