<?php

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.customer')] class extends Component
{
    use WithPagination;

    #[Url(history: true)] public string $search = '';
    #[Url(as: 'tenant', history: true)] public string $tenantFilter = '';

    public int $perPage = 12;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingTenantFilter(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->tenantFilter = '';
        $this->resetPage();
    }

    #[Computed]
    public function services()
    {
        return Service::query()
            // Eager load the published post to avoid N+1 queries in the view
            ->with([
                'category:id,name', 
                'tenant:id,name,is_active,business_hours,business_setup_completed',
                'post' => fn($q) => $q->where('status', 'published')->whereNull('archived_at')
            ])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->tenantFilter !== '', fn ($q) => $q->where('tenant_id', $this->tenantFilter))
            // ONLY show services that have a published, non-archived post
            ->whereHas('post', fn($q) => $q->where('status', 'published')->whereNull('archived_at'))
            ->orderBy('name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function tenants(): array
    {
        return \App\Models\Tenant::query()
            ->where('is_active', true)
            ->whereIn('id', fn ($q) => $q->select('tenant_id')->from('services')->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
            ->toArray();
    }
};