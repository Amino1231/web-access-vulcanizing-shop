<?php

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

    public string $email = '';

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
            ->with('user:id,name,email')
            ->withCount('schedules')
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('position', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', $term));
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
        $this->email = '';
        $this->phone = '';
        $this->commissionRate = '0';
        $this->hiredAt = now()->format('Y-m-d');
        $this->isActive = true;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $employee = Employee::with('user')
            ->where('tenant_id', $this->tenantId())
            ->find($id);

        if (! $employee) {
            return;
        }

        $this->resetValidation();
        $this->editingId = $employee->id;
        $this->name = (string) $employee->name;
        $this->position = (string) $employee->position;
        $this->email = (string) ($employee->user?->email ?? '');
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
        $this->email = '';
        $this->resetValidation();
    }

    public function save(): void
    {
        // Look up the linked user (if any) *before* validation so we can
        // ignore their own email in the unique rule.
        $existingUserId = $this->editingId
            ? Employee::where('tenant_id', $this->tenantId())
                ->whereKey($this->editingId)
                ->value('user_id')
            : null;

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'position' => ['required', 'string', 'min:2', 'max:120'],
            'email' => [
                'nullable', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($existingUserId),
            ],
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

        $savedName = $data['name'];

        try {
            DB::transaction(function () use (&$data, $tenantId, $existingUserId) {
                $email = trim($this->email) ?: null;

                if ($email) {
                    if ($existingUserId) {
                        // Keep the linked user in sync (name + email).
                        $user = User::find($existingUserId);
                        if ($user) {
                            $user->update([
                                'name' => $data['name'],
                                'email' => $email,
                            ]);
                            // Self-heal: make sure the role is still attached.
                            if (! $user->hasRole('employee')) {
                                $user->assignRole('employee');
                            }
                        }
                    } else {
                        // Create a login-capable user for this employee.
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $email,
                            'password' => Hash::make('password'),
                            'is_active' => true,
                        ]);
                        $user->assignRole('employee');
                        $data['user_id'] = $user->id;
                    }
                } elseif ($existingUserId) {
                    // Email cleared — detach the user, employee stays.
                    // We deliberately do NOT remove the role here: the same
                    // user may still be an employee at another shop, or may
                    // legitimately hold other roles too.
                    $data['user_id'] = null;
                }

                if ($this->editingId) {
                    $employee = Employee::where('tenant_id', $tenantId)->find($this->editingId);
                    if (! $employee) {
                        throw new RuntimeException('Employee not found.');
                    }
                    $employee->update($data);
                } else {
                    $data['tenant_id'] = $tenantId;
                    Employee::create($data);
                }
            });
        } catch (Throwable $e) {
            $this->addError('general', 'Save failed: '.$e->getMessage());

            return;
        }

        unset($this->employees, $this->counts);

        $wasEditing = (bool) $this->editingId;
        $this->closeForm();

        session()->flash(
            'success',
            $wasEditing
                ? "{$savedName} updated."
                : "{$savedName} added to your team."
        );
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
        $this->scheduleEmployeeId = $employee->id;
        $this->scheduleEmployeeName = $employee->name;

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

    protected function loadScheduleFromDatabase(): void
    {
        $this->schedules = $this->defaultSchedules();

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
            $this->schedules[$day]['start'] = substr((string) $row->start_time, 0, 5);
            $this->schedules[$day]['end'] = substr((string) $row->end_time, 0, 5);
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

        $this->loadScheduleFromDatabase();
        $this->closeSchedule();

        session()->flash('success', "{$employee->name}'s schedule updated.");
    }

    protected function normalizeTime(?string $t): ?string
    {
        if (! $t) {
            return null;
        }

        $t = trim($t);

        if (preg_match('/^\d:\d{2}$/', $t)) {
            $t = '0'.$t;
        }

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
        $userId = $employee->user_id;

        try {
            DB::transaction(function () use ($employee, $userId) {
                // Drop schedules first so we don't leave orphans if the FK isn't cascading.
                Schedule::where('employee_id', $employee->id)->delete();

                $employee->delete();

                if ($userId) {
                    // Only remove the login if this was its last employee record.
                    $stillUsed = Employee::where('user_id', $userId)->exists();

                    if (! $stillUsed) {
                        User::whereKey($userId)->delete();
                    }
                }
            });
        } catch (Throwable $e) {
            $this->cancelDelete();
            session()->flash('error', 'Delete failed: '.$e->getMessage());

            return;
        }

        unset($this->employees, $this->counts);
        $this->cancelDelete();

        session()->flash('success', "{$name} removed from your team.");
    }
};
