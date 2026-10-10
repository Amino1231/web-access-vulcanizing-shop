<?php

use App\Models\Employee;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    public int $employeeId;

    public string $employeeName = '';
    public string $employeePosition = '';

    /**
     * Keyed by ISO day (1=Monday ... 7=Sunday).
     * Each entry: ['enabled' => bool, 'start' => 'HH:MM', 'end' => 'HH:MM']
     */
    public array $schedules = [];

    public array $dayLabels = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    public function mount(int $employee): void
    {
        $tenantId = Auth::user()?->currentTenant()?->id;

        if (! $tenantId) {
            abort(404);
        }

        $employeeModel = Employee::where('tenant_id', $tenantId)->find($employee);

        if (! $employeeModel) {
            abort(404, 'Employee not found.');
        }

        $this->employeeId       = $employeeModel->id;
        $this->employeeName     = $employeeModel->name;
        $this->employeePosition = $employeeModel->position;

        $this->schedules = $this->defaultSchedules();

        $existing = Schedule::where('employee_id', $employeeModel->id)
            ->get()
            ->keyBy('day_of_week');

        foreach ($existing as $day => $row) {
            if (! isset($this->schedules[$day])) continue;

            $this->schedules[$day]['enabled'] = true;
            $this->schedules[$day]['start']   = substr((string) $row->start_time, 0, 5);
            $this->schedules[$day]['end']     = substr((string) $row->end_time, 0, 5);
        }
    }

    protected function defaultSchedules(): array
    {
        $defaults = [];

        foreach (range(1, 7) as $day) {
            $defaults[$day] = [
                'enabled' => $day !== 7,
                'start'   => '09:00',
                'end'     => '18:00',
            ];
        }

        return $defaults;
    }

    #[Computed]
    public function totalHours(): float
    {
        $total = 0.0;

        foreach ($this->schedules as $data) {
            if (empty($data['enabled'])) continue;

            $start = $this->normalizeTime($data['start'] ?? null);
            $end   = $this->normalizeTime($data['end'] ?? null);

            if (! $start || ! $end || $start >= $end) continue;

            [$sh, $sm] = array_map('intval', explode(':', $start));
            [$eh, $em] = array_map('intval', explode(':', $end));

            $total += (($eh * 60) + $em) - (($sh * 60) + $sm);
        }

        return round($total / 60, 1);
    }

    public function save(): void
    {
        $this->resetValidation();

        $this->validate([
            'schedules'           => ['required', 'array'],
            'schedules.*.enabled' => ['boolean'],
            'schedules.*.start'   => ['nullable', 'string'],
            'schedules.*.end'     => ['nullable', 'string'],
        ]);

        $tenantId = Auth::user()?->currentTenant()?->id;
        if (! $tenantId) {
            $this->addError('general', 'No active shop.');
            return;
        }

        $employee = Employee::where('tenant_id', $tenantId)->find($this->employeeId);
        if (! $employee) {
            $this->addError('general', 'Employee not found.');
            return;
        }

        // Normalize + validate
        foreach ($this->schedules as $day => $data) {
            if (empty($data['enabled'])) continue;

            $start = $this->normalizeTime($data['start'] ?? null);
            $end   = $this->normalizeTime($data['end'] ?? null);

            if (! $start || ! $end) {
                $this->addError("schedules.{$day}.end", 'Both times are required.');
                return;
            }

            if ($start >= $end) {
                $this->addError("schedules.{$day}.end", 'End time must be after start.');
                return;
            }

            $this->schedules[$day]['start'] = $start;
            $this->schedules[$day]['end']   = $end;
        }

        try {
            DB::transaction(function () use ($employee, $tenantId) {
                Schedule::where('employee_id', $employee->id)->delete();

                foreach ($this->schedules as $day => $data) {
                    if (empty($data['enabled'])) continue;

                    Schedule::create([
                        'tenant_id'   => $tenantId,
                        'employee_id' => $employee->id,
                        'day_of_week' => (int) $day,
                        'start_time'  => $data['start'],
                        'end_time'    => $data['end'],
                    ]);
                }
            });
        } catch (\Throwable $e) {
            $this->addError('general', 'Save failed: ' . $e->getMessage());
            return;
        }

        unset($this->totalHours);

        session()->flash('success', "{$employee->name}'s schedule saved.");
    }

    public function resetToDefault(): void
    {
        $this->schedules = $this->defaultSchedules();
        unset($this->totalHours);
        $this->resetValidation();
    }

    public function clearAll(): void
    {
        foreach ($this->schedules as $day => $data) {
            $this->schedules[$day]['enabled'] = false;
        }
        unset($this->totalHours);
    }

    protected function normalizeTime(?string $t): ?string
    {
        if (! $t) return null;

        $t = trim($t);

        if (preg_match('/^\d:\d{2}$/', $t)) {
            $t = '0' . $t;
        }

        if (preg_match('/^(\d{2}:\d{2}):\d{2}$/', $t, $m)) {
            $t = $m[1];
        }

        if (! preg_match('/^\d{2}:\d{2}$/', $t)) {
            return null;
        }

        [$h, $m] = explode(':', $t);
        if ((int) $h > 23 || (int) $m > 59) return null;

        return $t;
    }
};