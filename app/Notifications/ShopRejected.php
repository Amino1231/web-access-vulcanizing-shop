<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShopRejected extends Notification
{
    use Queueable;

    public function __construct(
        public Tenant $tenant,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'        => 'shop.rejected',
            'tenant_id'   => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'reason'      => $this->reason,
            'url'         => route('owner.business_setup'),
            'title'       => 'Your shop application was rejected',
            'message'     => $this->reason,
        ];
    }
}