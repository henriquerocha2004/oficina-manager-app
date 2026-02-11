<?php

namespace App\Actions\Tenant\Product;

use App\Dto\SearchDto;
use App\Models\Tenant\Product;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchProductAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        return $this->applySearchAndFilters(
            query: Product::query(),
            searchDTO: $searchDto,
            searchableColumns: ['name', 'sku', 'barcode', 'manufacturer'],
        );
    }
}
