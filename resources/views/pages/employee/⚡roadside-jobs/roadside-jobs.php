<?php

use App\Models\Employee;
use App\Models\RoadSideRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.employee')] #[Title('Roadside Jobs · Employee')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = 'active';

    public int $perPage = 12;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

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
            return ['active' => 0, 'completed' => 0, 'all' => 0];
        }

        $base = RoadSideRequest::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->whereHas('assignments', fn ($q) => $q->where('employee_id', $scope['employee_id']));

        return [
            'active' => (clone $base)
                ->whereIn('status', ['assigned', 'accepted', 'arrived', 'in_progress'])
                ->count(),

            'completed' => (clone $base)
                ->where('status', 'completed')
                ->count(),

            'all' => (clone $base)->count(),
        ];
    }

    #[Computed]
    public function jobs()
    {
        $scope = $this->scopeIds;

        if (! $scope['employee_id'] || ! $scope['tenant_id']) {
            return RoadSideRequest::query()->whereRaw('0 = 1')->paginate($this->perPage);
        }

        return RoadSideRequest::query()
            ->where('tenant_id', $scope['tenant_id'])
            ->whereHas('assignments', fn ($q) => $q->where('employee_id', $scope['employee_id']))
            ->with([
                'customer:id,name,phone',
                'vehicle:id,brand,model,plate_number',
            ])
            ->when($this->statusFilter === 'active', fn ($q) => $q->whereIn('status', [
                'assigned', 'accepted', 'arrived', 'in_progress',
            ]))
            ->when($this->statusFilter === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('request_number', 'like', $term)
                        ->orWhere('location', 'like', $term)
                        ->orWhere('problem_type', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term));
                });
            })
            ->orderByRaw("CASE WHEN status IN ('assigned','accepted','arrived','in_progress') THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'active';
        $this->resetPage();
    }

    public function refresh(): void
    {
        unset($this->employee, $this->scopeIds, $this->counts, $this->jobs);
        $this->dispatch('notify', type: 'success', message: 'Roadside jobs refreshed.');
    }
};