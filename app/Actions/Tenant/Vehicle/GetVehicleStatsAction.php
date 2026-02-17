<?php

namespace App\Actions\Tenant\Vehicle;

use App\Models\Tenant\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetVehicleStatsAction
{
    public function __invoke(): array
    {
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        $oneHundredEightyDaysAgo = Carbon::now()->subDays(180);

        $total = Vehicle::query()->count();

        $last90Days = Vehicle::query()
            ->where('created_at', '>=', $ninetyDaysAgo)
            ->count();

        $previous90Days = Vehicle::query()
            ->where('created_at', '>=', $oneHundredEightyDaysAgo)
            ->where('created_at', '<', $ninetyDaysAgo)
            ->count();

        $growth = $last90Days - $previous90Days;
        $growthPercentage = $previous90Days > 0
            ? round(($growth / $previous90Days) * 100, 1)
            : 0;

        $topBrandData = Vehicle::query()
            ->select('brand', DB::raw('count(*) as total'))
            ->groupBy('brand')
            ->orderByDesc('total')
            ->first();

        $topBrand = $topBrandData?->brand;
        $topBrandCount = $topBrandData?->total ?? 0;
        $topBrandPercentage = $total > 0
            ? round(($topBrandCount / $total) * 100, 1)
            : 0;

        return [
            'total' => $total,
            'last_90_days' => $last90Days,
            'previous_90_days' => $previous90Days,
            'growth' => $growth,
            'growth_percentage' => $growthPercentage,
            'top_brand' => $topBrand,
            'top_brand_count' => $topBrandCount,
            'top_brand_percentage' => $topBrandPercentage,
        ];
    }
}
