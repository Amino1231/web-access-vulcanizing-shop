<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobAssignedToEmployee extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $jobType,          // 'appointment' | 'roadside'
        public int $jobId,
        public string $jobNumber,
        public string $scheduledAt,      // human-readable
        public string $serviceName = '', // e.g. "Tire Replacement"
        public string $location = '',    // for roadside
        public string $urgency = 'normal' // 'normal' | 'urgent'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $isRoadside = $this->jobType === 'roadside';

        $title = $isRoadside
            ? 'New roadside job'
            : 'New appointment assigned';

        $message = $isRoadside
            ? "Emergency at {$this->location}. Scheduled: {$this->scheduledAt}."
            : "{$this->serviceName} on {$this->scheduledAt}.";

        return [
            'kind'    => 'employee.job_assigned',
            'title'   => $title,
            'message' => $message,
            'url'     => route('employee.dashboard'),
            'job' => [
                'type'   => $this->jobType,
                'id'     => $this->jobId,
                'number' => $this->jobNumber,
            ],
            'urgency' => $this->urgency,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}