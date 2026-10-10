<?php

use App\Enums\VerificationStatus;
use App\Support\OwnerDashboardMetrics;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    protected function metrics(): OwnerDashboardMetrics
    {
        return new OwnerDashboardMetrics($this->tenantId);
    }

    #[Computed]
    public function shopStatus(): ?array
    {
        $tenant = Auth::user()?->currentTenant();

        if (! $tenant) {
            return null;
        }

        $status = $tenant->verification_status instanceof VerificationStatus
            ? $tenant->verification_status->value
            : $tenant->verification_status;

        return [
            'status'               => $status,
            'label'                => $tenant->verification_status instanceof VerificationStatus
                ? $tenant->verification_status->label()
                : ucfirst(str_replace('_', ' ', $status)),
            'missing_requirements' => is_array($tenant->missing_requirements)
                ? $tenant->missing_requirements
                : [],
            'rejection_reason'     => $tenant->rejection_reason,
            'is_active'            => (bool) $tenant->is_active,
        ];
    }

    #[Computed]
    public function kpis(): array
    {
        return $this->tenantId
            ? $this->metrics()->kpis()
            : [];
    }

    #[Computed]
    public function revenueChart(): array
    {
        return $this->tenantId
            ? $this->metrics()->revenueChart()
            : ['labels' => [], 'series' => []];
    }

    #[Computed]
    public function orderDonut(): array
    {
        return $this->tenantId
            ? $this->metrics()->orderDonut()
            : [];
    }

    #[Computed]
    public function recentOrders(): \Illuminate\Support\Collection
    {
        return $this->tenantId
            ? $this->metrics()->recentOrders(8)
            : collect();
    }

    #[Computed]
    public function recentReviews(): \Illuminate\Support\Collection
    {
        return $this->tenantId
            ? $this->metrics()->recentReviews(5)
            : collect();
    }

    #[Computed]
    public function reviewSummary(): array
    {
        return $this->tenantId
            ? $this->metrics()->reviewSummary()
            : ['count' => 0, 'average' => 0];
    }
};