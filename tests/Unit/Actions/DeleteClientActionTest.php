<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Dto\ClientDto;
use App\Models\Tenant\Client;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Client\CreateClientAction;
use App\Actions\Tenant\Client\DeleteClientAction;
use App\Exceptions\Client\ClientNotFoundException;

class DeleteClientActionTest extends TestCase
{
    public function testDeletesClientWhenFound(): void
    {
        $data = [
            'name' => 'To Delete',
            'email' => 'del@example.com',
            'document_number' => '222222',
            'phone' => '22222222',
        ];

        $createAction = new CreateClientAction();
        $client = $createAction(ClientDto::fromArray($data));

        $this->assertDatabaseHas('client', ['document_number' => $data['document_number']]);

        $ulid = Ulid::fromString($client->id);

        $action = new DeleteClientAction();
        $action($ulid);

        $this->assertSoftDeleted('client', ['id' => $client->id]);
    }

    public function testThrowsWhenNotFound(): void
    {
        $this->expectException(ClientNotFoundException::class);

        $ulid = Ulid::fromString((string) Ulid::generate());

        $action = new DeleteClientAction();
        $action($ulid);
    }
}
