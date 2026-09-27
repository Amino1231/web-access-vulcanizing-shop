<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->tenant?->id;
    }

    #[Computed]
    public function kpis(): array
    {
        if (! $this->tenantId) return [];

        $revenue  = (float) Sale::where('tenant_id', $this->tenantId)->sum('final_amount');
        $orders   = Order::where('tenant_id', $this->tenantId)->count();
        $pending  = Order::where('tenant_id', $this->tenantId)->where('status', OrderStatus::Pending->value)->count();
        $products = Product::where('tenant_id', $this->tenantId)->count();
        $lowStock = Product::where('tenant_id', $this->tenantId)
            ->whereColumn('stock', '<=', 'low_stock_alert')
            ->where('stock', '>', 0)
            ->count();

        return [
            ['label' => 'Revenue',   'value' => '₱' . number_format($revenue, 2), 'delta' => 'All time', 'tone' => 'emerald'],
            ['label' => 'Orders',    'value' => (string) $orders,                 'delta' => $pending . ' pending', 'tone' => 'orange'],
            ['label' => 'Products',  'value' => (string) $products,               'delta' => $lowStock . ' low',    'tone' => 'blue'],
            ['label' => 'Low stock', 'value' => (string) $lowStock,               'delta' => 'Attention',           'tone' => 'red'],
        ];
    }

    #[Computed]
    public function revenueChart(): array
    {
        if (! $this->tenantId) return ['labels' => [], 'series' => []];

        $rows = Sale::query()
            ->where('tenant_id', $this->tenantId)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(final_amount) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        $labels = [];
        $series = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M');
            $series[] = round((float) ($rows[$key] ?? 0), 2);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    #[Computed]
    public function orderDonut(): array
    {
        if (! $this->tenantId) return [];

        $counts = Order::query()
            ->where('tenant_id', $this->tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return collect(OrderStatus::cases())
            ->map(fn ($s) => [
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

#[Computed]
public function recentOrders()
{
    if (! $this->tenantId) return collect();

    return Order::query()
        ->where('tenant_id', $this->tenantId)
        ->with(['customer:id,name', 'sale:id,invoice_number'])
        ->select(['id', 'tenant_id', 'customer_id', 'order_number', 'status', 'total', 'sale_id', 'created_at'])
        ->latest()
        ->limit(8)
        ->get();
}
   
};
?>