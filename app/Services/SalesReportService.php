<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public readonly Carbon $start;
    public readonly Carbon $end;
    public readonly string $label;

    public function __construct(
        public readonly int $tenantId,
        string $range = 'month',
        ?string $from = null,
        ?string $to = null,
    ) {
        $now = now();

        [$start, $end, $label] = match ($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
            'week'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This week'],
            'year'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'This year'],
            'custom' => [
                $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth(),
                $to   ? Carbon::parse($to)->endOfDay()     : $now->copy()->endOfDay(),
                'Custom range',
            ],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This month'],
        };

        $this->start = $start;
        $this->end   = $end;
        $this->label = $label;
    }

    protected function baseQuery()
    {
        return Sale::query()
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$this->start, $this->end]);
    }

    public function kpis(): array
    {
        $salesQuery = $this->baseQuery();

        $gross      = (float) (clone $salesQuery)->sum('final_amount');
        $commission = (float) (clone $salesQuery)->sum('platform_commission');
        $net        = (float) (clone $salesQuery)->sum('shop_net_revenue');
        $salesCount = (int)   (clone $salesQuery)->count();

        $itemsSold = (int) SaleItem::query()
            ->whereHas('sale', fn ($q) => $q
                ->where('tenant_id', $this->tenantId)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$this->start, $this->end])
            )
            ->sum('quantity');

        return [
            'revenue'    => $gross,
            'commission' => $commission,
            'net'        => $net,
            'sales'      => $salesCount,
            'avg'        => $salesCount > 0 ? round($gross / $salesCount, 2) : 0.0,
            'items'      => $itemsSold,
        ];
    }

    public function topProducts(): array
    {
        $rows = SaleItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(subtotal) as revenue'),
            ])
            ->whereNotNull('product_id')
            ->whereHas('sale', fn ($q) => $q
                ->where('tenant_id', $this->tenantId)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$this->start, $this->end])
            )
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $productIds = $rows->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)
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

    public function topCustomers(): array
    {
        return Sale::query()
            ->select([
                'customer_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(final_amount) as spent'),
            ])
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'paid')
            ->whereNotNull('customer_id')
            ->whereBetween('created_at', [$this->start, $this->end])
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

    public function salesByEmployee(): array
    {
        return Sale::query()
            ->select([
                'served_by',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(final_amount) as revenue'),
            ])
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'paid')
            ->whereNotNull('served_by')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('served_by')
            ->orderByDesc('revenue')
            ->with('server:id,name,commission_rate')
            ->get()
            ->map(fn ($row) => [
                'name'            => $row->server?->name ?? 'Unknown',
                'commission_rate' => (float) ($row->server?->commission_rate ?? 0),
                'orders'          => (int) $row->orders,
                'revenue'         => (float) $row->revenue,
                'commission'      => (float) ($row->server?->commission_rate ?? 0) / 100 * (float) $row->revenue,
            ])
            ->toArray();
    }

    /** Full list — for the PDF (no pagination). */
    public function allSales()
    {
        return $this->baseQuery()
            ->with([
                'customer:id,name,email',
                'server:id,name',
                'items:id,sale_id,product_id,service_id,item_type,quantity,price,subtotal',
                'items.product:id,name,sku',
                'items.service:id,name',
            ])
            ->latest()
            ->get();
    }

    /** Paginated list — for the Livewire page. */
    public function paginatedSales(int $perPage = 15)
    {
        return $this->baseQuery()
            ->with([
                'customer:id,name,email',
                'server:id,name',
                'items:id,sale_id,product_id,service_id,item_type,quantity,price,subtotal',
                'items.product:id,name,sku',
                'items.service:id,name',
            ])
            ->latest()
            ->paginate($perPage);
    }
}