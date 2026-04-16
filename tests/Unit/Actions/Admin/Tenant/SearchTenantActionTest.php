<?php

namespace Tests\Unit\Actions\Admin\Tenant;

use App\Actions\Admin\Tenant\SearchTenantAction;
use App\Dto\SearchDto;
use App\Models\Admin\Tenant;
use Tests\AdminTestCase;

class SearchTenantActionTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testReturnsPaginatedTenants(): void
    {
        Tenant::factory()->count(5)->create();

        $dto = new SearchDto(per_page: 2);
        $action = new SearchTenantAction();
        $result = $action($dto);

        $this->assertSame(2, $result->perPage());
        $this->assertGreaterThanOrEqual(2, $result->total());
    }

    public function testFiltersByStatus(): void
    {
        Tenant::factory()->create(['status' => 'active', 'name' => 'Ativo Teste']);
        Tenant::factory()->inactive()->create(['name' => 'Inativo Teste']);

        $dto = new SearchDto(filters: ['status' => 'inactive']);
        $action = new SearchTenantAction();
        $result = $action($dto);

        foreach ($result->items() as $item) {
            $this->assertSame('inactive', $item->status);
        }
    }
}
