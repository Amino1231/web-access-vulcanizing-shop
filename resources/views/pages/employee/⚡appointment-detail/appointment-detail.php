<?php
// resources/views/livewire/pages/employee/appointment-detail.blade.php

use App\Models\Appointment;
use App\Models\Employee;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\JobAcceptedByEmployee;
use App\Notifications\JobCompletedByEmployee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.employee')] #[Title('Appointment Detail · Employee')] class extends Component
{
    public Appointment $appointment;

    public string $notes = '';

    public function mount(Appointment $appointment): void
    {
        $this->authorize('view', $appointment);

        $this->appointment = $appointment->load([
            'customer:id,name,phone,email',
            'service:id,name,duration_minutes',
            'vehicle:id,brand,model,year_model,plate_number,vehicle_type',
            'employee:id,name,position',
        ]);

        $this->notes = (string) ($appointment->notes ?? '');
    }

    #[Computed]
    public function employee(): ?Employee
    {
        return Auth::user()?->currentEmployee();
    }

    public function accept(): void
    {
        $this->authorize('accept', $this->appointment);

        try {
            DB::transaction(function () {
                $this->appointment->update(['status' => 'accepted']);
            });

            $this->notifyOwnerAccepted();
            $this->notifyCustomer('accepted');

            unset($this->appointment);
            $this->appointment->refresh();

            $this->dispatch('notify', type: 'success', message: 'Job accepted.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not accept job.');
        }
    }

    public function start(): void
    {
        $this->authorize('start', $this->appointment);

        try {
            DB::transaction(function () {
                $this->appointment->update(['status' => 'in_progress']);
            });

            $this->notifyCustomer('in_progress');
            $this->appointment->refresh();

            $this->dispatch('notify', type: 'success', message: 'Job started.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not start job.');
        }
    }

    public function complete(): void
    {
        $this->authorize('complete', $this->appointment);

        $this->validate(['notes' => 'nullable|string|max:2000']);

        try {
            DB::transaction(function () {
                $this->appointment->update([
                    'status' => 'completed',
                    'notes'  => $this->notes ?: $this->appointment->notes,
                ]);
            });

            $this->notifyOwnerCompleted();
            $this->notifyCustomer('completed');
            $this->appointment->refresh();

            $this->dispatch('notify', type: 'success', message: 'Job completed.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not complete job.');
        }
    }

    public function cancel(): void
    {
        $this->authorize('cancel', $this->appointment);

        try {
            DB::transaction(function () {
                $this->appointment->update(['status' => 'cancelled']);
            });

            $this->notifyCustomer('cancelled');
            $this->appointment->refresh();

            $this->dispatch('notify', type: 'success', message: 'Job cancelled.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not cancel job.');
        }
    }

    // ─────────────────────────────────────────
    // Notification helpers
    // ─────────────────────────────────────────

    protected function notifyOwnerAccepted(): void
    {
        $owner = $this->appointment->tenant?->owner;

        if (! $owner) {
            return;
        }

        if (class_exists(JobAcceptedByEmployee::class)) {
            $owner->notify(new JobAcceptedByEmployee(
                jobType:      'appointment',
                jobId:        $this->appointment->id,
                jobNumber:    $this->appointment->appointment_number,
                mechanicName: $this->employee?->name ?? 'An employee',
            ));
        }
    }

    protected function notifyOwnerCompleted(): void
    {
        $owner = $this->appointment->tenant?->owner;

        if (! $owner) {
            return;
        }

        $owner->notify(new JobCompletedByEmployee(
            jobType:      'appointment',
            jobId:        $this->appointment->id,
            jobNumber:    $this->appointment->appointment_number,
            employeeName: $this->employee?->name ?? 'An employee',
        ));
    }

    protected function notifyCustomer(string $status): void
    {
        $this->appointment->customer?->notify(
            new AppointmentStatusChanged($this->appointment, $status)
        );
    }
};