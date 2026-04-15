<?php

namespace Tests\Unit\Actions\Admin\Tenant;

use App\Actions\Admin\Tenant\DeleteTenantAction;
use App\Exceptions\Admin\TenantNotFoundException;
use App\Models\Admin\Tenant;
use Tests\AdminTestCase;

class DeleteTenantActionTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testSoftDeletesTenantSuccessfully(): void
    {
        $tenant = Tenant::factory()->create();

        $action = new DeleteTenantAction();
        $action($tenant->id);

        $this->assertSoftDeleted('tenant', ['id' => $tenant->id], $this->adminConnection);
    }

    public function testThrowsExceptionWhenTenantNotFound(): void
    {
        $this->expectException(TenantNotFoundException::class);

        $action = new DeleteTenantAction();
        $action(999999);
    }
}
