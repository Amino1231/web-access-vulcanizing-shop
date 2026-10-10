<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCancelledByCustomer extends Notification
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
            'kind'         => 'order.cancelled_by_customer',
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name'=> $this->order->customer?->name,
            'url'          => route('owner.order_management'),
            'title'        => 'Order cancelled by customer',
            'message'      => ($this->order->customer?->name ?? 'A customer')
                              . ' cancelled ' . $this->order->order_number . '.',
        ];
    }
}