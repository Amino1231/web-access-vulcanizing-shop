<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OwnerDashboardMetrics
{
    public function __construct(public readonly int $tenantId) {}

    // ─────────────────────────────────────────
    // KPIs — one query per table, aggregated in SQL
    // ─────────────────────────────────────────

    public function kpis(): array
    {
        // ─── Order counts ─────────────────────
        $orderCounts = Order::query()
            ->where('tenant_id', $this->tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalOrders = array_sum($orderCounts);
        $pending     = (int) ($orderCounts[OrderStatus::Pending->value] ?? 0);

        // ─── Product aggregates ───────────────
        $productStats = Product::query()
            ->where('tenant_id', $this->tenantId)
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(stock), 0) as stock_sum,
                SUM(CASE WHEN stock > 0 AND stock <= low_stock_alert THEN 1 ELSE 0 END) as low_stock
            ')
            ->first();

        $totalProducts = (int) ($productStats->total ?? 0);
        $lowStock      = (int) ($productStats->low_stock ?? 0);

        // ─── Revenue aggregation (this month) ─
        $monthStart = now()->copy()->startOfMonth();

        $revenueRow = Sale::query()
            ->where('tenant_id', $this->tenantId)
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', $monthStart)
            ->selectRaw('
                COALESCE(SUM(final_amount), 0) as gross,
                COALESCE(SUM(platform_commission), 0) as commission,
                COALESCE(SUM(shop_net_revenue), 0) as net
            ')
            ->first();

        $grossThisMonth      = (float) ($revenueRow->gross ?? 0);
        $commissionThisMonth = (float) ($revenueRow->commission ?? 0);
        $netThisMonth        = (float) ($revenueRow->net ?? 0);

        return [
            [
                'label' => 'Gross Sales (mo.)',
                'value' => '₱' . number_format($grossThisMonth, 2),
                'delta' => 'Before fees',
                'tone'  => 'blue',
            ],
            [
                'label' => 'Platform Fee (mo.)',
                'value' => '₱' . number_format($commissionThisMonth, 2),
                'delta' => 'Platform cut',
                'tone'  => 'orange',
            ],
            [
                'label' => 'Net Revenue (mo.)',
                'value' => '₱' . number_format($netThisMonth, 2),
                'delta' => 'You keep',
                'tone'  => 'emerald',
            ],
            [
                'label' => 'Orders',
                'value' => (string) $totalOrders,
                'delta' => $pending . ' pending',
                'tone'  => 'orange',
            ],
        ];
    }

    // ─────────────────────────────────────────
    // Revenue chart — last 6 months
    // ─────────────────────────────────────────

    public function revenueChart(): array
    {
        $firstMonth = now()->subMonths(5)->startOfMonth();

        $rows = Sale::query()
            ->where('tenant_id', $this->tenantId)
            ->where('created_at', '>=', $firstMonth)
            ->whereIn('status', ['paid', 'completed'])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(final_amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        $labels = [];
        $series = [];

        for ($i = 5; $i >= 0; $i--) {
            $month    = now()->subMonths($i);
            $key      = $month->format('Y-m');
            $labels[] = $month->format('M');
            $series[] = round((float) ($rows[$key] ?? 0), 2);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    // ─────────────────────────────────────────
    // Order status donut
    // ─────────────────────────────────────────

    public function orderDonut(): array
    {
        $counts = Order::query()
            ->where('tenant_id', $this->tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return collect(OrderStatus::cases())
            ->map(fn (OrderStatus $s) => [
                'label' => $s->label(),
                'value' => (int) ($counts[$s->value] ?? 0),
                'color' => match ($s) {
                    OrderStatus::Pending    => '#f59e0b',
                    OrderStatus::Processing => '#3b82f6',
                    OrderStatus::Ready      => '#6366f1',
                    OrderStatus::Completed  => '#10b981',
                    OrderStatus::Cancelled  => '#ef4444',
                },
            ])
            ->all();
    }

    // ─────────────────────────────────────────
    // Recent orders
    // ─────────────────────────────────────────

    public function recentOrders(int $limit = 8): Collection
    {
        return Order::query()
            ->where('tenant_id', $this->tenantId)
            ->with(['customer:id,name', 'sale:id,invoice_number'])
            ->select([
                'id', 'tenant_id', 'customer_id', 'order_number',
                'status', 'total', 'sale_id', 'created_at',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ─────────────────────────────────────────
    // Reviews
    // ─────────────────────────────────────────

    public function recentReviews(int $limit = 5): Collection
    {
        return Review::query()
            ->where('tenant_id', $this->tenantId)
            ->with([
                'customer:id,name',
                'service:id,name',
                'employee:id,name',
                'sale:id,invoice_number',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function reviewSummary(): array
    {
        $row = Review::query()
            ->where('tenant_id', $this->tenantId)
            ->selectRaw('COUNT(*) as count, COALESCE(AVG(rating), 0) as avg')
            ->first();

        $count = (int) ($row->count ?? 0);
        $avg   = $count > 0 ? round((float) $row->avg, 1) : 0.0;

        return ['count' => $count, 'average' => $avg];
    }
}