<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Enum\Tenant\ServiceOrder\ServiceOrderStatusEnum;
use App\Models\Tenant\ServiceOrder;
use Illuminate\Support\Facades\DB;

class GetServiceOrderStatsAction
{
    public function __invoke(): array
    {
        $stats = ServiceOrder::query()
            ->select([
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft'),
                DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as waiting_approval'),
                DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress'),
                DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as waiting_payment'),
                DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('SUM(outstanding_balance) as total_outstanding'),
            ])
            ->addBinding(ServiceOrderStatusEnum::DRAFT->value)
            ->addBinding(ServiceOrderStatusEnum::WAITING_APPROVAL->value)
            ->addBinding(ServiceOrderStatusEnum::APPROVED->value)
            ->addBinding(ServiceOrderStatusEnum::IN_PROGRESS->value)
            ->addBinding(ServiceOrderStatusEnum::WAITING_PAYMENT->value)
            ->addBinding(ServiceOrderStatusEnum::COMPLETED->value)
            ->addBinding(ServiceOrderStatusEnum::CANCELLED->value)
            ->first();

        $lastMonthTotal = ServiceOrder::query()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $currentMonthTotal = ServiceOrder::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $growthPercentage = $lastMonthTotal > 0
            ? round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 2)
            : 0;

        return [
            'total' => (int) $stats->total,
            'by_status' => [
                'draft' => (int) $stats->draft,
                'waiting_approval' => (int) $stats->waiting_approval,
                'approved' => (int) $stats->approved,
                'in_progress' => (int) $stats->in_progress,
                'waiting_payment' => (int) $stats->waiting_payment,
                'completed' => (int) $stats->completed,
                'cancelled' => (int) $stats->cancelled,
            ],
            'total_revenue' => (float) ($stats->total_revenue ?? 0),
            'total_paid' => (float) ($stats->total_paid ?? 0),
            'total_outstanding' => (float) ($stats->total_outstanding ?? 0),
            'growth_percentage' => $growthPercentage,
        ];
    }
}
