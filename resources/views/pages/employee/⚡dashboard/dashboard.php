<?php

namespace App\Livewire\Employee;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\RoadSideRequest;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.employee')] #[Title('Dashboard · Employee')] class extends Component
{
    public ?Employee $employee = null;
    public ?Tenant $tenant = null;

    public array $stats = [
        'today'       => 0,
        'in_progress' => 0,
        'completed'   => 0,
        'roadside'    => 0,
    ];

    public $todayAppointments;
    public $activeRoadside;

    public array $knownAppointmentIds = [];
    public array $knownRoadsideIds    = [];
    public bool $bootstrapped         = false;

    public function mount(): void
    {
        $this->employee = Auth::user()?->currentEmployee();
        $this->tenant   = $this->employee?->tenant;

        $this->loadData();
        $this->bootstrapped = true;
    }

    public function loadData(): void
    {
        $this->stats = ['today' => 0, 'in_progress' => 0, 'completed' => 0, 'roadside' => 0];
        $this->todayAppointments = collect();
        $this->activeRoadside    = collect();

        if (! $this->employee || ! $this->tenant) {
            return;
        }

        $tenantId   = $this->employee->tenant_id;
        $employeeId = $this->employee->id;

        $base = Appointment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId);

        // ---- Stats ----
        $this->stats = [
            'today' => (clone $base)
                ->whereDate('start_time', today())
                ->count(),

            'in_progress' => (clone $base)
                ->whereIn('status', [
                    AppointmentStatus::Accepted->value,
                    AppointmentStatus::InProgress->value,
                ])
                ->count(),

            'completed' => (clone $base)
                ->where('status', AppointmentStatus::Completed->value)
                ->whereDate('updated_at', today())
                ->count(),

            'roadside' => RoadSideRequest::query()
                ->where('tenant_id', $tenantId)
                ->whereHas('assignments', fn ($q) => $q->where('employee_id', $employeeId))
                ->whereIn('status', ['pending', 'accepted'])
                ->count(),
        ];

        // ---- Today's appointments ----
        $this->todayAppointments = Appointment::query()
            ->where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->whereDate('start_time', today())
            ->with([
                'customer:id,name,phone',
                'service:id,name,duration_minutes',
            ])
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // ---- Active roadside ----
        $this->activeRoadside = RoadSideRequest::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('assignments', fn ($q) => $q->where('employee_id', $employeeId))
            ->whereIn('status', ['pending', 'accepted'])
            ->with(['customer:id,name,phone'])
            ->latest()
            ->limit(5)
            ->get();

        $this->detectAndNotifyNewAssignments();
    }

    protected function detectAndNotifyNewAssignments(): void
    {
        $freshApptIds = $this->todayAppointments->pluck('id')->map(fn ($id) => (int) $id)->all();
        $freshRoadIds = $this->activeRoadside->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($this->bootstrapped) {
            $newAppts = array_diff($freshApptIds, $this->knownAppointmentIds);
            $newRoads = array_diff($freshRoadIds, $this->knownRoadsideIds);

            if (count($newAppts) > 0) {
                $count = count($newAppts);
                $this->dispatch(
                    'notify',
                    type: 'success',
                    message: $count === 1
                        ? 'New appointment assigned to you.'
                        : "{$count} new appointments assigned to you.",
                );
            }

            if (count($newRoads) > 0) {
                $count = count($newRoads);
                $this->dispatch(
                    'notify',
                    type: 'error',
                    message: $count === 1
                        ? 'New roadside request assigned to you.'
                        : "{$count} new roadside requests assigned to you.",
                );
            }
        }

        $this->knownAppointmentIds = $freshApptIds;
        $this->knownRoadsideIds    = $freshRoadIds;
    }

    /**
     * Background poll — no toast, this is silent.
     */
    public function refresh(): void
    {
        $this->employee = Auth::user()?->currentEmployee();
        $this->tenant   = $this->employee?->tenant;
        $this->loadData();
    }

    /**
     * Manual refresh button — shows a toast.
     */
    public function refreshManually(): void
    {
        $this->refresh();
        $this->dispatch('notify', type: 'success', message: 'Dashboard refreshed.');
    }
};