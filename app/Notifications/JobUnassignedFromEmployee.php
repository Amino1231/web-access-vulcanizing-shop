<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobUnassignedFromEmployee extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $jobNumber,
        public string $reason = 'Reassigned to another mechanic.'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind'    => 'mechanic.job_removed',
            'title'   => 'Job reassigned',
            'message' => "{$this->jobNumber} — {$this->reason}",
            'url'     => route('mechanic.dashboard'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}