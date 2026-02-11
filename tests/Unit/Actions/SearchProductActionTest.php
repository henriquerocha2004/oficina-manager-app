<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Product\SearchProductAction;
use App\Dto\SearchDto;
use App\Models\Tenant\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class SearchProductActionTest extends TestCase
{
    use DatabaseTransactions;

    public function testSearchesProductsByName(): void
    {
        Product::create([
            'name' => 'Brake Pad Premium',
            'category' => 'brakes',
            'unit' => 'unit',
            'unit_price' => 150.00,
        ]);

        Product::create([
            'name' => 'Oil Filter',
            'category' => 'filters',
            'unit' => 'unit',
            'unit_price' => 25.00,
        ]);

        $searchDto = new SearchDto(search: 'Brake');
        $action = new SearchProductAction;
        $results = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertGreaterThan(0, $results->total());
        $this->assertStringContainsString('Brake', $results->items()[0]->name);
    }

    public function testFiltersProductsByCategory(): void
    {
        Product::create([
            'name' => 'Brake Pad',
            'category' => 'brakes',
            'unit' => 'unit',
            'unit_price' => 150.00,
        ]);

        Product::create([
            'name' => 'Engine Oil',
            'category' => 'fluids',
            'unit' => 'liter',
            'unit_price' => 80.00,
        ]);

        $searchDto = new SearchDto(filters: ['category' => 'brakes']);
        $action = new SearchProductAction;
        $results = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertEquals('brakes', $results->items()[0]->category);
    }

    public function testPaginatesResults(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Product::create([
                'name' => "Product {$i}",
                'category' => 'other',
                'unit' => 'unit',
                'unit_price' => 10.00 * $i,
            ]);
        }

        $searchDto = new SearchDto(per_page: 10);
        $action = new SearchProductAction;
        $results = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $results);
        $this->assertCount(10, $results->items());
        $this->assertEquals(20, $results->total());
    }
}
