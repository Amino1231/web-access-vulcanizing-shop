<?php

use App\Enums\VerificationStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    public ?string $status = null;
    public ?string $rejectionReason = null;
    public ?array $missingRequirements = null;
    public bool $canSubmitAgain = false;

    #[Computed]
    public function tenant()
    {
        return Auth::user()?->currentTenant();
    }

    public function mount(): void
    {
        $tenant = $this->tenant;

        $this->status = $tenant?->verification_status instanceof VerificationStatus
            ? $tenant->verification_status->value
            : ($tenant?->verification_status ?? VerificationStatus::Pending->value);

        $this->rejectionReason = $tenant?->rejection_reason ?? null;

        // missing_requirements is already cast to array on the Tenant model — do NOT json_decode
        $this->missingRequirements = is_array($tenant?->missing_requirements)
            ? $tenant->missing_requirements
            : null;

        $this->canSubmitAgain = $this->status === VerificationStatus::Rejected->value;
    }

    public function retrySubmission(): void
    {
        $tenant = $this->tenant;

        if (! $tenant) {
            $this->redirectRoute('owner.business_setup', navigate: true);
            return;
        }

        $tenant->update([
            'verification_status'      => VerificationStatus::Pending->value,
            'rejection_reason'         => null,
            'missing_requirements'     => null,
            'submitted_at'             => now(),
            'business_setup_completed' => true,
            'is_active'                => false,
        ]);

        session()->flash('success', 'Your business profile has been resubmitted for review.');
        $this->redirectRoute('owner.business_setup', navigate: true);
    }
};