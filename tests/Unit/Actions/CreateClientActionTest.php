<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Actions\Tenant\Client\CreateClientAction;
use App\Dto\ClientDto;
use App\Exceptions\Client\ClientAlreadyExistsException;
use App\Models\Tenant\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateClientActionTest extends TestCase
{
    public function testCreatesClientWhenNotExists(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'document_number' => '123456',
            'phone' => '99999999',
        ];

        $clientDto = ClientDto::fromArray($data);

        $action = new CreateClientAction();
        $result = $action($clientDto);

        $this->assertInstanceOf(Client::class, $result);
        $this->assertDatabaseHas('client', ['document_number' => $data['document_number']]);
    }

    public function testThrowsWhenClientAlreadyExists(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'document_number' => '123456',
            'phone' => '99999999',
        ];

        Client::create($data);

        $clientDto = ClientDto::fromArray($data);

        $this->expectException(ClientAlreadyExistsException::class);

        (new CreateClientAction())($clientDto);
    }
}
