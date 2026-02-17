<?php

namespace App\Actions\Tenant\Service;

use App\Models\Tenant\Service;
use Illuminate\Support\Carbon;

class GetServiceStatsAction
{
    public function __invoke(): array
    {
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        $oneHundredEightyDaysAgo = Carbon::now()->subDays(180);

        $total = Service::query()->count();

        $activeServices = Service::query()
            ->where('is_active', true)
            ->count();

        $averagePrice = Service::query()
            ->avg('base_price');

        $last90Days = Service::query()
            ->where('created_at', '>=', $ninetyDaysAgo)
            ->count();

        $previous90Days = Service::query()
            ->where('created_at', '>=', $oneHundredEightyDaysAgo)
            ->where('created_at', '<', $ninetyDaysAgo)
            ->count();

        $growth = $last90Days - $previous90Days;
        $growthPercentage = $previous90Days > 0
            ? round(($growth / $previous90Days) * 100, 1)
            : 0;

        return [
            'total' => $total,
            'active_services' => $activeServices,
            'average_price' => $averagePrice ? round((float) $averagePrice, 2) : 0,
            'last_90_days' => $last90Days,
            'previous_90_days' => $previous90Days,
            'growth' => $growth,
            'growth_percentage' => $growthPercentage,
        ];
    }
}
