<?php

namespace Tests\Unit\Controllers;

use App\Actions\Tenant\Client\CreateClientAction;
use App\Dto\ClientDto;
use App\Http\Controllers\tenant\ClientController;
use App\Http\Requests\tenant\ClientRequest;
use App\Models\Tenant\Client;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

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

        /** @var ClientRequest&MockInterface $requestMock */
        $requestMock = Mockery::mock(ClientRequest::class);
        $requestMock->shouldReceive('toDto')->andReturn($clientDto);

        $createdClient = new Client;

        /** @var CreateClientAction&MockInterface $createAction */
        $createAction = Mockery::mock(CreateClientAction::class);
        $createAction->shouldReceive('__invoke')->andReturn($createdClient);

        $controller = new ClientController;

        $response = $controller->store($requestMock, $createAction);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('client', $payload['data']);
    }
}
