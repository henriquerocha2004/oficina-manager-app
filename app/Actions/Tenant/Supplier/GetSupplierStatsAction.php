<?php

namespace App\Actions\Tenant\Supplier;

use App\Models\Tenant\Supplier;
use Illuminate\Support\Carbon;

class GetSupplierStatsAction
{
    /**
     * Calculates supplier statistics.
     */
    public function __invoke(): array
    {
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        $oneHundredEightyDaysAgo = Carbon::now()->subDays(180);

        $total = Supplier::query()->count();

        $last90Days = Supplier::query()
            ->where('created_at', '>=', $ninetyDaysAgo)
            ->count();

        $previous90Days = Supplier::query()
            ->where('created_at', '>=', $oneHundredEightyDaysAgo)
            ->where('created_at', '<', $ninetyDaysAgo)
            ->count();

        $activeSuppliers = Supplier::query()
            ->where('is_active', true)
            ->count();

        $topState = Supplier::query()
            ->select('state')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('state')
            ->groupBy('state')
            ->orderByDesc('count')
            ->first();

        $growth = $last90Days - $previous90Days;
        $growthPercentage = $previous90Days > 0
            ? round(($growth / $previous90Days) * 100, 1)
            : 0;

        $topStateCount = $topState?->count ?? 0;
        $topStatePercentage = $total > 0
            ? round(($topStateCount / $total) * 100, 1)
            : 0;

        $activePercentage = $total > 0
            ? round(($activeSuppliers / $total) * 100, 1)
            : 0;

        return [
            'total' => $total,
            'last_90_days' => $last90Days,
            'previous_90_days' => $previous90Days,
            'active_suppliers' => $activeSuppliers,
            'active_percentage' => $activePercentage,
            'growth' => $growth,
            'growth_percentage' => $growthPercentage,
            'top_state' => $topState?->state ?? null,
            'top_state_count' => $topStateCount,
            'top_state_percentage' => $topStatePercentage,
        ];
    }
}
