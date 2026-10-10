<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShopNeedsMoreInfo extends Notification
{
    use Queueable;

    public function __construct(
        public Tenant $tenant,
        public array $requirements = []
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'         => 'shop.needs_more_info',
            'tenant_id'    => $this->tenant->id,
            'tenant_name'  => $this->tenant->name,
            'requirements' => $this->requirements,
            'url'          => route('owner.business_setup'),
            'title'        => 'More info needed for ' . $this->tenant->name,
            'message'      => 'An admin needs additional details before approving your shop.',
        ];
    }
}