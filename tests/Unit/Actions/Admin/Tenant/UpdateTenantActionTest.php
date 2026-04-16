<?php

namespace Tests\Unit\Actions\Admin\Tenant;

use App\Actions\Admin\Tenant\UpdateTenantAction;
use App\Dto\Admin\TenantUpdateDto;
use App\Exceptions\Admin\TenantNotFoundException;
use App\Models\Admin\Tenant;
use Tests\AdminTestCase;

class UpdateTenantActionTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testUpdatesTenantStatusSuccessfully(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);

        $dto = new TenantUpdateDto(
            name: $tenant->name,
            email: $tenant->email,
            domain: $tenant->domain,
            status: 'inactive',
        );

        $action = new UpdateTenantAction();
        $action($dto, $tenant->id);

        $this->assertDatabaseHas('tenant', [
            'id' => $tenant->id,
            'status' => 'inactive',
        ], $this->adminConnection);
    }

    public function testUpdatesTenantClientIdSuccessfully(): void
    {
        $tenant = Tenant::factory()->create();
        $client = \App\Models\Admin\Client::factory()->create();

        $dto = new TenantUpdateDto(
            name: $tenant->name,
            email: $tenant->email,
            domain: $tenant->domain,
            status: $tenant->status,
            client_id: $client->id,
        );

        $action = new UpdateTenantAction();
        $action($dto, $tenant->id);

        $this->assertDatabaseHas('tenant', [
            'id' => $tenant->id,
            'client_id' => $client->id,
        ], $this->adminConnection);
    }

    public function testThrowsExceptionWhenTenantNotFound(): void
    {
        $this->expectException(TenantNotFoundException::class);

        $dto = new TenantUpdateDto(
            name: 'Qualquer',
            email: 'qualquer@teste.com',
            domain: 'qualquer',
            status: 'active',
        );

        $action = new UpdateTenantAction();
        $action($dto, 999999);
    }
}
