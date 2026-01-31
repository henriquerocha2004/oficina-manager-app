<?php

namespace App\Services\Traits;

use App\Dto\SearchDto as DtoSearchDto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait QueryBuilderTrait
{
    protected function applySearchAndFilters(
        Builder $query,
        DtoSearchDto $searchDTO,
        array $searchableColumns = []
    ): LengthAwarePaginator {
        if ($searchDTO->search && !empty($searchableColumns)) {
            $query->where(function ($q) use ($searchDTO, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$searchDTO->search}%");
                }
            });
        }

        if (!empty($searchDTO->filters)) {
            foreach ($searchDTO->filters as $column => $value) {
                if (!empty($value)) {
                    $query->where($column, '=', $value);
                }
            }
        }

        $query->orderBy($searchDTO->sort_by, $searchDTO->sort_direction);

        return $query->paginate($searchDTO->per_page);
    }
}
