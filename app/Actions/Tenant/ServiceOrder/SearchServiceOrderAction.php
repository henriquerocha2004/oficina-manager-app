<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Dto\ServiceOrderSearchDto;
use App\Models\Tenant\ServiceOrder;
use App\Traits\QueryBuilderTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchServiceOrderAction
{
    use QueryBuilderTrait;

    public function __invoke(ServiceOrderSearchDto $searchDto): LengthAwarePaginator
    {
        $query = ServiceOrder::query()
            ->with(['client', 'vehicle', 'technician']);

        if ($searchDto->status !== null) {
            $query->where('status', $searchDto->status);
        }

        if ($searchDto->client_id !== null) {
            $query->where('client_id', $searchDto->client_id);
        }

        if ($searchDto->vehicle_id !== null) {
            $query->where('vehicle_id', $searchDto->vehicle_id);
        }

        if ($searchDto->technician_id !== null) {
            $query->where('technician_id', $searchDto->technician_id);
        }

        if ($searchDto->order_number !== null) {
            $query->where('order_number', 'like', '%'.$searchDto->order_number.'%');
        }

        if ($searchDto->date_from !== null) {
            $query->whereDate('created_at', '>=', $searchDto->date_from);
        }

        if ($searchDto->date_to !== null) {
            $query->whereDate('created_at', '<=', $searchDto->date_to);
        }

        if ($searchDto->search !== null) {
            $query->where(function ($q) use ($searchDto) {
                $q->where('order_number', 'like', '%'.$searchDto->search.'%')
                    ->orWhere('diagnosis', 'like', '%'.$searchDto->search.'%');
            });
        }

        return $query
            ->orderBy($searchDto->sort_by, $searchDto->sort_direction)
            ->paginate($searchDto->per_page);
    }
}
