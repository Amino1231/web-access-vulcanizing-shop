<?php

use App\Enums\VerificationStatus;
use App\Models\Tenant;
use App\Notifications\ShopApproved;
use App\Notifications\ShopNeedsMoreInfo;
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

    #[Url(history: true)]
    public string $statusFilter = '';

    #[Url(history: true)]
    public string $activeFilter = '';

    public int $perPage = 15;

    public ?int $confirmingActionId = null;
    public ?string $confirmingAction = null;
    public ?string $confirmingName = null;

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingStatusFilter(): void  { $this->resetPage(); }
    public function updatingActiveFilter(): void  { $this->resetPage(); }

    public ?int $viewingTenantId = null;
    public ?array $viewingTenant = null;

    /** Needs-more-info sub-modal state */
    public ?int $needsInfoId = null;
    public string $needsInfoName = '';
    public string $needsInfoReason = '';

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

    public function approveFromModal(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $tenant->update([
            'verification_status' => VerificationStatus::Approved->value,
            'is_active' => true,
            'rejection_reason' => null,
            'missing_requirements' => null,
            'approved_at' => now(),
        ]);

        $tenant->user?->notify(new ShopApproved($tenant));

        $this->viewTenant($tenantId);
        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} approved.");
    }

    public function suspendFromModal(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $tenant->update([
            'verification_status' => 'suspended',
            'is_active' => false,
        ]);

        $tenant->user?->notify(new ShopSuspended($tenant));

        $this->viewTenant($tenantId);
        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} suspended.");
    }

    // ─────────────────────────────────────────
    // Needs more info
    // ─────────────────────────────────────────

    public function openNeedsInfo(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $this->needsInfoId     = $tenant->id;
        $this->needsInfoName   = $tenant->name;
        $this->needsInfoReason = '';
    }

    public function cancelNeedsInfo(): void
    {
        $this->needsInfoId     = null;
        $this->needsInfoName   = '';
        $this->needsInfoReason = '';
    }

    public function confirmNeedsInfo(): void
    {
        $this->validate([
            'needsInfoReason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'needsInfoReason.required' => 'Tell the owner what info you need.',
            'needsInfoReason.min'      => 'Give them at least 10 characters of guidance.',
        ]);

        $tenant = Tenant::findOrFail($this->needsInfoId);

        $tenant->update([
            'verification_status'  => VerificationStatus::NeedsMoreInfo->value,
            'rejection_reason'     => null,
            'missing_requirements' => [$this->needsInfoReason],
            'is_active'            => false,
        ]);

        $tenant->user?->notify(new ShopNeedsMoreInfo($tenant, [$this->needsInfoReason]));

        $this->cancelNeedsInfo();
        $this->viewTenant($tenant->id);
        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} marked as needing more info.");
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->activeFilter = '';
        $this->resetPage();
    }

    #[Computed]
    public function counts(): array
    {
        $rows = Tenant::query()
            ->selectRaw('verification_status, COUNT(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status')
            ->toArray();

        return [
            'all'             => array_sum($rows),
            'approved'        => (int) ($rows['approved'] ?? 0),
            'pending'         => (int) ($rows['pending'] ?? 0),
            'under_review'    => (int) ($rows['under_review'] ?? 0),
            'needs_more_info' => (int) ($rows['needs_more_info'] ?? 0),
            'rejected'        => (int) ($rows['rejected'] ?? 0),
            'suspended'       => (int) ($rows['suspended'] ?? 0),
        ];
    }

    #[Computed]
    public function shops()
    {
        return Tenant::query()
            ->with(['user:id,name,email,phone'])
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
            ->when($this->statusFilter !== '', fn ($q) => $q->where('verification_status', $this->statusFilter))
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
                'verification_status' => 'suspended',
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

    public function approve(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $tenant->update([
            'verification_status' => VerificationStatus::Approved->value,
            'is_active' => true,
            'rejection_reason' => null,
            'missing_requirements' => null,
            'approved_at' => now(),
        ]);

        $tenant->user?->notify(new ShopApproved($tenant));

        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} approved.");
    }

    public function approveAllPending(): void
    {
        $tenants = Tenant::whereIn('verification_status', [
            VerificationStatus::Pending->value,
            VerificationStatus::UnderReview->value,
        ])->with('user')->get();

        $count = Tenant::whereIn('verification_status', [
            VerificationStatus::Pending->value,
            VerificationStatus::UnderReview->value,
        ])->update([
            'verification_status' => VerificationStatus::Approved->value,
            'is_active' => true,
            'rejection_reason' => null,
            'missing_requirements' => null,
            'approved_at' => now(),
        ]);

        foreach ($tenants as $t) {
            $t->user?->notify(new ShopApproved($t));
        }

        unset($this->shops, $this->counts);

        session()->flash('success', "{$count} shop(s) approved.");
    }

};