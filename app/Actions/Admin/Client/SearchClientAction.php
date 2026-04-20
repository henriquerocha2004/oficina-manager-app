<?php

namespace App\Actions\Admin\Client;

use App\Dto\SearchDto;
use App\Models\Admin\Client;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchClientAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        $query = Client::query();

        Log::info('[AdminClients] SearchClientAction query', [
            'model_connection' => $query->getModel()->getConnectionName(),
            'table'            => $query->getModel()->getTable(),
            'raw_sql_preview'  => $query->toSql(),
            'total_in_table'   => DB::connection($query->getModel()->getConnectionName())
                ->table($query->getModel()->getTable())
                ->count(),
        ]);

        return $this->applySearchAndFilters(
            query: $query,
            searchDTO: $searchDto,
            searchableColumns: ['name', 'document', 'email'],
        );
    }
}
