<?php

use App\Models\Employee;
use App\Models\RoadSideAssignment;
use App\Models\RoadSideRequest;
use App\Notifications\RoadsideStatusChanged;
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
    public string $statusFilter = 'pending';

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 12;

    /** Accept modal */
    public ?int $acceptingId = null;
    public ?string $acceptingNumber = null;
    public ?int $selectedEmployeeId = null;

    /** Confirm modal */
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
            return ['all' => 0, 'pending' => 0, 'active' => 0, 'completed' => 0];
        }

        $base = RoadSideRequest::where('tenant_id', $tenantId);

        return [
            'all'       => (clone $base)->count(),
            'pending'   => (clone $base)->where('status', 'pending')->count(),
            'active'    => (clone $base)->where('status', 'accepted')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];
    }

    #[Computed]
    public function employees()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return collect();
        }

        return Employee::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'position']);
    }

    #[Computed]
    public function requests()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return RoadSideRequest::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return RoadSideRequest::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'customer:id,name,email,phone',
                'vehicle:id,plate_number,brand,model,year_model',
                'assignments' => fn ($q) => $q->with('employee:id,name')->latest(),
            ])
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('request_number', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('problem_type', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c
                            ->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term))
                        ->orWhereHas('vehicle', fn ($v) => $v
                            ->where('plate_number', 'like', $term));
                });
            })
            ->when($this->statusFilter === 'pending', fn ($q) => $q->where('status', 'pending'))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('status', 'accepted'))
            ->when($this->statusFilter === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'accepted' THEN 2 WHEN 'completed' THEN 3 END")
            ->orderByDesc('requested_at')
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'pending';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Accept flow
    // ─────────────────────────────────────────

    public function openAccept(int $requestId): void
    {
        $req = RoadSideRequest::where('tenant_id', $this->tenantId())->find($requestId);
        if (! $req) return;

        $this->acceptingId        = $req->id;
        $this->acceptingNumber    = $req->request_number;
        $this->selectedEmployeeId = $this->employees->first()?->id;
        $this->resetValidation();
    }

    public function cancelAccept(): void
    {
        $this->acceptingId        = null;
        $this->acceptingNumber    = null;
        $this->selectedEmployeeId = null;
    }

    public function confirmAccept(): void
    {
        $this->validate([
            'selectedEmployeeId' => ['required', 'integer', 'exists:employees,id'],
        ], [
            'selectedEmployeeId.required' => 'Pick an employee to assign.',
        ]);

        $req = RoadSideRequest::where('tenant_id', $this->tenantId())->find($this->acceptingId);
        if (! $req) {
            $this->cancelAccept();
            return;
        }

        $employee = Employee::where('tenant_id', $this->tenantId())
            ->where('id', $this->selectedEmployeeId)
            ->first();

        if (! $employee) {
            $this->cancelAccept();
            session()->flash('error', 'Employee not found.');
            return;
        }

        DB::transaction(function () use ($req, $employee) {
            $req->update([
                'status'      => 'accepted',
                'accepted_at' => now(),
            ]);

            RoadSideAssignment::create([
                'tenant_id'           => $this->tenantId(),
                'roadside_request_id' => $req->id,
                'employee_id'         => $employee->id,
                'assigned_at'         => now(),
                'status'              => 'assigned',
            ]);
        });

        $req->customer?->notify(new RoadsideStatusChanged($req, 'accepted'));

        unset($this->requests, $this->counts);

        session()->flash('success', "{$req->request_number} accepted and assigned to {$employee->name}.");
        $this->cancelAccept();
    }

    // ─────────────────────────────────────────
    // Assignment progress
    // ─────────────────────────────────────────

    public function confirmAction(int $requestId, string $action): void
    {
        $req = RoadSideRequest::where('tenant_id', $this->tenantId())->find($requestId);
        if (! $req) return;

        $this->confirmingId     = $req->id;
        $this->confirmingAction = $action;
        $this->confirmingLabel  = $req->request_number;
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

        $req = RoadSideRequest::where('tenant_id', $this->tenantId())
            ->with(['assignments' => fn ($q) => $q->latest()])
            ->find($this->confirmingId);

        if (! $req) {
            $this->cancelConfirmation();
            return;
        }

        $assignment = $req->assignments->first();
        $customerNotified = null;

        if ($this->confirmingAction === 'arrived') {
            $assignment?->update([
                'status'     => 'arrived',
                'arrival_at' => now(),
            ]);
            $customerNotified = 'arrived';
        } elseif ($this->confirmingAction === 'completed') {
            DB::transaction(function () use ($req, $assignment) {
                $assignment?->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
                $req->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
            });
            $customerNotified = 'completed';
        }

        if ($customerNotified) {
            $req->customer?->notify(new RoadsideStatusChanged($req, $customerNotified));
        }

        unset($this->requests, $this->counts);

        session()->flash('success', "{$req->request_number} updated.");
        $this->cancelConfirmation();
    }
};