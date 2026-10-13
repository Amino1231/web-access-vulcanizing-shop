<?php

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\RoadSideRequest;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.employee')] #[Title('Dashboard · Employee')] class extends Component
{
    /**
     * Currently logged-in employee (Employee row, not User).
     * Nullable — user may have role but no Employee row yet.
     */
    #[Computed]
    public function employee(): ?Employee
    {
        return Auth::user()?->currentEmployee();
    }

    #[Computed]
    public function tenant(): ?Tenant
    {
        return $this->employee?->tenant;
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
    public function stats(): array
    {
        $scope = $this->scopeIds;

        if (! $scope['employee_id'] || ! $scope['tenant_id']) {
            return ['today' => 0, 'in_progress' => 0, 'completed' => 0, 'roadside' => 0];
        }

        $base = Appointment::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->where('employee_id', $scope['employee_id']);

        return [
            'today' => (clone $base)
                ->whereDate('start_time', today())
                ->count(),

            'in_progress' => (clone $base)
                ->where('status', 'in_progress')
                ->count(),

            'completed' => (clone $base)
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
                ->count(),

            'roadside' => RoadSideRequest::query()
                ->where('tenant_id', $scope['tenant_id'])
                ->whereHas('assignments', fn ($q) => $q->where('employee_id', $scope['employee_id']))
                ->whereIn('status', ['assigned', 'accepted', 'arrived'])
                ->count(),
        ];
    }

    #[Computed]
    public function todayAppointments()
    {
        $scope = $this->scopeIds;

        if (! $scope['employee_id'] || ! $scope['tenant_id']) {
            return collect();
        }

        return Appointment::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->where('employee_id', $scope['employee_id'])
            ->whereDate('start_time', today())
            ->with([
                'customer:id,name,phone',
                'service:id,name,duration_minutes',
            ])
            ->orderBy('start_time')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function activeRoadside()
    {
        $scope = $this->scopeIds;

        if (! $scope['employee_id'] || ! $scope['tenant_id']) {
            return collect();
        }

        return RoadSideRequest::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->whereHas('assignments', fn ($q) => $q->where('employee_id', $scope['employee_id']))
            ->whereIn('status', ['assigned', 'accepted', 'arrived'])
            ->with(['customer:id,name,phone'])
            ->latest()
            ->limit(5)
            ->get();
    }

    public function refresh(): void
    {
        unset(
            $this->employee,
            $this->tenant,
            $this->scopeIds,
            $this->stats,
            $this->todayAppointments,
            $this->activeRoadside,
        );

        $this->dispatch('notify', type: 'success', message: 'Dashboard refreshed.');
    }
};