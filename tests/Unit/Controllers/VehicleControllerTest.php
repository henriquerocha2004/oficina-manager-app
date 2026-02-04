<?php

namespace Tests\Unit\Controllers;

use Mockery;
use Tests\TestCase;
use App\Dto\VehicleDto;
use App\Models\Tenant\Vehicle;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\tenant\VehicleController;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class VehicleControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testStoreReturnsCreatedResponse(): void
    {
        $data = [
            'license_plate' => 'TST-9999',
            'brand' => 'Test Brand',
            'model' => 'Test Model',
            'year' => 2023,
            'client_id' => '01HXE7Q8N3K9Y2Z1P5V7M4W6J8',
        ];

        $vehicleDto = VehicleDto::fromArray($data);

        $requestMock = Mockery::mock('App\\Http\\Requests\\tenant\\VehicleRequest');
        $requestMock->shouldReceive('toDto')->andReturn($vehicleDto);

        $createdVehicle = new Vehicle();

        $createAction = Mockery::mock('App\\Actions\\Tenant\\Vehicle\\CreateVehicleAction');
        $createAction->shouldReceive('__invoke')->andReturn($createdVehicle);

        $controller = new VehicleController();

        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('vehicle', $payload['data']);
    }
}
