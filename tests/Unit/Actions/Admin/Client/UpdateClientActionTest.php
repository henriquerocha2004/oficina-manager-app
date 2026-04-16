<?php

namespace Tests\Unit\Actions\Admin\Client;

use App\Actions\Admin\Client\UpdateClientAction;
use App\Dto\Admin\ClientDto;
use App\Exceptions\Admin\ClientNotFoundException;
use App\Models\Admin\Client;
use Tests\AdminTestCase;

class UpdateClientActionTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testUpdatesClientSuccessfully(): void
    {
        $client = Client::factory()->create(['name' => 'Nome Antigo']);

        $dto = ClientDto::fromArray([
            'name' => 'Nome Novo',
            'email' => $client->email,
            'document' => $client->document,
        ]);

        $action = new UpdateClientAction();
        $action($dto, $client->id);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Nome Novo',
        ], $this->adminConnection);
    }

    public function testThrowsExceptionWhenClientNotFound(): void
    {
        $this->expectException(ClientNotFoundException::class);

        $dto = ClientDto::fromArray([
            'name' => 'Qualquer',
            'email' => 'qualquer@teste.com',
            'document' => '00000000000000',
        ]);

        $action = new UpdateClientAction();
        $action($dto, '01ARZ3NDEKTSV4RRFFQ69G5FAV');
    }
}
