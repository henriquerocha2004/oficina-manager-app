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
        return $this->applySearchAndFilters(
            query: Vehicle::query(),
            searchDTO: $searchDto,
            searchableColumns: ['license_plate', 'brand', 'model', 'vin'],
        );
    }
}
