<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobAcceptedByEmployee extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $jobType,
        public int    $jobId,
        public string $jobNumber,
        public string $mechanicName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $isRoadside = $this->jobType === 'roadside';

        return [
            'kind'    => 'job.accepted',
            'title'   => $isRoadside ? 'Roadside job accepted' : 'Appointment accepted',
            'message' => "{$this->mechanicName} accepted {$this->jobNumber}.",
            'url'     => route($isRoadside ? 'owner.roadside' : 'owner.appointments'),
            'job' => [
                'type'   => $this->jobType,
                'id'     => $this->jobId,
                'number' => $this->jobNumber,
            ],
            'actor' => ['name' => $this->mechanicName],
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}