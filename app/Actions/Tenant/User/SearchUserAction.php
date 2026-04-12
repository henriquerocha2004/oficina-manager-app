<?php

namespace App\Actions\Tenant\User;

use App\Dto\SearchDto;
use App\Models\Tenant\User;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchUserAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto, ?int $excludedUserId = null): LengthAwarePaginator
    {
        $query = User::query()->whereNull('deleted_at');

        if (!is_null($excludedUserId)) {
            $query->where('id', '!=', $excludedUserId);
        }

        return $this->applySearchAndFilters(
            query: $query,
            searchDTO: $searchDto,
            searchableColumns: ['name', 'email'],
        );
    }
}
