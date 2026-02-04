<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use App\Http\Controllers\tenant\ClientController;
use App\Dto\ClientDto;
use Symfony\Component\HttpFoundation\Response;

class ClientControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testStoreReturnsCreatedResponse(): void
    {
        $data = [
            'name' => 'Controller User',
            'email' => 'ctrl@example.com',
            'document_number' => '777777',
            'phone' => '77777777',
        ];

        $clientDto = ClientDto::fromArray($data);

        $requestMock = Mockery::mock('App\\Http\\Requests\\tenant\\ClientRequest');
        $requestMock->shouldReceive('toDto')->andReturn($clientDto);

        $createdClient = new \App\Models\Tenant\Client();

        $createAction = Mockery::mock('App\\Actions\\Tenant\\Client\\CreateClientAction');
        $createAction->shouldReceive('__invoke')->andReturn($createdClient);

        $controller = new ClientController();

        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('client', $payload['data']);
    }
}
