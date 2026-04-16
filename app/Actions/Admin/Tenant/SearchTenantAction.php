<?php

namespace App\Actions\Admin\Tenant;

use App\Dto\SearchDto;
use App\Models\Admin\Tenant;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchTenantAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        return $this->applySearchAndFilters(
            query: Tenant::query()->with('client'),
            searchDTO: $searchDto,
            searchableColumns: ['name', 'email', 'domain'],
        );
    }
}
