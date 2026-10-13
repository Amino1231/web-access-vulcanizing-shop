<?php
// resources/views/livewire/pages/employee/roadside-job-detail.blade.php

use App\Models\Employee;
use App\Models\RoadSideRequest;
use App\Notifications\JobAcceptedByEmployee;
use App\Notifications\JobCompletedByEmployee;
use App\Notifications\RoadsideStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.employee')] #[Title('Roadside Job · Employee')] class extends Component
{
    public RoadSideRequest $job;

    public string $notes = '';

    public function mount(RoadSideRequest $job): void
    {
        // Authorization: employee must be assigned to this job
        $employee = Auth::user()?->currentEmployee();
        abort_unless($employee, 403);

        $assigned = $job->assignments()->where('employee_id', $employee->id)->exists();
        abort_unless($assigned, 403);

        $this->job = $job->load([
            'customer:id,name,phone,email',
            'vehicle:id,brand,model,plate_number,vehicle_type',
        ]);

        $this->notes = (string) ($job->notes ?? '');
    }

    #[Computed]
    public function employee(): ?Employee
    {
        return Auth::user()?->currentEmployee();
    }

    public function accept(): void
    {
        $this->applyStatus('accepted', 'Help is on the way.');
    }

    public function markArrived(): void
    {
        $this->applyStatus('arrived', 'Marked as arrived.');
    }

    public function complete(): void
    {
        $this->validate(['notes' => 'nullable|string|max:2000']);

        try {
            DB::transaction(function () {
                $this->job->update([
                    'status' => 'completed',
                    'notes'  => $this->notes ?: $this->job->notes,
                ]);
            });

            $this->notifyOwnerCompleted();
            $this->notifyCustomer('completed');
            $this->job->refresh();

            $this->dispatch('notify', type: 'success', message: 'Roadside job completed.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not complete job.');
        }
    }

    protected function applyStatus(string $status, string $successMessage): void
    {
        $allowed = match ($status) {
            'accepted' => in_array($this->job->status, ['assigned', 'pending'], true),
            'arrived'  => $this->job->status === 'accepted',
            default    => false,
        };

        abort_unless($allowed, 403);

        try {
            DB::transaction(function () use ($status) {
                $this->job->update(['status' => $status]);
            });

            if ($status === 'accepted') {
                $this->notifyOwnerAccepted();
            }

            $this->notifyCustomer($status);
            $this->job->refresh();

            $this->dispatch('notify', type: 'success', message: $successMessage);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not update job.');
        }
    }

    // ─────────────────────────────────────────
    // Notification helpers
    // ─────────────────────────────────────────

    protected function notifyOwnerAccepted(): void
    {
        $owner = $this->job->tenant?->owner;

        if (! $owner || ! class_exists(JobAcceptedByEmployee::class)) {
            return;
        }

        $owner->notify(new JobAcceptedByEmployee(
            jobType:      'roadside',
            jobId:        $this->job->id,
            jobNumber:    $this->job->request_number,
            mechanicName: $this->employee?->name ?? 'An employee',
        ));
    }

    protected function notifyOwnerCompleted(): void
    {
        $owner = $this->job->tenant?->owner;

        if (! $owner) {
            return;
        }

        $owner->notify(new JobCompletedByEmployee(
            jobType:      'roadside',
            jobId:        $this->job->id,
            jobNumber:    $this->job->request_number,
            employeeName: $this->employee?->name ?? 'An employee',
        ));
    }

    protected function notifyCustomer(string $status): void
    {
        $this->job->customer?->notify(
            new RoadsideStatusChanged($this->job, $status)
        );
    }
};