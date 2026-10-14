<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Employee;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\JobAssignedToEmployee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    public ?int $confirmingId = null;
    public ?string $confirmingAction = null;
    public ?string $confirmingLabel = null;

    /** Assignment modal state */
    public ?int $assigningId = null;
    public ?string $assigningNumber = null;
    public ?int $assigningEmployeeId = null;

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
                    AppointmentStatus::Assigned->value,
                    AppointmentStatus::Accepted->value,
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
    public function employees()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) return collect();

        return Employee::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'position']);
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
                      AppointmentStatus::Assigned->value,
                      AppointmentStatus::Accepted->value,
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
            ->orderBy(
                'start_time',
                $this->statusFilter === 'completed' || $this->statusFilter === 'cancelled' ? 'desc' : 'asc'
            )
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'upcoming';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Assignment flow
    // ─────────────────────────────────────────

    public function openAssignModal(int $appointmentId): void
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) return;

        $appointment = Appointment::where('tenant_id', $tenantId)->find($appointmentId);
        if (! $appointment) return;

        $this->assigningId         = $appointment->id;
        $this->assigningNumber     = $appointment->appointment_number;
        $this->assigningEmployeeId = $appointment->employee_id;
        $this->resetValidation();
    }

    public function cancelAssign(): void
    {
        $this->assigningId         = null;
        $this->assigningNumber     = null;
        $this->assigningEmployeeId = null;
        $this->resetValidation();
    }

    public function assignEmployee(): void
    {
        $this->validate([
            'assigningEmployeeId' => ['required', 'integer', 'exists:employees,id'],
        ], [
            'assigningEmployeeId.required' => 'Pick an employee to assign.',
        ]);

        $tenantId = $this->tenantId();
        if (! $tenantId) return;

        $appointment = Appointment::with(['employee.user', 'service', 'tenant.user'])
            ->where('tenant_id', $tenantId)
            ->find($this->assigningId);

        if (! $appointment) {
            $this->cancelAssign();
            return;
        }

        $employee = Employee::where('tenant_id', $tenantId)
            ->where('id', $this->assigningEmployeeId)
            ->first();

        if (! $employee) {
            $this->cancelAssign();
            return;
        }

        $isNewAssignment = $appointment->employee_id !== $employee->id;

        // Save the assignment
        $update = ['employee_id' => $employee->id];

        // Transition to Assigned if it was still early-stage
        if (in_array($appointment->status, [
            AppointmentStatus::Scheduled,
            AppointmentStatus::Confirmed,
        ], true)) {
            $update['status'] = AppointmentStatus::Assigned;
        }

        $appointment->update($update);

        // Notify the employee ONLY if this is a new assignment
        if ($isNewAssignment) {
            $employee->user?->notify(new JobAssignedToEmployee(
                jobType: 'appointment',
                jobId: $appointment->id,
                jobNumber: $appointment->appointment_number,
                scheduledAt: $appointment->start_time->format('M d, g:i A'),
                serviceName: $appointment->service?->name ?? 'Service',
            ));
        }

        unset($this->appointments, $this->counts);

        session()->flash('success', "{$employee->name} assigned to {$appointment->appointment_number}.");
        $this->cancelAssign();
    }

    // ─────────────────────────────────────────
    // Confirmation flow
    // ─────────────────────────────────────────

    public function confirmAction(int $appointmentId, string $action): void
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) return;

        $appointment = Appointment::where('tenant_id', $tenantId)->find($appointmentId);
        if (! $appointment) return;

        $this->confirmingId     = $appointment->id;
        $this->confirmingAction = $action;
        $this->confirmingLabel  = $appointment->appointment_number;

        // Pre-select the currently assigned employee if any
        $this->assigningEmployeeId = $appointment->employee_id;
    }

    public function cancelConfirmation(): void
    {
        $this->confirmingId     = null;
        $this->confirmingAction = null;
        $this->confirmingLabel  = null;
        $this->resetValidation();
    }

    public function performAction(): void
    {
        if (! $this->confirmingId || ! $this->confirmingAction) return;

        $tenantId = $this->tenantId();
        if (! $tenantId) {
            $this->cancelConfirmation();
            return;
        }

        // The "confirm" action requires an employee to be picked
        if ($this->confirmingAction === 'confirm') {
            $this->validate([
                'assigningEmployeeId' => ['required', 'integer', 'exists:employees,id'],
            ], [
                'assigningEmployeeId.required' => 'Pick an employee to assign.',
            ]);
        }

        $appointment = Appointment::with(['employee.user', 'service', 'tenant.user'])
            ->where('tenant_id', $tenantId)
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

        if (! $newStatus) {
            $this->cancelConfirmation();
            return;
        }

        $update = ['status' => $newStatus];

        // On confirm, also persist the assigned employee
        if ($this->confirmingAction === 'confirm') {
            $update['employee_id'] = $this->assigningEmployeeId;
        }

        $appointment->update($update);

        // Notify the assigned employee on confirm/start
        if (in_array($newStatus, [
            AppointmentStatus::Confirmed,
            AppointmentStatus::InProgress,
        ], true)) {
            $employeeUser = $appointment->fresh('employee.user')->employee?->user;

            if ($employeeUser) {
                $employeeUser->notify(new JobAssignedToEmployee(
                    jobType: 'appointment',
                    jobId: $appointment->id,
                    jobNumber: $appointment->appointment_number,
                    scheduledAt: $appointment->start_time->format('M d, g:i A'),
                    serviceName: $appointment->service?->name ?? 'Service',
                ));
            }
        }

        // Notify the customer for every status change
        try {
            $appointment->customer?->notify(
                new AppointmentStatusChanged($appointment, $newStatus->value)
            );
        } catch (\Throwable $e) {
            Log::warning('Appointment notification failed', [
                'appointment_id' => $appointment->id,
                'error'          => $e->getMessage(),
            ]);
        }

        unset($this->appointments, $this->counts);

        session()->flash(
            'success',
            "Appointment {$appointment->appointment_number} marked as {$newStatus->label()}."
        );
        $this->cancelConfirmation();
    }
};