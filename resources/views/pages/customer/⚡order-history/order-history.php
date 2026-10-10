<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\OrderCancelledByCustomer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.customer')] class extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public int $perPage = 10;

    public ?int $viewingOrderId = null;
    public ?array $viewingOrder = null;

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    #[Computed]
    public function counts(): array
    {
        return Order::query()
            ->withoutGlobalScope('tenant')
            ->where('customer_id', Auth::id())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->withoutGlobalScope('tenant')
            ->with([
                'tenant:id,name',
                'items:id,order_id,product_id,product_name,variant_label,sku,quantity,unit_price,line_total',
                'sale:id,invoice_number',
            ])
            ->where('customer_id', Auth::id())
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate($this->perPage);
    }

    public function viewOrder(int $orderId): void
    {
        $order = Order::query()
            ->withoutGlobalScope('tenant')
            ->where('customer_id', Auth::id())
            ->with([
                'tenant:id,name,phone,address,email',
                'items.product:id,ft_img,slug',
                'sale:id,invoice_number,final_amount,payment_method,status',
            ])
            ->find($orderId);

        if (! $order) {
            $this->dispatch('notify', type: 'error', message: 'Order not found.');
            return;
        }

        $this->viewingOrderId = $order->id;
        $this->viewingOrder = [
            'id'              => $order->id,
            'order_number'    => $order->order_number,
            'status'          => $order->status->value,
            'status_label'    => $order->status->label(),
            'badge'           => $order->status->badge(),
            'subtotal'        => (float) $order->subtotal,
            'discount'        => (float) $order->discount,
            'tax'             => (float) $order->tax,
            'total'           => (float) $order->total,
            'payment_method'  => $order->payment_method,
            'notes'           => $order->notes,
            'cancellation_reason' => $order->cancellation_reason,
            'created_at'      => $order->created_at?->format('M d, Y g:i A'),
            'processed_at'    => $order->processed_at?->format('M d, Y g:i A'),
            'completed_at'    => $order->completed_at?->format('M d, Y g:i A'),
            'cancelled_at'    => $order->cancelled_at?->format('M d, Y g:i A'),
            'tenant' => [
                'name'    => $order->tenant?->name,
                'phone'   => $order->tenant?->phone,
                'address' => $order->tenant?->address,
                'email'   => $order->tenant?->email,
            ],
            'sale' => $order->sale ? [
                'invoice_number' => $order->sale->invoice_number,
                'status'         => $order->sale->status,
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
                    ? asset('storage/' . $item->product->ft_img)
                    : null,
            ])->toArray(),
        ];
    }

    public function closeOrderModal(): void
    {
        $this->viewingOrderId = null;
        $this->viewingOrder = null;
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()
            ->withoutGlobalScope('tenant')
            ->where('customer_id', Auth::id())
            ->find($orderId);

        if (! $order) return;

        // Customer can only cancel while still Pending.
        // Once the shop accepts (Processing), stock is deducted —
        // only the shop can cancel and restore inventory.
        if ($order->status !== OrderStatus::Pending) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Once a shop accepts your order, contact them directly to cancel.'
            );
            return;
        }

        $order->update([
            'status'              => OrderStatus::Cancelled,
            'cancelled_at'        => now(),
            'cancellation_reason' => 'Cancelled by customer',
        ]);

        // Notify the shop owner
        $order->tenant?->user?->notify(new OrderCancelledByCustomer($order));

        unset($this->orders, $this->counts);

        if ($this->viewingOrderId === $orderId) {
            $this->viewOrder($orderId);
        }

        $this->dispatch('notify', type: 'success', message: 'Order cancelled.');
    }
};