<?php

namespace App\Notifications;

use App\Models\RoadSideRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoadsideRequested extends Notification
{
    use Queueable;

    public function __construct(public RoadSideRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'         => 'roadside.requested',
            'request_id'   => $this->request->id,
            'request_number' => $this->request->request_number,
            'customer_name' => $this->request->customer?->name,
            'problem_type' => $this->request->problem_type,
            'url'          => route('owner.roadside'),
            'title'        => 'New roadside request',
            'message'      => ($this->request->customer?->name ?? 'A customer')
                              . ' needs help: ' . ($this->request->problem_type ?? 'assistance'),
        ];
    }
}