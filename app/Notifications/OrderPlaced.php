<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'         => 'order.placed',
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name'=> $this->order->customer?->name,
            'total'        => (float) $this->order->total,
            'url'          => route('owner.order_management'),
            'title'        => 'New order received',
            'message'      => ($this->order->customer?->name ?? 'A customer')
                              . ' placed ' . $this->order->order_number
                              . ' · ₱' . number_format($this->order->total, 2),
        ];
    }
}