<?php

use App\Models\Employee;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = 'active';

    public int $perPage = 15;

    // ─────────────────────────────────────────
    // Form modal state
    // ─────────────────────────────────────────

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $position = '';

    public string $phone = '';

    public string $commissionRate = '0';

    public string $hiredAt = '';

    public bool $isActive = true;

    // ─────────────────────────────────────────
    // Schedule modal state
    // ─────────────────────────────────────────

    public bool $showSchedule = false;

    public ?int $scheduleEmployeeId = null;

    public string $scheduleEmployeeName = '';

    /**
     * Keyed by ISO day (1=Monday ... 7=Sunday).
     * Each entry: ['enabled' => bool, 'start' => 'HH:MM', 'end' => 'HH:MM']
     */
    public array $schedules = [];

    // ─────────────────────────────────────────
    // Delete modal state
    // ─────────────────────────────────────────

    public ?int $confirmingDeleteId = null;

    public ?string $confirmingDeleteName = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    // ─────────────────────────────────────────
    // Counts + list
    // ─────────────────────────────────────────

    #[Computed]
    public function counts(): array
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return ['all' => 0, 'active' => 0, 'inactive' => 0];
        }

        $base = Employee::where('tenant_id', $tenantId);

        return [
            'all' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
        ];
    }

    #[Computed]
    public function employees()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return Employee::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return Employee::query()
            ->where('tenant_id', $tenantId)
            ->withCount('schedules')
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('position', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'active';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Create / Edit
    // ─────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->name = '';
        $this->position = '';
        $this->phone = '';
        $this->commissionRate = '0';
        $this->hiredAt = now()->format('Y-m-d');
        $this->isActive = true;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $employee = Employee::where('tenant_id', $this->tenantId())->find($id);
        if (! $employee) {
            return;
        }

        $this->resetValidation();
        $this->editingId = $employee->id;
        $this->name = (string) $employee->name;
        $this->position = (string) $employee->position;
        $this->phone = (string) ($employee->phone ?? '');
        $this->commissionRate = (string) ($employee->commission_rate ?? '0');
        $this->hiredAt = $employee->hired_at?->format('Y-m-d') ?? '';
        $this->isActive = (bool) $employee->is_active;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'position' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'commissionRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hiredAt' => ['nullable', 'date'],
            'isActive' => ['boolean'],
        ]);

        $tenantId = $this->tenantId();
        if (! $tenantId) {
            $this->addError('general', 'No active shop.');

            return;
        }

        $data = [
            'name' => trim($this->name),
            'position' => trim($this->position),
            'phone' => trim($this->phone) ?: null,
            'commission_rate' => is_numeric($this->commissionRate) ? (float) $this->commissionRate : 0,
            'hired_at' => $this->hiredAt ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            $employee = Employee::where('tenant_id', $tenantId)->find($this->editingId);
            if (! $employee) {
                $this->closeForm();
                session()->flash('error', 'Employee not found.');

                return;
            }

            $employee->update($data);
            session()->flash('success', "{$employee->name} updated.");
        } else {
            $data['tenant_id'] = $tenantId;
            $employee = Employee::create($data);
            session()->flash('success', "{$employee->name} added to your team.");
        }

        unset($this->employees, $this->counts);
        $this->closeForm();
    }

    // ─────────────────────────────────────────
    // Toggle active
    // ─────────────────────────────────────────

    public function toggleActive(int $id): void
    {
        $employee = Employee::where('tenant_id', $this->tenantId())->find($id);
        if (! $employee) {
            return;
        }

        $employee->update(['is_active' => ! $employee->is_active]);
        unset($this->employees, $this->counts);

        $verb = $employee->is_active ? 'reactivated' : 'deactivated';
        session()->flash('success', "{$employee->name} {$verb}.");
    }

    // ─────────────────────────────────────────
    // Schedule modal
    // ─────────────────────────────────────────

    public function openSchedule(int $id): void
    {
        $employee = Employee::where('tenant_id', $this->tenantId())->find($id);
        if (! $employee) {
            return;
        }

        $this->resetValidation();
        $this->scheduleEmployeeId   = $employee->id;
        $this->scheduleEmployeeName = $employee->name;

        // Load from DB — clears all days, then enables only what's saved
        $this->loadScheduleFromDatabase();

        $this->showSchedule = true;
    }

    public function closeSchedule(): void
    {
        $this->showSchedule = false;
        $this->scheduleEmployeeId = null;
        $this->scheduleEmployeeName = '';
        $this->schedules = [];
        $this->resetValidation();
    }

    /**
     * Clear every day OFF, then flip on ONLY the days that exist in the DB.
     * Single source of truth for "what's the schedule right now".
     */
    protected function loadScheduleFromDatabase(): void
    {
        $this->schedules = $this->defaultSchedules();

        // Force every day OFF first
        foreach ($this->schedules as $day => $_) {
            $this->schedules[$day]['enabled'] = false;
        }

        if (! $this->scheduleEmployeeId) {
            return;
        }

        $existing = Schedule::where('employee_id', $this->scheduleEmployeeId)->get();

        foreach ($existing as $row) {
            $day = (int) $row->day_of_week;

            if (! isset($this->schedules[$day])) {
                continue;
            }

            $this->schedules[$day]['enabled'] = true;
            $this->schedules[$day]['start']   = substr((string) $row->start_time, 0, 5);
            $this->schedules[$day]['end']     = substr((string) $row->end_time, 0, 5);
        }
    }

    public function saveSchedule(): void
    {
        $this->resetValidation();

        $this->validate([
            'schedules' => ['required', 'array'],
            'schedules.*.enabled' => ['boolean'],
            'schedules.*.start' => ['nullable', 'string'],
            'schedules.*.end' => ['nullable', 'string'],
        ]);

        $tenantId = $this->tenantId();
        if (! $tenantId || ! $this->scheduleEmployeeId) {
            $this->addError('general', 'Missing employee or shop.');

            return;
        }

        $employee = Employee::where('tenant_id', $tenantId)->find($this->scheduleEmployeeId);
        if (! $employee) {
            $this->closeSchedule();
            session()->flash('error', 'Employee not found.');

            return;
        }

        // Normalize + validate start/end for enabled days
        foreach ($this->schedules as $day => $data) {
            if (empty($data['enabled'])) {
                continue;
            }

            $start = $this->normalizeTime($data['start'] ?? null);
            $end = $this->normalizeTime($data['end'] ?? null);

            if (! $start || ! $end) {
                $this->addError("schedules.{$day}.end", 'Both times are required.');

                return;
            }

            if ($start >= $end) {
                $this->addError("schedules.{$day}.end", 'End time must be after start.');

                return;
            }

            // Put normalized values back so we insert clean strings
            $this->schedules[$day]['start'] = $start;
            $this->schedules[$day]['end'] = $end;
        }

        try {
            DB::transaction(function () use ($employee, $tenantId) {
                Schedule::where('employee_id', $employee->id)->delete();

                foreach ($this->schedules as $day => $data) {
                    if (empty($data['enabled'])) {
                        continue;
                    }

                    Schedule::create([
                        'tenant_id' => $tenantId,
                        'employee_id' => $employee->id,
                        'day_of_week' => (int) $day,
                        'start_time' => $data['start'],
                        'end_time' => $data['end'],
                    ]);
                }
            });
        } catch (Throwable $e) {
            $this->addError('general', 'Save failed: '.$e->getMessage());

            return;
        }

        unset($this->employees, $this->counts);

        // Reload from DB so the form reflects the saved truth, then close
        $this->loadScheduleFromDatabase();
        $this->closeSchedule();

        session()->flash('success', "{$employee->name}'s schedule updated.");
    }

    /**
     * Turn "9:00", "09:00", "09:00:00" into "09:00".
     * Returns null if not a valid time.
     */
    protected function normalizeTime(?string $t): ?string
    {
        if (! $t) {
            return null;
        }

        $t = trim($t);

        // "9:00" → "09:00"
        if (preg_match('/^\d:\d{2}$/', $t)) {
            $t = '0'.$t;
        }

        // "09:00:00" → "09:00"
        if (preg_match('/^(\d{2}:\d{2}):\d{2}$/', $t, $m)) {
            $t = $m[1];
        }

        if (! preg_match('/^\d{2}:\d{2}$/', $t)) {
            return null;
        }

        [$h, $m] = explode(':', $t);
        if ((int) $h > 23 || (int) $m > 59) {
            return null;
        }

        return $t;
    }

    protected function defaultSchedules(): array
    {
        $defaults = [];

        // ISO: 1=Monday ... 7=Sunday
        foreach (range(1, 7) as $day) {
            $defaults[$day] = [
                'enabled' => $day !== 7,
                'start' => '09:00',
                'end' => '18:00',
            ];
        }

        return $defaults;
    }

    // ─────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $employee = Employee::where('tenant_id', $this->tenantId())->find($id);
        if (! $employee) {
            return;
        }

        $this->confirmingDeleteId = $employee->id;
        $this->confirmingDeleteName = $employee->name;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->confirmingDeleteName = null;
    }

    public function deleteEmployee(): void
    {
        if (! $this->confirmingDeleteId) {
            return;
        }

        $employee = Employee::where('tenant_id', $this->tenantId())
            ->find($this->confirmingDeleteId);

        if (! $employee) {
            $this->cancelDelete();

            return;
        }

        $name = $employee->name;
        $employee->delete();

        unset($this->employees, $this->counts);
        $this->cancelDelete();

        session()->flash('success', "{$name} removed from your team.");
    }
};