<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Unified Inventory Management
 * ============================
 *
 * Two tabs:
 *   - Products : unit-based stock + movement audit log
 *   - Services : booking-based utilization + appointment log
 *
 * Products and services are fundamentally different inventories:
 *   - Products have finite stock (units on hand)
 *   - Services have temporal capacity (time slots)
 *
 * The tabs keep them separated without splitting into two pages.
 */
new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $tab = 'products';   // products | services

    // ─── Product log filters ──────────────────
    public string $logSearch = '';
    public string $logType = '';
    public string $logFrom = '';
    public string $logTo = '';
    public int $perPage = 15;

    // ─── Product adjustment modal ─────────────
    public bool $showAdjustModal = false;
    public string $productSearch = '';
    public ?int $selectedProductId = null;
    public ?int $selectedVariantId = null;
    public string $adjustType = 'stock_in';
    public int $adjustQuantity = 1;
    public string $adjustReason = '';

    // ─── Service log filters ──────────────────
    public string $serviceLogSearch = '';
    public string $serviceLogStatus = '';
    public string $serviceLogFrom = '';
    public string $serviceLogTo = '';

    // ─── Stats ────────────────────────────────
    public array $kpis = [];
    public array $chartLabels = [];
    public array $lineSeries = [];
    public array $donutSeries = [];

    public array $serviceKpis = [];
    public array $serviceChartLabels = [];
    public array $serviceLineSeries = [];
    public array $serviceDonutSeries = [];

    public function updatingLogSearch(): void        { $this->resetPage('productPage'); }
    public function updatingLogType(): void          { $this->resetPage('productPage'); }
    public function updatingLogFrom(): void          { $this->resetPage('productPage'); }
    public function updatingLogTo(): void            { $this->resetPage('productPage'); }
    public function updatingServiceLogSearch(): void { $this->resetPage('servicePage'); }
    public function updatingServiceLogStatus(): void { $this->resetPage('servicePage'); }
    public function updatingServiceLogFrom(): void   { $this->resetPage('servicePage'); }
    public function updatingServiceLogTo(): void     { $this->resetPage('servicePage'); }

    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['products', 'services'], true) ? $tab : 'products';

        // Reset any cross-tab filters that no longer apply
        // (optional — remove if you want filters to persist across tabs)
        // $this->resetPage('productPage');
        // $this->resetPage('servicePage');

        // Tell the browser to redraw charts after the new tab renders
        $this->dispatch('tab-changed', tab: $this->tab);
    }

    protected function refreshStats(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            $this->kpis = $this->chartLabels = $this->lineSeries = $this->donutSeries = [];
            $this->serviceKpis = $this->serviceChartLabels = $this->serviceLineSeries = $this->serviceDonutSeries = [];
            return;
        }

        $this->refreshProductStats($tenantId);
        $this->refreshServiceStats($tenantId);
    }

    protected function refreshProductStats(int $tenantId): void
    {
        $products = Product::query()
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'stock', 'low_stock_alert'])
            ->get();

        $totalProducts = $products->count();
        $totalStock    = (int) $products->sum('stock');
        $lowStockCount = $products->filter(
            fn ($p) => $p->stock > 0 && $p->stock <= ($p->low_stock_alert ?? 5)
        )->count();
        $outOfStock    = $products->where('stock', '<=', 0)->count();

        $salesTotal = (float) Sale::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('final_amount');

        $this->kpis = [
            ['label' => 'Product items', 'value' => (string) $totalProducts, 'delta' => 'Catalog', 'tone' => 'orange'],
            ['label' => 'Stock units',   'value' => (string) $totalStock,    'delta' => 'On hand', 'tone' => 'green'],
            ['label' => 'Low stock',     'value' => (string) $lowStockCount, 'delta' => $lowStockCount > 0 ? 'Attention' : 'All good', 'tone' => $lowStockCount > 0 ? 'amber' : 'green'],
            ['label' => 'Sales total',   'value' => '₱' . number_format($salesTotal, 2), 'delta' => 'Revenue', 'tone' => 'green'],
        ];

        $labels = [];
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');
            $series[] = (int) Inventory::query()
                ->where('tenant_id', $tenantId)
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('quantity');
        }
        $this->chartLabels = $labels;
        $this->lineSeries  = $series;

        $this->donutSeries = [
            ['label' => 'In stock',     'value' => max($products->where('stock', '>', 0)->count() - $lowStockCount, 0), 'color' => '#10b981'],
            ['label' => 'Low stock',    'value' => $lowStockCount,                                                     'color' => '#f59e0b'],
            ['label' => 'Out of stock', 'value' => max($outOfStock, 0),                                                'color' => '#ef4444'],
        ];
    }

    protected function refreshServiceStats(int $tenantId): void
    {
        $services = Service::query()
            ->where('tenant_id', $tenantId)
            ->select(['id', 'name', 'is_active', 'price'])
            ->get();

        $totalServices  = $services->count();
        $activeServices = $services->where('is_active', true)->count();
        $inactiveServices = max($totalServices - $activeServices, 0);

        $monthStart = now()->startOfMonth();

        $bookingsThisMonth = Appointment::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $completedThisMonth = Appointment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', AppointmentStatus::Completed->value)
            ->where('updated_at', '>=', $monthStart)
            ->count();

        $serviceRevenue = (float) \App\Models\SaleItem::query()
            ->whereNotNull('service_id')
            ->whereHas('sale', fn ($q) => $q->where('tenant_id', $tenantId)->whereIn('status', ['paid', 'completed']))
            ->sum('subtotal');

        $this->serviceKpis = [
            ['label' => 'Services offered', 'value' => (string) $totalServices, 'delta' => 'Catalog', 'tone' => 'orange'],
            ['label' => 'Active services',  'value' => (string) $activeServices, 'delta' => $activeServices === $totalServices ? 'All live' : 'Some offline', 'tone' => 'green'],
            ['label' => 'Bookings (mo.)',   'value' => (string) $bookingsThisMonth, 'delta' => $completedThisMonth . ' completed', 'tone' => 'amber'],
            ['label' => 'Service revenue',  'value' => '₱' . number_format($serviceRevenue, 2), 'delta' => 'Lifetime', 'tone' => 'emerald'],
        ];

        // 6-month booking trend
        $labels = [];
        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');
            $series[] = (int) Appointment::query()
                ->where('tenant_id', $tenantId)
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }
        $this->serviceChartLabels = $labels;
        $this->serviceLineSeries  = $series;

        $this->serviceDonutSeries = [
            ['label' => 'Active services',   'value' => $activeServices,   'color' => '#10b981'],
            ['label' => 'Inactive services', 'value' => $inactiveServices, 'color' => '#94a3b8'],
        ];
    }

    // ─────────────────────────────────────────
    // Product log
    // ─────────────────────────────────────────

    #[Computed]
    public function logs()
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) {
            return Inventory::query()->whereRaw('0=1')->paginate($this->perPage, ['*'], 'productPage');
        }

        return Inventory::query()
            ->where('tenant_id', $tenantId)
            ->with(['product:id,name,sku'])
            ->when($this->logSearch !== '', function ($q) {
                $term = '%'.$this->logSearch.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('remarks', 'like', $term)
                        ->orWhereHas('product', fn ($p) => $p
                            ->where('name', 'like', $term)
                            ->orWhere('sku', 'like', $term));
                });
            })
            ->when($this->logType !== '', fn ($q) => $q->where('type', $this->logType))
            ->when($this->logFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->logFrom))
            ->when($this->logTo !== '',   fn ($q) => $q->whereDate('created_at', '<=', $this->logTo))
            ->latest()
            ->paginate($this->perPage, ['*'], 'productPage');
    }

    public function clearLogFilters(): void
    {
        $this->logSearch = '';
        $this->logType   = '';
        $this->logFrom   = '';
        $this->logTo     = '';
        $this->resetPage('productPage');
    }

    // ─────────────────────────────────────────
    // Service log (appointments)
    // ─────────────────────────────────────────

    #[Computed]
    public function serviceLogs()
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) {
            return Appointment::query()->whereRaw('0=1')->paginate($this->perPage, ['*'], 'servicePage');
        }

        return Appointment::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'service:id,name,duration_minutes',
                'customer:id,name,phone',
                'employee:id,name',
            ])
            ->when($this->serviceLogSearch !== '', function ($q) {
                $term = '%'.$this->serviceLogSearch.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('appointment_number', 'like', $term)
                        ->orWhereHas('service', fn ($s) => $s->where('name', 'like', $term))
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->when($this->serviceLogStatus !== '', fn ($q) => $q->where('status', $this->serviceLogStatus))
            ->when($this->serviceLogFrom !== '', fn ($q) => $q->whereDate('start_time', '>=', $this->serviceLogFrom))
            ->when($this->serviceLogTo !== '',   fn ($q) => $q->whereDate('start_time', '<=', $this->serviceLogTo))
            ->latest('start_time')
            ->paginate($this->perPage, ['*'], 'servicePage');
    }

    public function clearServiceLogFilters(): void
    {
        $this->serviceLogSearch = '';
        $this->serviceLogStatus = '';
        $this->serviceLogFrom   = '';
        $this->serviceLogTo     = '';
        $this->resetPage('servicePage');
    }

    // ─────────────────────────────────────────
    // Adjustment modal
    // ─────────────────────────────────────────

    public function openAdjustModal(): void
    {
        $this->resetValidation();
        $this->productSearch     = '';
        $this->selectedProductId = null;
        $this->selectedVariantId = null;
        $this->adjustType        = 'stock_in';
        $this->adjustQuantity    = 1;
        $this->adjustReason      = '';
        $this->showAdjustModal   = true;
    }

    public function closeAdjustModal(): void
    {
        $this->showAdjustModal = false;
        $this->resetValidation();
    }

    public function pickProduct(int $productId): void
    {
        $product = Product::where('tenant_id', $this->tenantId)->find($productId);
        if (! $product) return;

        $this->selectedProductId = $product->id;
        $this->selectedVariantId = null;
        $this->productSearch     = '';
        $this->resetValidation();
    }

    public function clearPickedProduct(): void
    {
        $this->selectedProductId = null;
        $this->selectedVariantId = null;
    }

    #[Computed]
    public function pickerProducts()
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) return collect();

        return Product::query()
            ->where('tenant_id', $tenantId)
            ->when($this->productSearch !== '', function ($q) {
                $term = '%'.$this->productSearch.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term);
                });
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'sku', 'stock', 'low_stock_alert']);
    }

    #[Computed]
    public function pickedProduct(): ?Product
    {
        if (! $this->selectedProductId) return null;

        return Product::where('tenant_id', $this->tenantId)
            ->with(['variants:id,product_id,sku,stock_quantity'])
            ->find($this->selectedProductId);
    }

    #[Computed]
    public function currentStock(): int
    {
        $product = $this->pickedProduct;
        if (! $product) return 0;

        if ($this->selectedVariantId) {
            return (int) ($product->variants->firstWhere('id', $this->selectedVariantId)?->stock_quantity ?? 0);
        }

        return (int) $product->stock;
    }

    public function saveAdjustment(): void
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) {
            $this->addError('general', 'No tenant account linked.');
            return;
        }

        $this->validate([
            'selectedProductId' => ['required', 'integer'],
            'selectedVariantId' => ['nullable', 'integer'],
            'adjustType'        => ['required', 'in:stock_in,stock_out'],
            'adjustQuantity'    => ['required', 'integer', 'min:1', 'max:100000'],
            'adjustReason'      => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'selectedProductId.required' => 'Pick a product first.',
            'adjustQuantity.min'         => 'Quantity must be at least 1.',
            'adjustReason.required'      => 'Add a short reason for the log.',
        ]);

        try {
            DB::transaction(function () use ($tenantId) {
                $product = Product::where('tenant_id', $tenantId)
                    ->whereKey($this->selectedProductId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $qty     = (int) $this->adjustQuantity;
                $isIn    = $this->adjustType === 'stock_in';
                $remarks = trim($this->adjustReason);

                if ($this->selectedVariantId) {
                    $variant = Variant::where('tenant_id', $tenantId)
                        ->where('product_id', $product->id)
                        ->whereKey($this->selectedVariantId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $before = (int) $variant->stock_quantity;

                    if (! $isIn && $before < $qty) {
                        throw new \RuntimeException('insufficient_stock');
                    }

                    $after = $isIn ? $before + $qty : $before - $qty;
                    $variant->update(['stock_quantity' => $after]);

                    $product->refresh()->syncStockFromVariants();

                    Inventory::create([
                        'tenant_id'      => $tenantId,
                        'product_id'     => $product->id,
                        'type'           => $isIn ? 'stock_in' : 'stock_out',
                        'quantity'       => $isIn ? $qty : -$qty,
                        'before_stock'   => $before,
                        'after_stock'    => $after,
                        'reference_type' => 'manual',
                        'reference_id'   => null,
                        'remarks'        => $remarks." (variant {$variant->sku})",
                    ]);

                    return;
                }

                $before = (int) $product->stock;

                if (! $isIn && $before < $qty) {
                    throw new \RuntimeException('insufficient_stock');
                }

                $after = $isIn ? $before + $qty : $before - $qty;
                $product->update(['stock' => $after]);

                Inventory::create([
                    'tenant_id'      => $tenantId,
                    'product_id'     => $product->id,
                    'type'           => $isIn ? 'stock_in' : 'stock_out',
                    'quantity'       => $isIn ? $qty : -$qty,
                    'before_stock'   => $before,
                    'after_stock'    => $after,
                    'reference_type' => 'manual',
                    'reference_id'   => null,
                    'remarks'        => $remarks,
                ]);
            });

            $this->refreshStats();
            unset($this->logs, $this->pickerProducts, $this->pickedProduct);

            $this->closeAdjustModal();

            $this->dispatch(
                'notify',
                type: 'success',
                message: $this->adjustType === 'stock_in'
                    ? 'Stock added successfully.'
                    : 'Stock removed successfully.'
            );
        } catch (\RuntimeException $e) {
            $message = match ($e->getMessage()) {
                'insufficient_stock' => 'Not enough stock to remove that many units.',
                default              => 'Could not adjust stock.',
            };
            $this->dispatch('notify', type: 'error', message: $message);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not adjust stock.');
        }
    }
};