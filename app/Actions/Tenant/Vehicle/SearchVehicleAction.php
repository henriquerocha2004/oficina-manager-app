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
        $query = Vehicle::query()->with('client:id,name');
        return $this->applySearchAndFilters(
            query: $query,
            searchDTO: $searchDto,
            searchableColumns: ['license_plate', 'brand', 'model', 'vin'],
        );
    }
}
