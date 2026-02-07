<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use App\Actions\Tenant\Service\SearchServiceAction;
use App\Dto\SearchDto;
use App\Models\Tenant\Service;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchServiceActionTest extends TestCase
{
    public function testSearchesServicesWithoutFilters(): void
    {
        Service::factory()->count(5)->create();

        $searchDto = new SearchDto(
            search: null,
            per_page: 15,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: [],
        );

        $action = new SearchServiceAction();
        $result = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result->items());
    }

    public function testSearchesServicesByName(): void
    {
        Service::factory()->create(['name' => 'Troca de óleo']);
        Service::factory()->create(['name' => 'Alinhamento']);
        Service::factory()->create(['name' => 'Pintura completa']);

        $searchDto = new SearchDto(
            search: 'óleo',
            per_page: 15,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: [],
        );

        $action = new SearchServiceAction();
        $result = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(1, $result->items());
        $this->assertEquals('Troca de óleo', $result->items()[0]->name);
    }

    public function testSearchesServicesWithCategoryFilter(): void
    {
        Service::factory()->create(['category' => Service::CATEGORY_MAINTENANCE]);
        Service::factory()->create(['category' => Service::CATEGORY_MAINTENANCE]);
        Service::factory()->create(['category' => Service::CATEGORY_REPAIR]);

        $searchDto = new SearchDto(
            search: null,
            per_page: 15,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: ['category' => Service::CATEGORY_MAINTENANCE],
        );

        $action = new SearchServiceAction();
        $result = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }

    public function testSearchesServicesPaginated(): void
    {
        Service::factory()->count(20)->create();

        $searchDto = new SearchDto(
            search: null,
            per_page: 10,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: [],
        );

        $action = new SearchServiceAction();
        $result = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }
}
