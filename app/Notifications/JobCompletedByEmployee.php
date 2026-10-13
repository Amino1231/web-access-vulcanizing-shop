<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobCompletedByEmployee extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $jobType,
        public int $jobId,
        public string $jobNumber,
        public string $employeeName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $isRoadside = $this->jobType === 'roadside';

        return [
            'kind'    => 'owner.job_completed',
            'title'   => $isRoadside ? 'Roadside job completed' : 'Appointment completed',
            'message' => "{$this->employeeName} marked {$this->jobNumber} as completed.",
            'url'     => route($isRoadside ? 'owner.roadside' : 'owner.appointments'),
            'job' => [
                'type'   => $this->jobType,
                'id'     => $this->jobId,
                'number' => $this->jobNumber,
            ],
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}