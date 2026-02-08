<?php

namespace App\Actions\Tenant\Supplier;

use App\Dto\SearchDto;
use App\Models\Tenant\Supplier;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchSupplierAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        return $this->applySearchAndFilters(
            query: Supplier::query(),
            searchDTO: $searchDto,
            searchableColumns: ['name', 'trade_name', 'document_number', 'email'],
        );
    }
}
