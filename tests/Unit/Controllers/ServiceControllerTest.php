<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use App\Http\Controllers\tenant\ServiceController;
use App\Dto\ServiceDto;
use App\Models\Tenant\Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

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

        $requestMock = Mockery::mock('App\\Http\\Requests\\tenant\\ServiceRequest');
        $requestMock->shouldReceive('toDto')->andReturn($serviceDto);

        $createdService = new Service();

        $createAction = Mockery::mock('App\\Actions\\Tenant\\Service\\CreateServiceAction');
        $createAction->shouldReceive('__invoke')->andReturn($createdService);

        $controller = new ServiceController();

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

        $requestMock = Mockery::mock('App\\Http\\Requests\\tenant\\ServiceRequest');
        $requestMock->shouldReceive('toDto')->andReturn($serviceDto);

        $updateAction = Mockery::mock('App\\Actions\\Tenant\\Service\\UpdateServiceAction');
        $updateAction->shouldReceive('__invoke')->andReturnNull();

        $controller = new ServiceController();
        $serviceId = (string) Ulid::generate();

        $response = $controller->update($requestMock, $serviceId, $updateAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDeleteReturnsSuccessResponse(): void
    {
        $deleteAction = Mockery::mock('App\\Actions\\Tenant\\Service\\DeleteServiceAction');
        $deleteAction->shouldReceive('__invoke')->andReturnNull();

        $controller = new ServiceController();
        $serviceId = (string) Ulid::generate();

        $response = $controller->delete($serviceId, $deleteAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testFindOneReturnsServiceSuccessfully(): void
    {
        $service = new Service();

        $findOneAction = Mockery::mock('App\\Actions\\Tenant\\Service\\FindOneServiceAction');
        $findOneAction->shouldReceive('__invoke')->andReturn($service);

        $controller = new ServiceController();
        $serviceId = (string) Ulid::generate();

        $response = $controller->findOne($serviceId, $findOneAction);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('service', $payload['data']);
    }
}
