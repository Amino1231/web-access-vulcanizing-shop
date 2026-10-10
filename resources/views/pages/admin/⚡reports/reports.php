<?php

use App\Models\Appointment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Url(history: true)]
    public string $range = 'month';        // today | week | month | year | custom

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    public function updatedRange(): void
    {
        // Reset custom when switching away
        if ($this->range !== 'custom') {
            $this->from = '';
            $this->to   = '';
        }
    }

    public function updatedFrom(): void { $this->range = 'custom'; }
    public function updatedTo(): void   { $this->range = 'custom'; }

    #[Computed]
    public function period(): array
    {
        $now = now();

        return match ($this->range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
            'week'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This week'],
            'year'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'This year'],
            'custom' => [
                $this->from ? \Carbon\Carbon::parse($this->from)->startOfDay() : $now->copy()->startOfMonth(),
                $this->to   ? \Carbon\Carbon::parse($this->to)->endOfDay()     : $now->copy()->endOfDay(),
                'Custom range',
            ],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This month'],
        };
    }

    // ─────────────────────────────────────────
    // Platform KPIs
    // ─────────────────────────────────────────

    #[Computed]
    public function kpis(): array
    {
        [$start, $end] = $this->period;

        $gmv = (float) Sale::whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->sum('final_amount');

        $orders = Order::whereBetween('created_at', [$start, $end])->count();

        $completedOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->count();

        $completionRate = $orders > 0
            ? (int) round(($completedOrders / $orders) * 100)
            : 0;

        $newUsers = User::whereBetween('created_at', [$start, $end])->count();

        $newShops = Tenant::whereBetween('created_at', [$start, $end])->count();

        $activeShops = Tenant::where('is_active', true)
            ->where('verification_status', 'approved')
            ->count();

        return [
            ['label' => 'Platform GMV',     'value' => '₱'.number_format($gmv, 2),  'tone' => 'green'],
            ['label' => 'Orders placed',    'value' => (string) $orders,            'tone' => 'orange'],
            ['label' => 'Completion rate',  'value' => $completionRate.'%',         'tone' => 'amber'],
            ['label' => 'New users',        'value' => (string) $newUsers,          'tone' => 'red'],
            ['label' => 'New shops',        'value' => (string) $newShops,          'tone' => 'green'],
            ['label' => 'Active shops',     'value' => (string) $activeShops,       'tone' => 'orange'],
        ];
    }

    // ─────────────────────────────────────────
    // Revenue trend (last 12 months)
    // ─────────────────────────────────────────

    #[Computed]
    public function revenueTrend(): array
    {
        $first = now()->subMonths(11)->startOfMonth();

        $labels = [];
        $series = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');

            $series[] = (float) Sale::whereIn('status', ['paid', 'completed'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('final_amount');
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    // ─────────────────────────────────────────
    // Top shops by revenue in period
    // ─────────────────────────────────────────

    #[Computed]
    public function topShops()
    {
        [$start, $end] = $this->period;

        $revenue = Sale::query()
            ->selectRaw('tenant_id, SUM(final_amount) as revenue, COUNT(*) as orders')
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('tenant_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $tenantIds = $revenue->pluck('tenant_id');

        $shops = Tenant::query()
            ->whereIn('id', $tenantIds)
            ->with('user:id,name')
            ->get()
            ->keyBy('id');

        return $revenue->map(fn ($row) => [
            'id'       => $row->tenant_id,
            'name'     => $shops[$row->tenant_id]?->name ?? 'Deleted shop',
            'owner'    => $shops[$row->tenant_id]?->user?->name,
            'logo'     => $shops[$row->tenant_id]?->logo
                ? asset('storage/'.$shops[$row->tenant_id]->logo)
                : null,
            'orders'   => (int) $row->orders,
            'revenue'  => (float) $row->revenue,
        ])->toArray();
    }

    // ─────────────────────────────────────────
    // Top products across all shops
    // ─────────────────────────────────────────

    #[Computed]
    public function topProducts()
    {
        [$start, $end] = $this->period;

        return SaleItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(subtotal) as revenue'),
            ])
            ->whereNotNull('product_id')
            ->whereHas('sale', fn ($q) => $q
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('created_at', [$start, $end])
            )
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $product = Product::with('tenant:id,name')->find($row->product_id);

                return [
                    'name'    => $product?->name ?? 'Deleted product',
                    'shop'    => $product?->tenant?->name,
                    'sku'     => $product?->sku,
                    'qty'     => (int) $row->qty,
                    'revenue' => (float) $row->revenue,
                ];
            })
            ->toArray();
    }

    // ─────────────────────────────────────────
    // Category breakdown
    // ─────────────────────────────────────────

    #[Computed]
    public function categoryBreakdown()
    {
        [$start, $end] = $this->period;

        $rows = SaleItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(subtotal) as revenue'),
            ])
            ->whereNotNull('product_id')
            ->whereHas('sale', fn ($q) => $q
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('created_at', [$start, $end])
            )
            ->groupBy('product_id')
            ->get();

        $totals = [];
        $grand  = 0;

        foreach ($rows as $row) {
            $cat = Product::with('category:id,name')
                ->find($row->product_id)
                ?->category?->name ?? 'Uncategorized';

            $totals[$cat] = ($totals[$cat] ?? 0) + (float) $row->revenue;
            $grand += (float) $row->revenue;
        }

        arsort($totals);

        return collect($totals)
            ->map(fn ($rev, $label) => [
                'label'   => $label,
                'revenue' => $rev,
                'pct'     => $grand > 0 ? round(($rev / $grand) * 100, 1) : 0,
            ])
            ->values()
            ->take(6)
            ->toArray();
    }

    // ─────────────────────────────────────────
    // New signups breakdown
    // ─────────────────────────────────────────

    #[Computed]
    public function newSignups(): array
    {
        [$start, $end] = $this->period;

        $owners = User::role('owner')->whereBetween('created_at', [$start, $end])->count();
        $customers = User::role('customer')->whereBetween('created_at', [$start, $end])->count();

        return [
            'owners'    => $owners,
            'customers' => $customers,
            'total'     => $owners + $customers,
        ];
    }

    public function setRange(string $range): void
    {
        $this->range = $range;
        $this->from  = '';
        $this->to    = '';
    }
};