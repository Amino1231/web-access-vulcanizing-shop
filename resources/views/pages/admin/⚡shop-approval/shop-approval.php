<?php

use App\Enums\VerificationStatus;
use App\Models\Tenant;
use App\Notifications\ShopApproved;
use App\Notifications\ShopNeedsMoreInfo;
use App\Notifications\ShopRejected;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public array $shops = [];

    /** Reject modal state */
    public ?int $rejectingId = null;
    public string $rejectingName = '';
    public string $rejectionReason = '';

    /** Needs-more-info modal state */
    public ?int $needsInfoId = null;
    public string $needsInfoName = '';
    public string $needsInfoReason = '';

    public function mount(): void
    {
        $this->loadShops();
    }

    public function loadShops(): void
    {
        $this->shops = Tenant::query()
            ->with('user:id,name,email')
            ->whereIn('verification_status', [
                VerificationStatus::Pending->value,
                VerificationStatus::UnderReview->value,
                VerificationStatus::NeedsMoreInfo->value,
                VerificationStatus::Rejected->value,
            ])
            ->orderByRaw("FIELD(verification_status, 'pending', 'under_review', 'needs_more_info', 'rejected')")
            ->orderByDesc('submitted_at')
            ->get()
            ->map(function (Tenant $tenant) {
                return [
                    'id'                    => $tenant->id,
                    'shop_name'             => $tenant->name,
                    'owner_name'            => $tenant->user?->name ?? 'N/A',
                    'owner_email'           => $tenant->user?->email ?? 'N/A',
                    'status'                => $tenant->verification_status ?? VerificationStatus::Pending->value,
                    'status_label'          => $this->statusLabel($tenant->verification_status),
                    'address'               => $tenant->address ?? 'N/A',
                    'phone'                 => $tenant->phone ?? 'N/A',
                    'submitted_at'          => $tenant->submitted_at?->format('M d, Y') ?? '—',
                    'rejection_reason'      => $tenant->rejection_reason,
                    'missing_requirements'  => $tenant->missing_requirements ?? [],
                    'attachment'            => $this->firstAttachment($tenant->attachment),
                ];
            })
            ->toArray();
    }

    protected function firstAttachment($attachment): ?string
    {
        if (is_array($attachment)) {
            return $attachment[0] ?? null;
        }

        return $attachment ?: null;
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

        session()->flash('success', "{$tenant->name} approved.");
        $this->loadShops();
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
            'verification_status'      => VerificationStatus::NeedsMoreInfo->value,
            'rejection_reason'         => null,
            'missing_requirements'     => [$this->needsInfoReason],
            'is_active'                => false,
        ]);

        $tenant->user?->notify(new ShopNeedsMoreInfo($tenant, [$this->needsInfoReason]));

        session()->flash('success', "{$tenant->name} marked as needing more info.");
        $this->cancelNeedsInfo();
        $this->loadShops();
    }

    // ─────────────────────────────────────────
    // Reject (with reason modal)
    // ─────────────────────────────────────────

    public function openReject(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) return;

        $this->rejectingId   = $tenant->id;
        $this->rejectingName = $tenant->name;
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

        session()->flash('success', "{$tenant->name} rejected. The owner has been notified.");
        $this->cancelReject();
        $this->loadShops();
    }
};