<?php

namespace Tests\Unit\Actions;

use App\Actions\Tenant\Supplier\CreateSupplierAction;
use App\Actions\Tenant\Supplier\SearchSupplierAction;
use App\Dto\SearchDto;
use App\Dto\SupplierDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use Throwable;

class SearchSupplierActionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testSearchReturnsSuppliers(): void
    {
        $createAction = new CreateSupplierAction;

        $createAction(SupplierDto::fromArray([
            'name' => 'Fornecedor Alpha Ltda',
            'document_number' => '44444444000144',
            'email' => 'alpha@fornecedor.com',
            'phone' => '1111111111',
        ]));

        $createAction(SupplierDto::fromArray([
            'name' => 'Fornecedor Beta Ltda',
            'document_number' => '55555555000155',
            'email' => 'beta@fornecedor.com',
            'phone' => '2222222222',
        ]));

        $searchDto = new SearchDto(
            search: null,
            per_page: 15,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: []
        );

        $action = new SearchSupplierAction;
        $result = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(2, $result->total());
    }

    /**
     * @throws Throwable
     */
    public function testSearchWithFilters(): void
    {
        $createAction = new CreateSupplierAction;

        $createAction(SupplierDto::fromArray([
            'name' => 'Fornecedor Gamma Ltda',
            'document_number' => '66666666000166',
            'email' => 'gamma@fornecedor.com',
            'phone' => '3333333333',
        ]));

        $searchDto = new SearchDto(
            search: 'Gamma',
            per_page: 15,
            sort_by: 'created_at',
            sort_direction: 'desc',
            filters: []
        );

        $action = new SearchSupplierAction;
        $result = $action($searchDto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(1, $result->total());
    }
}
