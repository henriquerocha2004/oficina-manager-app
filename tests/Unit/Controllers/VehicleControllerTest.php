<?php

namespace Tests\Unit\Controllers;

use App\Actions\Tenant\Vehicle\CreateVehicleAction;
use App\Dto\VehicleDto;
use App\Http\Controllers\tenant\VehicleController;
use App\Http\Requests\tenant\VehicleRequest;
use App\Models\Tenant\Vehicle;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

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

        /** @var VehicleRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(VehicleRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($vehicleDto);

        $createdVehicle = new Vehicle;

        /** @var CreateVehicleAction&MockInterface $createAction */
        $createAction = Mockery::mock(CreateVehicleAction::class);
        $createAction->shouldReceive('__invoke')->andReturn($createdVehicle);

        $controller = new VehicleController;

        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('vehicle', $payload['data']);
    }
}
