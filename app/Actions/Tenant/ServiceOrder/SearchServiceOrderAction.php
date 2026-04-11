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

        if ($searchDto->client_name !== null) {
            $term = '%' . $searchDto->client_name . '%';
            $query->whereHas('client', function ($q) use ($term) {
                $q->whereRaw('name ilike ?', [$term]);
            });
        }

        if ($searchDto->vehicle_id !== null) {
            $query->where('vehicle_id', $searchDto->vehicle_id);
        }

        if ($searchDto->plate !== null) {
            $term = '%' . $searchDto->plate . '%';
            $query->whereHas('vehicle', function ($q) use ($term) {
                $q->whereRaw('license_plate ilike ?', [$term]);
            });
        }

        if ($searchDto->technician_id !== null) {
            $query->where('technician_id', $searchDto->technician_id);
        }

        if ($searchDto->order_number !== null) {
            $term = '%' . $searchDto->order_number . '%';
            $query->whereRaw('order_number::text ilike ?', [$term]);
        }

        if ($searchDto->date_from !== null) {
            $query->whereDate('created_at', '>=', $searchDto->date_from);
        }

        if ($searchDto->date_to !== null) {
            $query->whereDate('created_at', '<=', $searchDto->date_to);
        }

        if ($searchDto->search !== null) {
            $term = '%' . $searchDto->search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('order_number::text ilike ?', [$term])
                    ->orWhereRaw('reported_problem ilike ?', [$term])
                    ->orWhereRaw('technical_diagnosis ilike ?', [$term]);
            });
        }

        return $query
            ->orderBy($searchDto->sort_by, $searchDto->sort_direction)
            ->paginate($searchDto->per_page);
    }
}
