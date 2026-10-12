<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        $this->order = $order->load([
            'customer:id,name,email,phone',
            'tenant:id,name,phone,address,email',
            'items:id,order_id,product_name,variant_label,sku,quantity,unit_price,line_total',
        ]);
    }
};