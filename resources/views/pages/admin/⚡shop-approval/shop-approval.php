<?php

use App\Enums\VerificationStatus;
use App\Models\Tenant;
use App\Notifications\ShopApproved;
use App\Notifications\ShopNeedsMoreInfo;
use App\Notifications\ShopRejected;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public int $perPage = 15;

    /** Reject modal state */
    public ?int $rejectingId = null;
    public string $rejectingName = '';
    public string $rejectionReason = '';

    /** Needs-more-info modal state */
    public ?int $needsInfoId = null;
    public string $needsInfoName = '';
    public string $needsInfoReason = '';

    #[Computed]
    public function shops()
    {
        return Tenant::query()
            ->with('user:id,name,email')
            ->whereIn('verification_status', [
                VerificationStatus::Pending->value,
                VerificationStatus::UnderReview->value,
                VerificationStatus::NeedsMoreInfo->value,
                VerificationStatus::Rejected->value,
            ])
            ->orderByRaw("
                CASE verification_status
                    WHEN 'pending' THEN 1
                    WHEN 'under_review' THEN 2
                    WHEN 'needs_more_info' THEN 3
                    WHEN 'rejected' THEN 4
                    ELSE 5
                END
            ")
            ->orderByDesc('submitted_at')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function counts(): array
    {
        $rows = Tenant::query()
            ->selectRaw('verification_status, COUNT(*) as total')
            ->whereIn('verification_status', [
                VerificationStatus::Pending->value,
                VerificationStatus::UnderReview->value,
                VerificationStatus::NeedsMoreInfo->value,
                VerificationStatus::Rejected->value,
            ])
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status')
            ->toArray();

        return [
            'pending'         => (int) ($rows['pending'] ?? 0),
            'under_review'    => (int) ($rows['under_review'] ?? 0),
            'needs_more_info' => (int) ($rows['needs_more_info'] ?? 0),
            'rejected'        => (int) ($rows['rejected'] ?? 0),
            'total'           => array_sum($rows),
        ];
    }

    protected function statusLabel(?string $value): string
    {
        return VerificationStatus::tryFrom($value ?? '')?->label() ?? 'Pending';
    }

    // ─────────────────────────────────────────
    // Approve
    // ─────────────────────────────────────────

    public function approve(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);

        $tenant->update([
            'verification_status'      => VerificationStatus::Approved->value,
            'rejection_reason'         => null,
            'missing_requirements'     => null,
            'is_active'                => true,
            'business_setup_completed' => true,
            'approved_at'              => now(),
        ]);

        $tenant->user?->notify(new ShopApproved($tenant));

        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} approved. Now visible in Approved shops.");
    }

    // ─────────────────────────────────────────
    // Needs more info (with reason modal)
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

        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} marked as needing more info.");
        $this->cancelNeedsInfo();
    }

    // ─────────────────────────────────────────
    // Reject (with reason modal)
    // ─────────────────────────────────────────

    public function openReject(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $this->rejectingId     = $tenant->id;
        $this->rejectingName   = $tenant->name;
        $this->rejectionReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId     = null;
        $this->rejectingName   = '';
        $this->rejectionReason = '';
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'rejectionReason.required' => 'Please explain why this shop was rejected.',
            'rejectionReason.min'      => 'Give the owner at least 10 characters of guidance.',
        ]);

        $tenant = Tenant::findOrFail($this->rejectingId);

        $tenant->update([
            'verification_status'      => VerificationStatus::Rejected->value,
            'rejection_reason'         => $this->rejectionReason,
            'missing_requirements'     => [$this->rejectionReason],
            'is_active'                => false,
            'business_setup_completed' => true,
        ]);

        $tenant->user?->notify(new ShopRejected($tenant, $this->rejectionReason));

        unset($this->shops, $this->counts);

        session()->flash('success', "{$tenant->name} rejected. The owner has been notified.");
        $this->cancelReject();
    }
};