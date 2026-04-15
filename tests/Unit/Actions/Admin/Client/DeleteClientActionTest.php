<?php

namespace Tests\Unit\Actions\Admin\Client;

use App\Actions\Admin\Client\DeleteClientAction;
use App\Exceptions\Admin\ClientNotFoundException;
use App\Models\Admin\Client;
use Tests\AdminTestCase;

class DeleteClientActionTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testSoftDeletesClientSuccessfully(): void
    {
        $client = Client::factory()->create();

        $action = new DeleteClientAction();
        $action($client->id);

        $this->assertSoftDeleted('clients', ['id' => $client->id], $this->adminConnection);
    }

    public function testThrowsExceptionWhenClientNotFound(): void
    {
        $this->expectException(ClientNotFoundException::class);

        $action = new DeleteClientAction();
        $action('01ARZ3NDEKTSV4RRFFQ69G5FAV');
    }
}
