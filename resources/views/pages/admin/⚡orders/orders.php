<?php

use App\Enums\OrderStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = '';

    #[Url(history: true)]
    public string $tenantFilter = '';

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    public int $perPage = 20;

    // Detail modal
    public ?int $viewingOrderId = null;
    public ?array $viewingOrder = null;

    // Confirmation modal
    public ?int $confirmingOrderId = null;
    public ?string $confirmingAction = null;
    public ?string $confirmingNumber = null;
    public string $cancelReason = 'Cancelled by admin';

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTenantFilter(): void { $this->resetPage(); }
    public function updatingFrom(): void         { $this->resetPage(); }
    public function updatingTo(): void           { $this->resetPage(); }

    #[Computed]
    public function counts(): array
    {
        return [
            'all'        => Order::count(),
            'pending'    => Order::where('status', OrderStatus::Pending->value)->count(),
            'processing' => Order::where('status', OrderStatus::Processing->value)->count(),
            'ready'      => Order::where('status', OrderStatus::Ready->value)->count(),
            'completed'  => Order::where('status', OrderStatus::Completed->value)->count(),
            'cancelled'  => Order::where('status', OrderStatus::Cancelled->value)->count(),
        ];
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->with([
                'tenant:id,name,logo',
                'customer:id,name,email',
                'items:id,order_id,product_id,product_name,variant_label,sku,quantity,unit_price,line_total',
            ])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('order_number', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term))
                        ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', $term));
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->tenantFilter !== '', fn ($q) => $q->where('tenant_id', $this->tenantFilter))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '',   fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->tenantFilter = '';
        $this->from = '';
        $this->to = '';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Detail modal
    // ─────────────────────────────────────────

    public function viewOrder(int $orderId): void
    {
        $order = Order::query()
            ->with([
                'tenant:id,name,logo,phone,email,address',
                'customer:id,name,email,phone',
                'items.product:id,ft_img',
                'items.product.tenant:id,name',
                'sale:id,invoice_number,final_amount,payment_method,status',
            ])
            ->find($orderId);

        if (! $order) {
            session()->flash('error', 'Order not found.');
            return;
        }

        $this->viewingOrderId = $order->id;
        $this->viewingOrder = [
            'id'                  => $order->id,
            'order_number'        => $order->order_number,
            'status'              => $order->status->value,
            'status_label'        => $order->status->label(),
            'badge'               => $order->status->badge(),
            'subtotal'            => (float) $order->subtotal,
            'discount'            => (float) $order->discount,
            'tax'                 => (float) $order->tax,
            'total'               => (float) $order->total,
            'payment_method'      => $order->payment_method,
            'notes'               => $order->notes,
            'cancellation_reason' => $order->cancellation_reason,
            'created_at'          => $order->created_at?->format('M d, Y g:i A'),
            'processed_at'        => $order->processed_at?->format('M d, Y g:i A'),
            'completed_at'        => $order->completed_at?->format('M d, Y g:i A'),
            'cancelled_at'        => $order->cancelled_at?->format('M d, Y g:i A'),
            'tenant' => [
                'id'      => $order->tenant?->id,
                'name'    => $order->tenant?->name,
                'logo'    => $order->tenant?->logo ? asset('storage/'.$order->tenant->logo) : null,
                'phone'   => $order->tenant?->phone,
                'email'   => $order->tenant?->email,
                'address' => $order->tenant?->address,
            ],
            'customer' => [
                'id'    => $order->customer?->id,
                'name'  => $order->customer?->name ?? 'Deleted user',
                'email' => $order->customer?->email,
                'phone' => $order->customer?->phone,
            ],
            'sale' => $order->sale ? [
                'invoice_number' => $order->sale->invoice_number,
                'status'         => $order->sale->status,
                'final_amount'   => (float) $order->sale->final_amount,
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'id'            => $item->id,
                'product_id'    => $item->product_id,
                'product_name'  => $item->product_name,
                'variant_label' => $item->variant_label,
                'sku'           => $item->sku,
                'quantity'      => (int) $item->quantity,
                'unit_price'    => (float) $item->unit_price,
                'line_total'    => (float) $item->line_total,
                'image'         => $item->product?->ft_img
                    ? asset('storage/'.$item->product->ft_img)
                    : null,
                'shop'          => $item->product?->tenant?->name,
            ])->toArray(),
        ];
    }

    public function closeOrderModal(): void
    {
        $this->viewingOrderId = null;
        $this->viewingOrder = null;
    }

    // ─────────────────────────────────────────
    // Admin actions
    // ─────────────────────────────────────────

    public function confirmCancel(int $orderId): void
    {
        $order = Order::find($orderId);
        if (! $order) return;

        $this->confirmingOrderId   = $order->id;
        $this->confirmingAction    = 'cancel';
        $this->confirmingNumber    = $order->order_number;
        $this->cancelReason        = 'Cancelled by admin';
    }

    public function cancelAction(): void
    {
        $this->confirmingOrderId = null;
        $this->confirmingAction = null;
        $this->confirmingNumber = null;
        $this->cancelReason = 'Cancelled by admin';
        $this->resetValidation();
    }

    public function performCancel(): void
    {
        $this->validate([
            'cancelReason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        if (! $this->confirmingOrderId) return;

        $order = Order::with('items')->find($this->confirmingOrderId);
        if (! $order) {
            $this->cancelAction();
            return;
        }

        if (! $order->status->canBeCancelled()) {
            session()->flash('error', 'This order cannot be cancelled.');
            $this->cancelAction();
            return;
        }

        $wasProcessing = $order->status === OrderStatus::Processing;

        try {
            DB::transaction(function () use ($order, $wasProcessing) {
                if ($wasProcessing) {
                    foreach ($order->items as $item) {
                        $this->restoreStockForItem($order->tenant_id, $item, $order);
                    }
                }

                $order->update([
                    'status'              => OrderStatus::Cancelled,
                    'processed_by'        => Auth::id(),
                    'cancelled_at'        => now(),
                    'cancellation_reason' => $this->cancelReason.' (admin)',
                ]);
            });

            unset($this->orders, $this->counts);

            if ($this->viewingOrderId === $order->id) {
                $this->viewOrder($order->id);
            }

            session()->flash(
                'success',
                "Order {$order->order_number} cancelled."
                . ($wasProcessing ? ' Stock restored.' : '')
            );
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Could not cancel order.');
        }

        $this->cancelAction();
    }

    protected function restoreStockForItem(int $tenantId, $item, Order $order): void
    {
        if (! $item->product_id) return;

        $product = Product::where('tenant_id', $tenantId)
            ->whereKey($item->product_id)
            ->lockForUpdate()
            ->first();

        if (! $product) return;

        $qty = (int) $item->quantity;

        if ($item->variant_id) {
            $variant = Variant::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->whereKey($item->variant_id)
                ->lockForUpdate()
                ->first();

            if (! $variant) return;

            $before = (int) $variant->stock_quantity;
            $after  = $before + $qty;
            $variant->update(['stock_quantity' => $after]);

            $product->refresh()->syncStockFromVariants();

            Inventory::create([
                'tenant_id'      => $tenantId,
                'product_id'     => $product->id,
                'type'           => 'stock_in',
                'quantity'       => $qty,
                'before_stock'   => $before,
                'after_stock'    => $after,
                'reference_type' => 'order',
                'reference_id'   => $order->id,
                'remarks'        => "Order {$order->order_number} force-cancelled by admin · variant stock restored",
            ]);

            return;
        }

        $before = (int) $product->stock;
        $after  = $before + $qty;
        $product->update(['stock' => $after]);

        Inventory::create([
            'tenant_id'      => $tenantId,
            'product_id'     => $product->id,
            'type'           => 'stock_in',
            'quantity'       => $qty,
            'before_stock'   => $before,
            'after_stock'    => $after,
            'reference_type' => 'order',
            'reference_id'   => $order->id,
            'remarks'        => "Order {$order->order_number} force-cancelled by admin · stock restored",
        ]);
    }
};