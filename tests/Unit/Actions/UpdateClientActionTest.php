<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Dto\ClientDto;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Client\UpdateClientAction;
use App\Exceptions\Client\ClientNotFoundException;

class UpdateClientActionTest extends TestCase
{
    public function testUpdatesClientWhenFound(): void
    {
        $data = [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'document_number' => '654321',
            'phone' => '88888888',
        ];

        $createAction = new \App\Actions\Tenant\Client\CreateClientAction();
        $client = $createAction(\App\Dto\ClientDto::fromArray($data));

        $this->assertDatabaseHas('client', ['document_number' => $data['document_number']]);

        $clientDto = ClientDto::fromArray([
            'name' => 'Jane Updated',
            'email' => 'jane@example.com',
            'document_number' => '654321',
            'phone' => '88888888',
        ]);

        $ulid = Ulid::fromString($client->id);

        $action = new UpdateClientAction();
        $action($clientDto, $ulid);

        $this->assertDatabaseHas('client', ['id' => $client->id, 'name' => 'Jane Updated']);
    }

    public function testThrowsWhenNotFound(): void
    {
        $clientDto = ClientDto::fromArray([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'document_number' => '654321',
            'phone' => '88888888',
        ]);

        $this->expectException(ClientNotFoundException::class);

        $ulid = Ulid::fromString((string) Ulid::generate());

        $action = new UpdateClientAction();
        $action($clientDto, $ulid);
    }
}
