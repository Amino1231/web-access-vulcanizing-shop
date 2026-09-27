<?php

use App\Enums\OrderStatus;
use App\Models\Order;
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

    public function updatingStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->withoutGlobalScope('tenant')
            ->with([
                'tenant:id,name',
                'items:id,order_id,product_id,product_name,variant_label,quantity,unit_price,line_total',
            ])
            ->where('customer_id', Auth::id())
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate($this->perPage);
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()
            ->withoutGlobalScope('tenant')
            ->where('customer_id', Auth::id())
            ->find($orderId);

        if (! $order || ! $order->status->canBeCancelled()) {
            $this->dispatch('notify', type: 'error', message: 'Cannot cancel this order.');
            return;
        }

        $order->update([
            'status'              => OrderStatus::Cancelled,
            'cancelled_at'        => now(),
            'cancellation_reason' => 'Cancelled by customer',
        ]);

        unset($this->orders);
        $this->dispatch('notify', type: 'success', message: 'Order cancelled.');
    }
};
?>
