<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.customer')] class extends Component
{
    use WithPagination;

    #[Url(history: true)] public string $statusFilter = '';
    public int $perPage = 10;

    public function updatingStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function appointments()
    {
        return Appointment::query()
            ->with(['tenant:id,name', 'employee:id,name', 'vehicle:id,plate_number'])
            ->where('customer_id', Auth::id())
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('start_time')
            ->paginate($this->perPage);
    }

    public function cancelAppointment(int $appointmentId): void
    {
        $appointment = Appointment::query()
            ->where('customer_id', Auth::id())
            ->find($appointmentId);

        if (! $appointment) return;

        if (! in_array($appointment->status, [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed], true)) {
            $this->dispatch('notify', type: 'error', message: 'This appointment cannot be cancelled.');
            return;
        }

        $appointment->update([
            'status' => AppointmentStatus::Cancelled->value,
        ]);

        unset($this->appointments);
        $this->dispatch('notify', type: 'success', message: 'Appointment cancelled.');
    }
};
