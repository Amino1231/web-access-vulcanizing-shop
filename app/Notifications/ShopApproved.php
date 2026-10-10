<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShopApproved extends Notification
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
            'kind'        => 'shop.approved',
            'tenant_id'   => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'url'         => route('owner.dashboard'),
            'title'       => 'Your shop was approved',
            'message'     => $this->tenant->name . ' is now live on the platform.',
        ];
    }
}