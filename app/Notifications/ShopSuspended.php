<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShopSuspended extends Notification
{
    use Queueable;

    public function __construct(public Tenant $tenant) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'        => 'shop.suspended',
            'tenant_id'   => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'url'         => route('owner.business_status'),
            'title'       => 'Your shop was suspended',
            'message'     => 'Contact support to appeal this decision.',
        ];
    }
}