<?php

namespace App\Actions\Tenant\Vehicle;

use App\Models\Tenant\ServiceOrder;

class GetVehicleHistoryStatsAction
{
    public function __invoke(string $vehicleId): array
    {
        $total = ServiceOrder::where('vehicle_id', $vehicleId)->count();

        $lastVisit = ServiceOrder::where('vehicle_id', $vehicleId)
            ->max('created_at');

        $recurring = ServiceOrder::where('vehicle_id', $vehicleId)
            ->whereNotNull('reported_problem')
            ->selectRaw('reported_problem, COUNT(*) as cnt')
            ->groupBy('reported_problem')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return [
            'total_orders' => $total,
            'last_visit' => $lastVisit,
            'recurring_problems' => $recurring,
        ];
    }
}
