<?php

use App\Enums\OrderStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Variant;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    public string $statusFilter = 'pending';

    public string $search = '';

    public int $perPage = 15;

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    #[Computed]
    public function orders()
    {
        if (! $this->tenantId) {
            return Order::query()->whereRaw('0 = 1')->paginate($this->perPage);
        }

        return Order::query()
            ->where('tenant_id', $this->tenantId)
            ->with([
                'customer:id,name,email',
                'items:id,order_id,product_id,variant_id,product_name,variant_label,sku,quantity,unit_price,line_total',
            ])
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('order_number', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function counts(): array
    {
        if (! $this->tenantId) {
            return [];
        }

        return Order::query()
            ->where('tenant_id', $this->tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    // ─────────────────────────────────────────
    // Accept → deducts stock + notifies customer
    // ─────────────────────────────────────────

    public function accept(int $orderId): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            $this->dispatch('notify', type: 'error', message: 'No tenant account linked.');

            return;
        }

        $order = null;

        try {
            DB::transaction(function () use ($orderId, $tenantId, &$order) {
                $order = Order::with('items')
                    ->where('tenant_id', $tenantId)
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $order->status->canBeProcessed()) {
                    throw new RuntimeException('invalid_transition');
                }

                foreach ($order->items as $item) {
                    $this->deductStockForItem($tenantId, $item, $order);
                }

                $order->update([
                    'status' => OrderStatus::Processing,
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                ]);
            });

            // Notify customer
            $order?->customer?->notify(new OrderStatusChanged($order, 'processing'));

            unset($this->orders, $this->counts);
            $this->dispatch('notify', type: 'success', message: 'Order accepted. Stock reserved.');
        } catch (RuntimeException $e) {
            $message = match ($e->getMessage()) {
                'insufficient_stock' => 'Not enough stock to accept this order.',
                'variant_missing' => 'One of the variants no longer exists.',
                'product_missing' => 'One of the products no longer exists.',
                'invalid_transition' => 'Invalid status change.',
                default => 'Could not accept order.',
            };
            $this->dispatch('notify', type: 'error', message: $message);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not accept order.');
        }
    }

    protected function deductStockForItem(int $tenantId, $item, Order $order): void
    {
        if (! $item->product_id) {
            return;
        }

        $product = Product::where('tenant_id', $tenantId)
            ->whereKey($item->product_id)
            ->lockForUpdate()
            ->first();

        if (! $product) {
            throw new RuntimeException('product_missing');
        }

        $qty = (int) $item->quantity;

        if ($item->variant_id) {
            $variant = Variant::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->whereKey($item->variant_id)
                ->lockForUpdate()
                ->first();

            if (! $variant) {
                throw new RuntimeException('variant_missing');
            }

            $before = (int) $variant->stock_quantity;
            if ($before < $qty) {
                throw new RuntimeException('insufficient_stock');
            }

            $after = $before - $qty;
            $variant->update(['stock_quantity' => $after]);

            $product->refresh()->syncStockFromVariants();

            Inventory::create([
                'tenant_id' => $tenantId,
                'product_id' => $product->id,
                'type' => 'stock_out',
                'quantity' => -$qty,
                'before_stock' => $before,
                'after_stock' => $after,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'remarks' => "Order {$order->order_number} accepted · variant {$item->variant_label}",
            ]);

            return;
        }

        $before = (int) $product->stock;
        if ($before < $qty) {
            throw new RuntimeException('insufficient_stock');
        }

        $after = $before - $qty;
        $product->update(['stock' => $after]);

        Inventory::create([
            'tenant_id' => $tenantId,
            'product_id' => $product->id,
            'type' => 'stock_out',
            'quantity' => -$qty,
            'before_stock' => $before,
            'after_stock' => $after,
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'remarks' => "Order {$order->order_number} accepted",
        ]);
    }

    // ─────────────────────────────────────────
    // Mark ready → no stock change + notifies customer
    // ─────────────────────────────────────────

    public function markReady(int $orderId): void
    {
        $this->applyStatus($orderId, OrderStatus::Ready);

        $order = Order::where('tenant_id', $this->tenantId)->find($orderId);
        $order?->customer?->notify(new OrderStatusChanged($order, 'ready'));
    }

    // ─────────────────────────────────────────
    // Cancel → restores stock (if processing) + notifies customer
    // ─────────────────────────────────────────

    public function cancel(int $orderId, string $reason = 'Cancelled by shop'): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            $this->dispatch('notify', type: 'error', message: 'No tenant account linked.');

            return;
        }

        $wasProcessing = false;
        $order = null;

        try {
            DB::transaction(function () use ($orderId, $tenantId, $reason, &$wasProcessing, &$order) {
                $order = Order::with('items')
                    ->where('tenant_id', $tenantId)
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $order->status->canBeCancelled()) {
                    throw new RuntimeException('invalid_transition');
                }

                if ($order->status === OrderStatus::Processing) {
                    $wasProcessing = true;

                    foreach ($order->items as $item) {
                        $this->restoreStockForItem($tenantId, $item, $order);
                    }
                }

                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'processed_by' => Auth::id(),
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                ]);
            });

            // Notify customer
            $order?->customer?->notify(new OrderStatusChanged($order, 'cancelled', $reason));

            unset($this->orders, $this->counts);
            $this->dispatch(
                'notify',
                type: 'success',
                message: $wasProcessing
                    ? 'Order cancelled. Stock restored.'
                    : 'Order cancelled.'
            );
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: 'Invalid status change.');
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not cancel order.');
        }
    }

    protected function restoreStockForItem(int $tenantId, $item, Order $order): void
    {
        if (! $item->product_id) {
            return;
        }

        $product = Product::where('tenant_id', $tenantId)
            ->whereKey($item->product_id)
            ->lockForUpdate()
            ->first();

        if (! $product) {
            return;
        }

        $qty = (int) $item->quantity;

        if ($item->variant_id) {
            $variant = Variant::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->whereKey($item->variant_id)
                ->lockForUpdate()
                ->first();

            if (! $variant) {
                return;
            }

            $before = (int) $variant->stock_quantity;
            $after = $before + $qty;
            $variant->update(['stock_quantity' => $after]);

            $product->refresh()->syncStockFromVariants();

            Inventory::create([
                'tenant_id' => $tenantId,
                'product_id' => $product->id,
                'type' => 'stock_in',
                'quantity' => $qty,
                'before_stock' => $before,
                'after_stock' => $after,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'remarks' => "Order {$order->order_number} cancelled · variant {$item->variant_label} stock restored",
            ]);

            return;
        }

        $before = (int) $product->stock;
        $after = $before + $qty;
        $product->update(['stock' => $after]);

        Inventory::create([
            'tenant_id' => $tenantId,
            'product_id' => $product->id,
            'type' => 'stock_in',
            'quantity' => $qty,
            'before_stock' => $before,
            'after_stock' => $after,
            'reference_type' => 'order',
            'reference_id' => $order->id,
            'remarks' => "Order {$order->order_number} cancelled · stock restored",
        ]);
    }

    // ─────────────────────────────────────────
    // Complete → creates sale + notifies customer
    // ─────────────────────────────────────────

    public function complete(int $orderId): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            $this->dispatch('notify', type: 'error', message: 'No tenant account linked.');

            return;
        }

        $order = null;

        try {
            DB::transaction(function () use ($orderId, $tenantId, &$order) {
                $order = Order::with('items')
                    ->where('tenant_id', $tenantId)
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $order->status->canBeCompleted()) {
                    throw new RuntimeException('invalid_transition');
                }

                $sale = Sale::create([
                    'tenant_id' => $order->tenant_id,
                    'customer_id' => $order->customer_id,
                    'served_by' => null,
                    'invoice_number' => 'INV-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6)),
                    'subtotal' => $order->subtotal,
                    'discount' => $order->discount,
                    'tax' => $order->tax,
                    'final_amount' => $order->total,
                    'payment_method' => $order->payment_method,
                    'status' => 'paid',
                ]);

                foreach ($order->items as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item->product_id,
                        'service_id' => null,
                        'item_type' => 'product',
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price,
                        'subtotal' => $item->line_total,
                    ]);
                }

                // ─── Payment status on completion ─────────────────
                // Cash on pickup stays as-is (already paid at handover
                // or still unpaid if the shop chooses to track it manually).
                // Every other method (bank transfer, GCash, card, Maya) is
                // considered settled once the shop completes the order.
                $paymentUpdates = [];
                if ($order->payment_method !== 'cash_on_pickup') {
                    $paymentUpdates = [
                        'payment_status' => 'paid',
                        'paid_at' => $order->paid_at ?? now(),
                    ];
                }

                $order->update(array_merge([
                    'status' => OrderStatus::Completed,
                    'completed_at' => now(),
                    'processed_by' => Auth::id(),
                    'sale_id' => $sale->id,
                ], $paymentUpdates));
            });

            $order?->customer?->notify(new OrderStatusChanged($order, 'completed'));

            unset($this->orders, $this->counts);
            $this->dispatch('notify', type: 'success', message: 'Order completed. Sale recorded.');
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: 'Order is not ready to complete.');
        } catch (Throwable $e) {
            Log::error('ORDER COMPLETE FAILED', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->dispatch('notify', type: 'error', message: 'Could not complete order.');
        }
    }

    // ─────────────────────────────────────────
    // Simple status change (no stock, no notify)
    // ─────────────────────────────────────────

    protected function applyStatus(int $orderId, OrderStatus $to, ?string $reason = null): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            $this->dispatch('notify', type: 'error', message: 'No tenant account linked.');

            return;
        }

        try {
            DB::transaction(function () use ($orderId, $tenantId, $to) {
                $order = Order::query()
                    ->where('tenant_id', $tenantId)
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $allowed = match ($to) {
                    OrderStatus::Ready => $order->status->canBeMarkedReady(),
                    default => false,
                };

                if (! $allowed) {
                    throw new RuntimeException('invalid_transition');
                }

                $order->update([
                    'status' => $to,
                    'processed_by' => Auth::id(),
                ]);
            });
        } catch (RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: 'Invalid status change.');

            return;
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not update order.');

            return;
        }

        unset($this->orders, $this->counts);
        $this->dispatch('notify', type: 'success', message: "Order marked as {$to->label()}.");
    }
};
