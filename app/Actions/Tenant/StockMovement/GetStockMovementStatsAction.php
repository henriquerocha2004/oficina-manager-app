<?php

namespace App\Actions\Tenant\StockMovement;

use App\Models\Tenant\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetStockMovementStatsAction
{
    public function __invoke(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Total de movimentações do mês atual
        $currentMonthTotal = StockMovement::query()
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->count();

        // Total de movimentações do mês anterior
        $lastMonthTotal = StockMovement::query()
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        // Total de entradas do mês atual
        $currentMonthIn = StockMovement::query()
            ->where('movement_type', 'IN')
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->sum('quantity');

        // Total de entradas do mês anterior
        $lastMonthIn = StockMovement::query()
            ->where('movement_type', 'IN')
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('quantity');

        // Total de saídas do mês atual
        $currentMonthOut = StockMovement::query()
            ->where('movement_type', 'OUT')
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->sum('quantity');

        // Total de saídas do mês anterior
        $lastMonthOut = StockMovement::query()
            ->where('movement_type', 'OUT')
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('quantity');

        // Motivo mais comum do mês atual
        $mostCommonReason = StockMovement::query()
            ->select('reason', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->groupBy('reason')
            ->orderByDesc('count')
            ->first();

        // Calcular percentuais de crescimento
        $totalGrowth = $this->calculateGrowth($lastMonthTotal, $currentMonthTotal);
        $inGrowth = $this->calculateGrowth($lastMonthIn, $currentMonthIn);
        $outGrowth = $this->calculateGrowth($lastMonthOut, $currentMonthOut);

        return [
            'total_movements' => [
                'current' => $currentMonthTotal,
                'previous' => $lastMonthTotal,
                'growth' => $totalGrowth,
            ],
            'total_in' => [
                'current' => $currentMonthIn,
                'previous' => $lastMonthIn,
                'growth' => $inGrowth,
            ],
            'total_out' => [
                'current' => $currentMonthOut,
                'previous' => $lastMonthOut,
                'growth' => $outGrowth,
            ],
            'most_common_reason' => $mostCommonReason?->reason ?? 'N/A',
        ];
    }

    private function calculateGrowth(?int $previous, int $current): float
    {
        if ($previous === 0 || $previous === null) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
