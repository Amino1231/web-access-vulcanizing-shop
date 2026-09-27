<?php

use App\Enums\OrderStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
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

    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->with([
                'customer:id,name,email',
                'items:id,order_id,product_id,product_name,variant_label,sku,quantity,unit_price,line_total',
            ])
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
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
        return Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    public function accept(int $orderId): void
    {
        $this->applyStatus($orderId, OrderStatus::Processing);
    }

    public function markReady(int $orderId): void
    {
        $this->applyStatus($orderId, OrderStatus::Ready);
    }

    public function cancel(int $orderId, string $reason = 'Cancelled by shop'): void
    {
        $this->applyStatus($orderId, OrderStatus::Cancelled, $reason);
    }

    protected function applyStatus(int $orderId, OrderStatus $to, ?string $reason = null): void
    {
        try {
            DB::transaction(function () use ($orderId, $to, $reason) {
                $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();

                $allowed = match ($to) {
                    OrderStatus::Processing => $order->status->canBeProcessed(),
                    OrderStatus::Ready      => $order->status->canBeMarkedReady(),
                    OrderStatus::Cancelled  => $order->status->canBeCancelled(),
                    default                 => false,
                };

                if (! $allowed) {
                    throw new \RuntimeException('invalid_transition');
                }

                $payload = ['status' => $to, 'processed_by' => Auth::id()];

                if ($to === OrderStatus::Processing) {
                    $payload['processed_at'] = now();
                }

                if ($to === OrderStatus::Cancelled) {
                    $payload['cancelled_at'] = now();
                    $payload['cancellation_reason'] = $reason;
                }

                $order->update($payload);
            });
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', type: 'error', message: 'Invalid status change.');
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not update order.');
            return;
        }

        unset($this->orders, $this->counts);
        $this->dispatch('notify', type: 'success', message: "Order marked as {$to->label()}.");
    }

    public function complete(int $orderId): void
    {
        try {
            DB::transaction(function () use ($orderId) {
                $order = Order::with('items')->whereKey($orderId)->lockForUpdate()->firstOrFail();

                if (! $order->status->canBeCompleted()) {
                    throw new \RuntimeException('invalid_transition');
                }

                        $sale = Sale::create([
                'tenant_id'      => $order->tenant_id,
                'customer_id'    => $order->customer_id,
                'served_by'      => null, 
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'subtotal'       => $order->subtotal,
                'discount'       => $order->discount,
                'tax'            => $order->tax,
                'final_amount'   => $order->total,
                'payment_method' => $order->payment_method,
                'status'         => 'paid',
            ]);

                $productIds = $order->items->pluck('product_id')->filter()->unique()->values();

                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($order->items as $item) {
                    SaleItem::create([
                        'tenant_id'  => $order->tenant_id,
                        'sale_id'    => $sale->id,
                        'product_id' => $item->product_id,
                        'item_type'  => 'product',
                        'quantity'   => $item->quantity,
                        'price'      => $item->unit_price,
                        'subtotal'   => $item->line_total,
                    ]);

                    if (! $item->product_id) {
                        continue;
                    }

                    $product = $products->get($item->product_id);

                    if (! $product) {
                        continue;
                    }

                    $before = (int) $product->stock;
                    $after  = max(0, $before - $item->quantity);

                    $product->update(['stock' => $after]);

                    Inventory::create([
                        'tenant_id'      => $order->tenant_id,
                        'product_id'     => $product->id,
                        'type'           => 'stock_out',
                        'quantity'       => -$item->quantity,
                        'before_stock'   => $before,
                        'after_stock'    => $after,
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'remarks'        => "Order {$order->order_number} completed",
                    ]);
                }

                $order->update([
                    'status'       => OrderStatus::Completed,
                    'completed_at' => now(),
                    'processed_by' => Auth::id(),
                    'sale_id'      => $sale->id,
                ]);
            });

            unset($this->orders, $this->counts);
            $this->dispatch('notify', type: 'success', message: 'Order completed. Sale recorded and stock updated.');
       } catch (\RuntimeException $e) {
    $this->dispatch('notify', type: 'error', message: 'Order is not ready to complete.');
} catch (\Throwable $e) {
    \Log::error('ORDER COMPLETE FAILED', [
        'order_id' => $orderId,
        'message'  => $e->getMessage(),
        'file'     => $e->getFile(),
        'line'     => $e->getLine(),
    ]);
    $this->dispatch('notify', type: 'error', message: 'Could not complete order.');
}
    }
};
?>

