<?php

namespace Tests\Unit\Actions\Admin\Client;

use App\Actions\Admin\Client\CreateClientAction;
use App\Dto\Admin\ClientDto;
use App\Exceptions\Admin\ClientAlreadyExistsException;
use App\Models\Admin\Client;
use Illuminate\Support\Facades\Artisan;
use Tests\AdminTestCase;

class CreateClientActionTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testCreatesClientSuccessfully(): void
    {
        $uid = uniqid('', true);
        $dto = ClientDto::fromArray([
            'name' => 'Empresa Teste',
            'email' => "test-{$uid}@empresa.com",
            'document' => $uid,
            'phone' => '11999990000',
        ]);

        $action = new CreateClientAction();
        $client = $action($dto);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertDatabaseHas('clients', [
            'email' => $dto->email,
            'document' => $dto->document,
        ], $this->adminConnection);
    }

    public function testThrowsExceptionWhenDocumentAlreadyExists(): void
    {
        $this->expectException(ClientAlreadyExistsException::class);

        $uid = uniqid('', true);
        Client::query()->create([
            'name' => 'Empresa Existente',
            'email' => "existing-{$uid}@teste.com",
            'document' => $uid,
        ]);

        $dto = ClientDto::fromArray([
            'name' => 'Outra Empresa',
            'email' => "other-{$uid}@teste.com",
            'document' => $uid,
        ]);

        $action = new CreateClientAction();
        $action($dto);
    }
}
