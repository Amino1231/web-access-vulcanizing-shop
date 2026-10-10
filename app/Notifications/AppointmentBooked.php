<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentBooked extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'kind'              => 'appointment.booked',
            'appointment_id'    => $this->appointment->id,
            'appointment_number' => $this->appointment->appointment_number,
            'customer_name'     => $this->appointment->customer?->name,
            'start_time'        => $this->appointment->start_time?->toDateTimeString(),
            'url'               => route('owner.appointments'),
            'title'             => 'New appointment booked',
            'message'           => ($this->appointment->customer?->name ?? 'A customer')
                                    . ' booked for '
                                    . ($this->appointment->start_time?->format('M d, g:i A') ?? ''),
        ];
    }
}