<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.employee')] #[Title('My Appointments · Employee')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = 'upcoming';

    public int $perPage = 12;

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function employee(): ?Employee
    {
        return Auth::user()?->currentEmployee();
    }

    #[Computed]
    public function scopeIds(): array
    {
        $e = $this->employee;

        return [
            'employee_id' => $e?->id,
            'tenant_id'   => $e?->tenant_id,
        ];
    }

    #[Computed]
    public function counts(): array
    {
        $scope = $this->scopeIds;

        if (! $scope['employee_id'] || ! $scope['tenant_id']) {
            return ['upcoming' => 0, 'completed' => 0, 'all' => 0];
        }

        $base = Appointment::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->where('employee_id', $scope['employee_id']);

        return [
            'upcoming' => (clone $base)
                ->whereIn('status', [
                    AppointmentStatus::Scheduled->value,
                    AppointmentStatus::Assigned->value,
                    AppointmentStatus::Accepted->value,
                    AppointmentStatus::Confirmed->value,
                    AppointmentStatus::InProgress->value,
                ])
                ->count(),

            'completed' => (clone $base)
                ->where('status', AppointmentStatus::Completed->value)
                ->count(),

            'all' => (clone $base)->count(),
        ];
    }

    #[Computed]
    public function appointments()
    {
        $scope = $this->scopeIds;

        if (! $scope['employee_id'] || ! $scope['tenant_id']) {
            return Appointment::query()->whereRaw('0 = 1')->paginate($this->perPage);
        }

        return Appointment::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->where('employee_id', $scope['employee_id'])
            ->with([
                'customer:id,name,phone',
                'service:id,name,duration_minutes',
            ])
            ->when($this->statusFilter === 'upcoming', fn ($q) => $q->whereIn('status', [
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Assigned->value,
                AppointmentStatus::Accepted->value,
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::InProgress->value,
            ]))
            ->when($this->statusFilter === 'completed', fn ($q) => $q->where(
                'status', AppointmentStatus::Completed->value
            ))
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('appointment_number', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->orderByRaw("CASE
                WHEN status IN ('in_progress', 'accepted', 'assigned', 'confirmed', 'scheduled') THEN 0
                ELSE 1
            END")
            ->orderBy('start_time')
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'upcoming';
        $this->resetPage();
    }

    public function refresh(): void
    {
        unset($this->employee, $this->scopeIds, $this->counts, $this->appointments);
        $this->dispatch('notify', type: 'success', message: 'Appointments refreshed.');
    }
};