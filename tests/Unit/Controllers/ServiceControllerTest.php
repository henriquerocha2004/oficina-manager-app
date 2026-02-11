<?php

namespace Tests\Unit\Controllers;

use App\Actions\Tenant\Service\CreateServiceAction;
use App\Actions\Tenant\Service\DeleteServiceAction;
use App\Actions\Tenant\Service\FindOneServiceAction;
use App\Actions\Tenant\Service\UpdateServiceAction;
use App\Dto\ServiceDto;
use App\Http\Controllers\tenant\ServiceController;
use App\Http\Requests\tenant\ServiceRequest;
use App\Models\Tenant\Service;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testStoreReturnsCreatedResponse(): void
    {
        $data = [
            'name' => 'Troca de óleo',
            'description' => 'Troca completa de óleo',
            'base_price' => 150.00,
            'category' => Service::CATEGORY_MAINTENANCE,
            'estimated_time' => 60,
            'is_active' => true,
        ];

        $serviceDto = ServiceDto::fromArray($data);

        /** @var ServiceRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(ServiceRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($serviceDto);

        $createdService = new Service;

        /** @var CreateServiceAction&MockInterface $createAction */
        $createAction = Mockery::mock(CreateServiceAction::class);
        $createAction->shouldReceive('__invoke')->andReturn($createdService);

        $controller = new ServiceController;

        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('service', $payload['data']);
    }

    public function testUpdateReturnsSuccessResponse(): void
    {
        $data = [
            'name' => 'Troca de óleo completa',
            'base_price' => 200.00,
            'category' => Service::CATEGORY_MAINTENANCE,
        ];

        $serviceDto = ServiceDto::fromArray($data);

        /** @var ServiceRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(ServiceRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($serviceDto);

        /** @var UpdateServiceAction&MockInterface $updateAction */
        $updateAction = Mockery::mock(UpdateServiceAction::class);
        $updateAction->shouldReceive('__invoke')->andReturnNull();

        $controller = new ServiceController;
        $serviceId = (string) Ulid::generate();

        $response = $controller->update($requestMock, $serviceId, $updateAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDeleteReturnsSuccessResponse(): void
    {
        /** @var DeleteServiceAction&MockInterface $deleteAction */
        $deleteAction = Mockery::mock(DeleteServiceAction::class);
        $deleteAction->shouldReceive('__invoke')->andReturnNull();

        $controller = new ServiceController;
        $serviceId = (string) Ulid::generate();

        $response = $controller->delete($serviceId, $deleteAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testFindOneReturnsServiceSuccessfully(): void
    {
        $service = new Service;

        /** @var FindOneServiceAction&MockInterface $findOneAction */
        $findOneAction = Mockery::mock(FindOneServiceAction::class);
        $findOneAction->shouldReceive('__invoke')->andReturn($service);

        $controller = new ServiceController;
        $serviceId = (string) Ulid::generate();

        $response = $controller->findOne($serviceId, $findOneAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('service', $payload['data']);
    }
}
