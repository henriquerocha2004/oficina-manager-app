<?php

namespace App\Actions\Admin\Client;

use App\Dto\SearchDto;
use App\Models\Admin\Client;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchClientAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        return $this->applySearchAndFilters(
            query: Client::query(),
            searchDTO: $searchDto,
            searchableColumns: ['name', 'document', 'email'],
        );
    }
}
