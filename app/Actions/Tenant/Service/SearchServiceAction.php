<?php

namespace App\Actions\Tenant\Service;

use App\Dto\SearchDto;
use App\Models\Tenant\Service;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchServiceAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        return $this->applySearchAndFilters(
            query: Service::query(),
            searchDTO: $searchDto,
            searchableColumns: ['name', 'description'],
        );
    }
}
