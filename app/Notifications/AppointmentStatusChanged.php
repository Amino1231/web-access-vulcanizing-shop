<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = ucfirst(str_replace('_', ' ', $this->status));

        return [
            'kind'              => 'appointment.status_changed',
            'appointment_id'    => $this->appointment->id,
            'appointment_number' => $this->appointment->appointment_number,
            'status'            => $this->status,
            'url'               => route('customer.appointment_detail', $this->appointment->id),
            'title'             => 'Appointment ' . $label,
            'message'           => 'Your booking ' . $this->appointment->appointment_number
                                    . ' is now ' . $label . '.',
        ];
    }
}