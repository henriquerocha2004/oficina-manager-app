<?php

namespace App\Actions\Tenant\Product;

use App\Models\Tenant\Product;
use Illuminate\Support\Carbon;

class GetProductStatsAction
{
    /**
     * Calculates product statistics.
     */
    public function __invoke(): array
    {
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        $oneHundredEightyDaysAgo = Carbon::now()->subDays(180);

        $total = Product::query()->count();

        $last90Days = Product::query()
            ->where('created_at', '>=', $ninetyDaysAgo)
            ->count();

        $previous90Days = Product::query()
            ->where('created_at', '>=', $oneHundredEightyDaysAgo)
            ->where('created_at', '<', $ninetyDaysAgo)
            ->count();

        $activeProducts = Product::query()
            ->where('is_active', true)
            ->count();

        $totalValue = Product::query()
            ->sum('unit_price');

        $topCategory = Product::query()
            ->select('category')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('count')
            ->first();

        $growth = $last90Days - $previous90Days;
        $growthPercentage = $previous90Days > 0
            ? round(($growth / $previous90Days) * 100, 1)
            : 0;

        $topCategoryCount = $topCategory?->count ?? 0;
        $topCategoryPercentage = $total > 0
            ? round(($topCategoryCount / $total) * 100, 1)
            : 0;

        $activePercentage = $total > 0
            ? round(($activeProducts / $total) * 100, 1)
            : 0;

        return [
            'total' => $total,
            'last_90_days' => $last90Days,
            'previous_90_days' => $previous90Days,
            'active_products' => $activeProducts,
            'active_percentage' => $activePercentage,
            'total_value' => round($totalValue, 2),
            'growth' => $growth,
            'growth_percentage' => $growthPercentage,
            'top_category' => $topCategory?->category ?? null,
            'top_category_count' => $topCategoryCount,
            'top_category_percentage' => $topCategoryPercentage,
        ];
    }
}
