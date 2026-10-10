<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Post;
use App\Models\Review;
use App\Services\ServiceReviewService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public int $appointmentId;

    public function mount(int $appointment): void
    {
        $this->appointmentId = $appointment;
    }

    #[Computed]
    public function appointment(): Appointment
    {
        return Appointment::query()
            ->with([
                'tenant:id,name,phone,address',
                'employee:id,name,position',
                'vehicle:id,plate_number,brand,model',
                'service:id,name,service_category_id',
                'service.category:id,name',
            ])
            ->where('customer_id', Auth::id())
            ->findOrFail($this->appointmentId);
    }

    /**
     * The published post for this appointment's service (if any).
     * Used to link the "Rate service" CTA to the booking page where
     * the review form lives.
     */
    #[Computed]
    public function servicePost(): ?Post
    {
        $service = $this->appointment->service;
        if (! $service) return null;

        return Post::query()
            ->where('service_id', $service->id)
            ->where('status', 'published')
            ->whereNull('archived_at')
            ->first();
    }

    #[Computed]
    public function hasReviewedService(): bool
    {
        $user    = Auth::user();
        $service = $this->appointment->service;

        if (! $user || ! $service) return false;

        return Review::where('customer_id', $user->id)
            ->where('service_id', $service->id)
            ->exists();
    }

    #[Computed]
    public function isCompleted(): bool
    {
        return $this->appointment->status === AppointmentStatus::Completed;
    }
    
    #[Computed]
    public function canRateService(): bool
    {
        return $this->appointment->status === AppointmentStatus::Completed
            && $this->servicePost !== null
            && ! $this->hasReviewedService;
    }

    public function cancel(): void
    {
        $a = $this->appointment;

        if (! $a->status->canBeCancelled()) {
            $this->dispatch('notify', type: 'error', message: 'This appointment cannot be cancelled.');
            return;
        }

        $a->update(['status' => AppointmentStatus::Cancelled->value]);
        unset($this->appointment);
        $this->dispatch('notify', type: 'success', message: 'Appointment cancelled.');
    }
};