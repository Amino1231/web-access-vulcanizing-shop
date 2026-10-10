<?php

use App\Enums\OrderStatus;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    // ─── Log filters ──────────────────────────
    public string $logSearch = '';
    public string $logType = '';        // '' | stock_in | stock_out
    public string $logFrom = '';
    public string $logTo = '';
    public int $perPage = 15;

    // ─── Adjustment modal ─────────────────────
    public bool $showAdjustModal = false;
    public string $productSearch = '';
    public ?int $selectedProductId = null;
    public ?int $selectedVariantId = null;
    public string $adjustType = 'stock_in';   // stock_in | stock_out
    public int $adjustQuantity = 1;
    public string $adjustReason = '';

    public array $kpis = [];
    public array $chartLabels = [];
    public array $lineSeries = [];
    public array $donutSeries = [];

    public function updatingLogSearch(): void { $this->resetPage(); }
    public function updatingLogType(): void   { $this->resetPage(); }
    public function updatingLogFrom(): void   { $this->resetPage(); }
    public function updatingLogTo(): void     { $this->resetPage(); }

    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    public function mount(): void
    {
        $this->refreshStats();
    }

    protected function refreshStats(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            $this->kpis        = [];
            $this->chartLabels = [];
            $this->lineSeries  = [];
            $this->donutSeries = [];
            return;
        }

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
            ['label' => 'Products',    'value' => (string) $totalProducts,                              'delta' => 'Catalog',                  'tone' => 'orange', 'filter' => ''],
            ['label' => 'Stock units', 'value' => (string) $totalStock,                                 'delta' => 'On hand',                  'tone' => 'green',  'filter' => ''],
            ['label' => 'Low stock',   'value' => (string) $lowStockCount,                              'delta' => $lowStockCount > 0 ? 'Attention' : 'All good', 'tone' => $lowStockCount > 0 ? 'amber' : 'green', 'filter' => 'low'],
            ['label' => 'Sales total', 'value' => '₱' . number_format($salesTotal, 2),                  'delta' => 'Revenue',                  'tone' => 'green',  'filter' => ''],
        ];

        // Line chart — 6-month net inventory movement
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

    // ─────────────────────────────────────────
    // Log (paginated + filtered)
    // ─────────────────────────────────────────

    #[Computed]
    public function logs()
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) {
            return Inventory::query()->whereRaw('0=1')->paginate($this->perPage);
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
            ->paginate($this->perPage);
    }

    public function clearLogFilters(): void
    {
        $this->logSearch = '';
        $this->logType   = '';
        $this->logFrom   = '';
        $this->logTo     = '';
        $this->resetPage();
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

                // ─── Variant adjustment ─────────
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

                // ─── Plain product adjustment ──
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