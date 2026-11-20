<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;

    /** View Modal */
    public ?int $viewingSaleId = null;

    /** Manual Sale Modal */
    public bool $showManualSaleModal = false;
    public string $item_type = 'service'; 
    public ?int $item_id = null;
    public int $quantity = 1;
    public string $payment_method = 'cash';
    public string $customer_name = ''; 

    public function updatingSearch(): void { $this->resetPage(); }

    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    #[Computed]
    public function tenant(): ?Tenant
    {
        return Auth::user()?->currentTenant();
    }

    #[Computed]
    public function summary(): array
    {
        if (! $this->tenantId) return ['total_sales' => 0, 'gross' => 0, 'commission' => 0, 'net' => 0];

        $data = Sale::where('tenant_id', $this->tenantId)
            ->selectRaw('COUNT(*) as total_sales, SUM(final_amount) as gross, SUM(platform_commission) as commission, SUM(shop_net_revenue) as net')
            ->first();

        return [
            'total_sales' => $data->total_sales ?? 0,
            'gross'       => $data->gross ?? 0,
            'commission'  => $data->commission ?? 0,
            'net'         => $data->net ?? 0,
        ];
    }

    #[Computed]
    public function saleItems()
    {
        if (! $this->tenantId) {
            return SaleItem::query()->whereRaw('0=1')->paginate($this->perPage);
        }

        return SaleItem::query()
            ->whereHas('sale', function($q) {
                $q->where('tenant_id', $this->tenantId);
            })
            ->with(['sale.customer', 'product', 'service'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('sale', fn($sq) => $sq->where('invoice_number', 'like', $term))
                        ->orWhereHas('sale.customer', fn($c) => $c->where('name', 'like', $term))
                        ->orWhereHas('sale', fn($sq) => $sq->where('customer_name', 'like', $term)) // Search Walk-ins
                        ->orWhereHas('product', fn($p) => $p->where('name', 'like', $term))
                        ->orWhereHas('service', fn($s) => $s->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function viewingSale(): ?Sale
    {
        if (! $this->viewingSaleId || ! $this->tenantId) return null;

        return Sale::with(['customer', 'items.product', 'items.service', 'server'])
            ->where('tenant_id', $this->tenantId)
            ->find($this->viewingSaleId);
    }

    #[Computed]
    public function availableServices()
    {
        if (! $this->tenantId) return collect();
        return Service::where('tenant_id', $this->tenantId)->where('is_active', true)->get();
    }

    #[Computed]
    public function availableProducts()
    {
        if (! $this->tenantId) return collect();
        return Product::where('tenant_id', $this->tenantId)->get();
    }

    // ─────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────

    public function viewSale(int $id): void
    {
        $this->viewingSaleId = $id;
        unset($this->saleItems, $this->summary); 
    }

    public function closeSaleModal(): void
    {
        $this->viewingSaleId = null;
        unset($this->saleItems, $this->summary); 
    }

    public function openManualSaleModal(): void
    {
        $this->reset(['item_type', 'item_id', 'quantity', 'payment_method', 'customer_name']);
        $this->showManualSaleModal = true;
        unset($this->saleItems, $this->summary); 
    }

    public function closeManualSaleModal(): void
    {
        $this->showManualSaleModal = false;
        $this->resetValidation();
        unset($this->saleItems, $this->summary); 
    }

    public function saveManualSale()
    {
        if (! $this->tenantId || ! $this->tenant) return;

        $this->validate([
            'item_type'      => ['required', 'in:service,product'],
            'item_id'        => ['required', 'integer'],
            'quantity'       => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string'],
            'customer_name'  => ['nullable', 'string', 'max:255'],
        ]);

        $item = $this->item_type === 'service'
            ? Service::where('tenant_id', $this->tenantId)->find($this->item_id)
            : Product::where('tenant_id', $this->tenantId)->find($this->item_id);

        if (! $item) {
            $this->addError('item_id', 'Selected item is invalid.');
            return;
        }

        $price = $this->item_type === 'service' ? $item->price : $item->selling_price;
        $subtotal = $price * $this->quantity;

        // Handle Walk-in Name (NO User creation)
        $customerName = trim($this->customer_name) !== '' 
            ? trim($this->customer_name) . ' (Walk-In)' 
            : 'Walk-In';

        try {
            DB::transaction(function () use ($item, $price, $subtotal, $customerName) {
                $sale = new Sale([
                    'tenant_id'      => $this->tenantId,
                    'customer_id'    => null, // Always null for walk-ins
                    'customer_name'  => $customerName,
                    'served_by'      => null,
                    'invoice_number' => 'INV-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6)),
                    'subtotal'       => $subtotal,
                    'discount'       => 0,
                    'tax'            => 0,
                    'final_amount'   => $subtotal,
                    'payment_method' => $this->payment_method,
                    'status'         => 'paid',
                ]);

                $rate = $this->tenant->commissionRate() ?? 10.00;
                $sale->applyCommission($rate);
                $sale->save();

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $this->item_type === 'product' ? $item->id : null,
                    'service_id' => $this->item_type === 'service' ? $item->id : null,
                    'item_type'  => $this->item_type,
                    'quantity'   => $this->quantity,
                    'price'      => $price,
                    'subtotal'   => $subtotal,
                ]);
            });

            $this->closeManualSaleModal();
            unset($this->saleItems, $this->summary);
            $this->dispatch('notify', type: 'success', message: 'Manual sale recorded successfully.');

        } catch (\Throwable $e) {
            \Log::error('MANUAL SALE FAILED', ['message' => $e->getMessage()]);
            $this->dispatch('notify', type: 'error', message: 'Could not record manual sale.');
        }

        return redirect()->route('owner.sales');
    }
};