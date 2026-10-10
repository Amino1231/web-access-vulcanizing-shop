<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Notifications\AppointmentStatusChanged;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $statusFilter = 'upcoming';

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 15;

    /** Confirmation modal state */
    public ?int $confirmingId = null;
    public ?string $confirmingAction = null;
    public ?string $confirmingLabel = null;

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    #[Computed]
    public function counts(): array
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return ['all' => 0, 'today' => 0, 'upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
        }

        $base = Appointment::where('tenant_id', $tenantId);

        return [
            'all'       => (clone $base)->count(),
            'today'     => (clone $base)->whereDate('start_time', today())->count(),
            'upcoming'  => (clone $base)
                ->where('start_time', '>=', now())
                ->whereIn('status', [
                    AppointmentStatus::Scheduled->value,
                    AppointmentStatus::Confirmed->value,
                    AppointmentStatus::InProgress->value,
                ])
                ->count(),
            'completed' => (clone $base)->where('status', AppointmentStatus::Completed->value)->count(),
            'cancelled' => (clone $base)->whereIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
            ])->count(),
        ];
    }

    #[Computed]
    public function appointments()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return Appointment::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return Appointment::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'customer:id,name,email,phone',
                'service:id,name,price,duration_minutes',
                'vehicle:id,plate_number,brand,model',
                'employee:id,name',
            ])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('appointment_number', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('phone', 'like', $term))
                        ->orWhereHas('vehicle', fn ($v) => $v
                            ->where('plate_number', 'like', $term)
                            ->orWhere('brand', 'like', $term)
                            ->orWhere('model', 'like', $term));
                });
            })
            ->when($this->statusFilter === 'today', fn ($q) =>
                $q->whereDate('start_time', today())
            )
            ->when($this->statusFilter === 'upcoming', fn ($q) =>
                $q->where('start_time', '>=', now())
                  ->whereIn('status', [
                      AppointmentStatus::Scheduled->value,
                      AppointmentStatus::Confirmed->value,
                      AppointmentStatus::InProgress->value,
                  ])
            )
            ->when($this->statusFilter === 'completed', fn ($q) =>
                $q->where('status', AppointmentStatus::Completed->value)
            )
            ->when($this->statusFilter === 'cancelled', fn ($q) =>
                $q->whereIn('status', [
                    AppointmentStatus::Cancelled->value,
                    AppointmentStatus::NoShow->value,
                ])
            )
            ->orderBy('start_time', $this->statusFilter === 'completed' || $this->statusFilter === 'cancelled' ? 'desc' : 'asc')
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'upcoming';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Confirmation flow
    // ─────────────────────────────────────────

    public function confirmAction(int $appointmentId, string $action): void
    {
        $appointment = Appointment::where('tenant_id', $this->tenantId())->find($appointmentId);
        if (! $appointment) return;

        $this->confirmingId     = $appointment->id;
        $this->confirmingAction = $action;
        $this->confirmingLabel  = $appointment->appointment_number;
    }

    public function cancelConfirmation(): void
    {
        $this->confirmingId     = null;
        $this->confirmingAction = null;
        $this->confirmingLabel  = null;
    }

    public function performAction(): void
    {
        if (! $this->confirmingId || ! $this->confirmingAction) return;

        $appointment = Appointment::where('tenant_id', $this->tenantId())
            ->find($this->confirmingId);
        if (! $appointment) {
            $this->cancelConfirmation();
            return;
        }

        $newStatus = match ($this->confirmingAction) {
            'confirm'  => AppointmentStatus::Confirmed,
            'start'    => AppointmentStatus::InProgress,
            'complete' => AppointmentStatus::Completed,
            'cancel'   => AppointmentStatus::Cancelled,
            'no_show'  => AppointmentStatus::NoShow,
            default    => null,
        };

        if ($newStatus) {
            $appointment->update(['status' => $newStatus->value]);

            $appointment->customer?->notify(
                new AppointmentStatusChanged($appointment, $newStatus->value)
            );
        }

        unset($this->appointments, $this->counts);

        session()->flash('success', "Appointment {$appointment->appointment_number} marked as {$newStatus?->label()}.");
        $this->cancelConfirmation();
    }
};