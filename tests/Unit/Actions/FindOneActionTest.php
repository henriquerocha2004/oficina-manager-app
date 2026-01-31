<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Dto\ClientDto;
use App\Models\Tenant\Client;
use Symfony\Component\Uid\Ulid;
use App\Actions\Tenant\Client\FindOneAction;
use App\Actions\Tenant\Client\CreateClientAction;
use App\Exceptions\Client\ClientNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FindOneActionTest extends TestCase
{
    public function testReturnsClientWhenFound(): void
    {
        $data = [
            'name' => 'Find Me',
            'email' => 'find@example.com',
            'document_number' => '999999',
            'phone' => '11111111',
        ];

        $createAction = new CreateClientAction();
        $client = $createAction(ClientDto::fromArray($data));

        $this->assertDatabaseHas('client', ['document_number' => $data['document_number']]);

        $ulid = Ulid::fromString($client->id);

        $action = new FindOneAction();
        $result = $action($ulid);

        $this->assertSame($client->id, $result->id);
    }

    public function testThrowsWhenNotFound(): void
    {
        $this->expectException(ClientNotFoundException::class);

        $ulid = Ulid::fromString((string) Ulid::generate());

        $action = new FindOneAction();
        $action($ulid);
    }
}
