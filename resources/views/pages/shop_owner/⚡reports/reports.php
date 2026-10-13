<?php

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $range = 'month';        // today | week | month | year | custom

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    public int $perPage = 15;

    public function updatedRange(): void
    {
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->range = 'custom';
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->range = 'custom';
        $this->resetPage();
    }

    protected function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    /**
     * Resolve [start, end] Carbon instances for the current range.
     */
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

    /**
     * Base sale query restricted to the active tenant + date range.
     */
    protected function baseSalesQuery()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return Sale::query()->whereRaw('0=1');
        }

        [$start, $end] = $this->period;

        return Sale::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end]);
    }

    #[Computed]
    public function kpis(): array
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return [
                'gross'      => 0.0,
                'commission' => 0.0,
                'net'        => 0.0,
                'sales'      => 0,
                'avg'        => 0.0,
                'items'      => 0,
            ];
        }

        [$start, $end] = $this->period;

        $salesQuery = Sale::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end]);

        $gross      = (float) (clone $salesQuery)->sum('final_amount');
        $commission = (float) (clone $salesQuery)->sum('platform_commission');
        $net        = (float) (clone $salesQuery)->sum('shop_net_revenue');

        $salesCount = (int) (clone $salesQuery)->count();

        $itemsSold = (int) SaleItem::query()
            ->whereHas('sale', fn ($q) => $q
                ->where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$start, $end])
            )
            ->sum('quantity');

        return [
            'gross'      => $gross,
            'commission' => $commission,
            'net'        => $net,
            'sales'      => $salesCount,
            'avg'        => $salesCount > 0 ? round($gross / $salesCount, 2) : 0.0,
            'items'      => $itemsSold,
        ];
    }

    #[Computed]
    public function sales()
    {
        return $this->baseSalesQuery()
            ->with([
                'customer:id,name,email',
                'server:id,name',
                'items:id,sale_id,product_id,service_id,item_type,quantity,price,subtotal',
                'items.product:id,name,sku',
                'items.service:id,name',
            ])
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function topProducts()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) return [];

        [$start, $end] = $this->period;

        $rows = SaleItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(subtotal) as revenue'),
            ])
            ->whereNotNull('product_id')
            ->whereHas('sale', fn ($q) => $q
                ->where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$start, $end])
            )
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Eager-load products in one query to avoid N+1
        $productIds = $rows->pluck('product_id')->unique();
        $products = \App\Models\Product::whereIn('id', $productIds)
            ->select('id', 'name', 'sku')
            ->get()
            ->keyBy('id');

        return $rows->map(fn ($row) => [
            'name'    => $products->get($row->product_id)?->name ?? 'Deleted product',
            'sku'     => $products->get($row->product_id)?->sku,
            'qty'     => (int) $row->qty,
            'revenue' => (float) $row->revenue,
        ])->toArray();
    }

    #[Computed]
    public function topCustomers()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) return [];

        [$start, $end] = $this->period;

        return Sale::query()
            ->select([
                'customer_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(final_amount) as spent'),
            ])
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereNotNull('customer_id')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('customer_id')
            ->orderByDesc('spent')
            ->limit(5)
            ->with('customer:id,name,email')
            ->get()
            ->map(fn ($row) => [
                'name'   => $row->customer?->name ?? 'Unknown',
                'email'  => $row->customer?->email,
                'orders' => (int) $row->orders,
                'spent'  => (float) $row->spent,
            ])
            ->toArray();
    }

    #[Computed]
    public function salesByEmployee()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) return [];

        [$start, $end] = $this->period;

        return Sale::query()
            ->select([
                'served_by',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(final_amount) as revenue'),
            ])
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereNotNull('served_by')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('served_by')
            ->orderByDesc('revenue')
            ->with('server:id,name,commission_rate')
            ->get()
            ->map(fn ($row) => [
                'name'             => $row->server?->name ?? 'Unknown',
                'commission_rate'  => (float) ($row->server?->commission_rate ?? 0),
                'orders'           => (int) $row->orders,
                'revenue'          => (float) $row->revenue,
                'commission'       => (float) ($row->server?->commission_rate ?? 0) / 100 * (float) $row->revenue,
            ])
            ->toArray();
    }

    public function setRange(string $range): void
    {
        $this->range = $range;
        $this->from  = '';
        $this->to    = '';
        $this->resetPage();
    }
};