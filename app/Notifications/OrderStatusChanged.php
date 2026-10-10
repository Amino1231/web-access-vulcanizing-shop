<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $status,
        public ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $title = match ($this->status) {
            'processing' => 'Order accepted',
            'ready'      => 'Order ready for pickup',
            'completed'  => 'Order completed',
            'cancelled'  => 'Order cancelled',
            default      => 'Order updated',
        };

        $message = match ($this->status) {
            'processing' => 'Your order ' . $this->order->order_number . ' has been accepted by the shop.',
            'ready'      => 'Your order ' . $this->order->order_number . ' is ready for pickup.',
            'completed'  => 'Your order ' . $this->order->order_number . ' is complete. Thanks!',
            'cancelled'  => 'Your order ' . $this->order->order_number . ' was cancelled.'
                            . ($this->reason ? ' Reason: ' . $this->reason : ''),
            default      => 'Your order status changed.',
        };

        return [
            'kind'         => 'order.status_changed',
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'status'       => $this->status,
            'url'          => route('customer.orders'),
            'title'        => $title,
            'message'      => $message,
        ];
    }
}