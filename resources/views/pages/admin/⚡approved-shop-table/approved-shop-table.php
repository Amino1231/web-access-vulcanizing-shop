<?php

use App\Enums\VerificationStatus;
use App\Models\Tenant;
use App\Notifications\ShopSuspended;
use App\Support\AdminDashboardMetrics;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    /**
     * NOTE: `statusFilter` is intentionally removed.
     * This page shows ONLY approved shops. Pending / Under Review /
     * Needs Info / Rejected shops live in the Approval Queue.
     */

    #[Url(history: true)]
    public string $activeFilter = '';

    public int $perPage = 15;

    public ?int $confirmingActionId = null;
    public ?string $confirmingAction = null;
    public ?string $confirmingName = null;

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingActiveFilter(): void { $this->resetPage(); }

    public ?int $viewingTenantId = null;
    public ?array $viewingTenant = null;

    public function viewTenant(int $tenantId): void
    {
        $detail = app(AdminDashboardMetrics::class)->tenantDetail($tenantId);

        if (! $detail) {
            session()->flash('error', 'Shop not found.');
            return;
        }

        $this->viewingTenantId = $tenantId;
        $this->viewingTenant = $detail;
    }

    public function closeTenantModal(): void
    {
        $this->viewingTenantId = null;
        $this->viewingTenant = null;
    }

    public function suspendFromModal(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $tenant->update([
            'verification_status' => VerificationStatus::Suspended->value,
            'is_active' => false,
        ]);

        $tenant->user?->notify(new ShopSuspended($tenant));

        $this->closeTenantModal();
        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} suspended.");
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->activeFilter = '';
        $this->resetPage();
    }

    #[Computed]
    public function counts(): array
    {
        // Only the total matters here — the other states belong to the approval queue
        return [
            'all' => Tenant::where('verification_status', VerificationStatus::Approved->value)->count(),
        ];
    }

    #[Computed]
    public function shops()
    {
        return Tenant::query()
            ->with(['user:id,name,email,phone'])
            // Approved only — this page is dedicated to the approved roster
            ->where('verification_status', VerificationStatus::Approved->value)
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term));
                });
            })
            ->when($this->activeFilter !== '', fn ($q) => $q->where('is_active', $this->activeFilter === 'active'))
            ->latest()
            ->paginate($this->perPage);
    }

    public function confirmToggleActive(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $this->confirmingActionId = $tenant->id;
        $this->confirmingAction = $tenant->is_active ? 'deactivate' : 'activate';
        $this->confirmingName = $tenant->name;
    }

    public function confirmSuspend(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $this->confirmingActionId = $tenant->id;
        $this->confirmingAction = 'suspend';
        $this->confirmingName = $tenant->name;
    }

    public function cancelAction(): void
    {
        $this->confirmingActionId = null;
        $this->confirmingAction = null;
        $this->confirmingName = null;
    }

    public function performAction(): void
    {
        if (! $this->confirmingActionId || ! $this->confirmingAction) return;

        $tenant = Tenant::find($this->confirmingActionId);
        if (! $tenant) {
            $this->cancelAction();
            return;
        }

        match ($this->confirmingAction) {
            'activate'   => $tenant->update(['is_active' => true]),
            'deactivate' => $tenant->update(['is_active' => false]),
            'suspend'    => $tenant->update([
                'verification_status' => VerificationStatus::Suspended->value,
                'is_active' => false,
            ]),
            default => null,
        };

        if ($this->confirmingAction === 'suspend') {
            $tenant->user?->notify(new ShopSuspended($tenant));
        }

        unset($this->shops, $this->counts);

        $message = match ($this->confirmingAction) {
            'activate'   => "{$tenant->name} activated.",
            'deactivate' => "{$tenant->name} deactivated.",
            'suspend'    => "{$tenant->name} suspended.",
            default      => 'Done.',
        };

        session()->flash('success', $message);
        $this->cancelAction();
    }
};