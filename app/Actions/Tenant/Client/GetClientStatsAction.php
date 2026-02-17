<?php

namespace App\Actions\Tenant\Client;

use App\Models\Tenant\Client;
use Illuminate\Support\Carbon;

class GetClientStatsAction
{
    public function __invoke(): array
    {
        $now = Carbon::now();
        $ninetyDaysAgo = $now->copy()->subDays(90);
        $oneEightyDaysAgo = $now->copy()->subDays(180);

        $total = Client::query()->count();

        $last90Days = Client::query()
            ->where('created_at', '>=', $ninetyDaysAgo)
            ->count();

        $previous90Days = Client::query()
            ->where('created_at', '>=', $oneEightyDaysAgo)
            ->where('created_at', '<', $ninetyDaysAgo)
            ->count();

        $growth = $last90Days - $previous90Days;
        $growthPercentage = $previous90Days > 0
            ? round(($growth / $previous90Days) * 100, 1)
            : 0;

        $topCity = Client::query()
            ->select('city')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->first();

        $topCityName = $topCity?->city ?? 'N/A';
        $topCityCount = $topCity?->count ?? 0;
        $topCityPercentage = $total > 0
            ? round(($topCityCount / $total) * 100, 1)
            : 0;

        return [
            'total' => $total,
            'last_90_days' => $last90Days,
            'previous_90_days' => $previous90Days,
            'growth' => $growth,
            'growth_percentage' => $growthPercentage,
            'top_city' => $topCityName,
            'top_city_count' => $topCityCount,
            'top_city_percentage' => $topCityPercentage,
        ];
    }
}
