<?php

namespace Tests\Unit\Actions\Admin\Client;

use App\Actions\Admin\Client\SearchClientAction;
use App\Dto\SearchDto;
use App\Models\Admin\Client;
use Illuminate\Support\Facades\Artisan;
use Tests\AdminTestCase;

class SearchClientActionTest extends AdminTestCase
{
    private string $adminConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminConnection = config('database.connections_names.admin');
    }

    public function testReturnsPagedResults(): void
    {
        Client::factory()->count(5)->create();

        $dto = new SearchDto(per_page: 2);
        $action = new SearchClientAction();
        $result = $action($dto);

        $this->assertSame(2, $result->perPage());
        $this->assertGreaterThanOrEqual(2, $result->total());
    }

    public function testFiltersClientByName(): void
    {
        $uniqueName = 'EmpresaÚnicaXYZ-' . uniqid();
        Client::factory()->create(['name' => $uniqueName]);
        Client::factory()->create(['name' => 'Outra Empresa Normal']);

        $dto = new SearchDto(search: $uniqueName);
        $action = new SearchClientAction();
        $result = $action($dto);

        $this->assertSame(1, $result->total());
        $this->assertSame($uniqueName, $result->items()[0]->name);
    }
}
