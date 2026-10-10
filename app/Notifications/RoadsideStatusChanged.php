<?php

namespace App\Notifications;

use App\Models\RoadSideRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoadsideStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public RoadSideRequest $request,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = match ($this->status) {
            'accepted'  => 'Help is on the way',
            'arrived'   => 'Employee has arrived',
            'completed' => 'Request completed',
            default     => 'Request updated',
        };

        return [
            'kind'           => 'roadside.status_changed',
            'request_id'     => $this->request->id,
            'request_number' => $this->request->request_number,
            'status'         => $this->status,
            'url'            => route('customer.map'),
            'title'          => $label,
            'message'        => 'Your request ' . $this->request->request_number . ': ' . $label,
        ];
    }
}