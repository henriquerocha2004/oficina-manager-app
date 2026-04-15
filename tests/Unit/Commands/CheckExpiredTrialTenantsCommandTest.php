<?php

namespace Tests\Unit\Commands;

use App\Models\Admin\Tenant;
use Illuminate\Support\Facades\Artisan;
use Tests\AdminTestCase;

class CheckExpiredTrialTenantsCommandTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testSetsInactiveWhenTrialExpired(): void
    {
        $tenant = Tenant::factory()->expiredTrial()->create();

        Artisan::call('app:check-expired-trial-tenants');

        $this->assertDatabaseHas('tenant', [
            'id' => $tenant->id,
            'status' => 'inactive',
        ], $this->adminConnection);
    }

    public function testDoesNotChangeNonExpiredTrial(): void
    {
        $tenant = Tenant::factory()->trial()->create();

        Artisan::call('app:check-expired-trial-tenants');

        $this->assertDatabaseHas('tenant', [
            'id' => $tenant->id,
            'status' => 'trial',
        ], $this->adminConnection);
    }

    public function testIgnoresNonTrialTenants(): void
    {
        $active = Tenant::factory()->create(['status' => 'active']);
        $inactive = Tenant::factory()->inactive()->create();

        Artisan::call('app:check-expired-trial-tenants');

        $this->assertDatabaseHas('tenant', ['id' => $active->id, 'status' => 'active'], $this->adminConnection);
        $this->assertDatabaseHas('tenant', ['id' => $inactive->id, 'status' => 'inactive'], $this->adminConnection);
    }
}
