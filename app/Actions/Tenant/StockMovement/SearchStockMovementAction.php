<?php

namespace App\Actions\Tenant\StockMovement;

use App\Dto\SearchDto;
use App\Models\Tenant\StockMovement;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchStockMovementAction
{
    use QueryBuilderTrait;

    public function __invoke(SearchDto $searchDto): LengthAwarePaginator
    {
        $query = StockMovement::query()
            ->with([
                'product' => function ($q) {
                    $q->withTrashed()->select('id', 'name', 'sku', 'deleted_at');
                },
                'user:id,name',
            ]);

        if ($searchDto->search) {
            $query->whereHas('product', function ($q) use ($searchDto) {
                $q->where('name', 'like', "%{$searchDto->search}%")
                    ->orWhere('sku', 'like', "%{$searchDto->search}%");
            });
        }

        if (! empty($searchDto->filters)) {
            if (! empty($searchDto->filters['product_id'])) {
                $query->where('product_id', '=', $searchDto->filters['product_id']);
            }

            if (! empty($searchDto->filters['movement_type'])) {
                $query->where('movement_type', '=', $searchDto->filters['movement_type']);
            }

            if (! empty($searchDto->filters['reason'])) {
                $query->where('reason', '=', $searchDto->filters['reason']);
            }

            if (! empty($searchDto->filters['date_from'])) {
                $query->whereDate('created_at', '>=', $searchDto->filters['date_from']);
            }

            if (! empty($searchDto->filters['date_to'])) {
                $query->whereDate('created_at', '<=', $searchDto->filters['date_to']);
            }
        }

        $query->orderBy($searchDto->sort_by, $searchDto->sort_direction);

        return $query->paginate($searchDto->per_page);
    }
}
