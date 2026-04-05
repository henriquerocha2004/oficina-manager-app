<?php

namespace App\Actions\Tenant\Vehicle;

use App\Dto\SearchDto;
use App\Models\Tenant\Vehicle;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchVehicleAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        $query = Vehicle::query()->with('currentOwner.client:id,name');

        if (!empty($searchDto->filters['client_id'])) {
            $query->whereHas('currentOwner', function ($q) use ($searchDto) {
                $q->where('client_id', $searchDto->filters['client_id'])
                  ->where('current_owner', true);
            });

            unset($searchDto->filters['client_id']);
        }

        return $this->applySearchAndFilters(
            query: $query,
            searchDTO: $searchDto,
            searchableColumns: ['license_plate', 'brand', 'model', 'vin'],
        );
    }
}
