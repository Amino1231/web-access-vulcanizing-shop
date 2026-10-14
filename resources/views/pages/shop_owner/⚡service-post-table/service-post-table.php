<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $activeFilter = '';
    public string $statusFilter = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public ?int $deletingId = null;
    public ?string $deletingName = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingActiveFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        $allowed = ['name', 'price', 'duration_minutes', 'created_at'];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->activeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    #[Computed]
    public function categories(): array
    {
        if (! $this->tenantId) {
            return [];
        }

        return ServiceCategory::query()
            ->where('tenant_id', $this->tenantId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    #[Computed]
    public function services()
    {
        if (! $this->tenantId) {
            return Service::query()->whereRaw('0 = 1')->paginate($this->perPage);
        }

        $sortable = ['name', 'price', 'duration_minutes', 'created_at'];
        $sortField = in_array($this->sortField, $sortable, true) ? $this->sortField : 'created_at';
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Service::query()
            ->with([
                'category:id,name',
                'post:id,service_id,status,archived_at,created_at',
            ])
            ->select([
                'id', 'tenant_id', 'service_category_id', 'name', 'description',
                'duration_minutes', 'price', 'is_vehicle_service', 'is_active', 'created_at',
            ])
            ->where('tenant_id', $this->tenantId)
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('service_category_id', $this->categoryFilter))
            ->when($this->activeFilter === 'active',   fn ($q) => $q->where('is_active', true))
            ->when($this->activeFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->statusFilter !== '', function ($q) {
                $q->whereHas('post', fn ($p) => $p->where('status', $this->statusFilter));
            })
            ->orderBy($sortField, $sortDir)
            ->paginate($this->perPage);
    }

    public function toggleActive(int $id): void
    {
        if (! $this->tenantId) return;

        $service = Service::where('tenant_id', $this->tenantId)->find($id);
        if (! $service) return;

        $service->update(['is_active' => ! $service->is_active]);

        unset($this->services);

        session()->flash('success', $service->name . ($service->is_active ? ' activated.' : ' deactivated.'));
    }

    public function togglePublish(int $serviceId): void
    {
        if (! $this->tenantId) return;

        $post = Post::query()
            ->where('tenant_id', $this->tenantId)
            ->where('service_id', $serviceId)
            ->first();

        if (! $post) {
            session()->flash('error', 'This service has no post to update.');
            return;
        }

        $post->status = $post->status === PostStatus::Published
            ? PostStatus::Draft
            : PostStatus::Published;

        if ($post->status === PostStatus::Published) {
            $post->archived_at = null;
        }

        $post->save();

        unset($this->services);

        session()->flash('success', 'Service ' . strtolower($post->status->label()) . '.');
    }

    public function confirmDelete(int $id): void
    {
        if (! $this->tenantId) return;

        $service = Service::query()
            ->where('tenant_id', $this->tenantId)
            ->select('id', 'name')
            ->find($id);

        if (! $service) return;

        $this->deletingId = $service->id;
        $this->deletingName = $service->name;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->deletingName = null;
    }

    public function delete(): void
    {
        if (! $this->tenantId || ! $this->deletingId) return;

        $service = Service::query()
            ->where('tenant_id', $this->tenantId)
            ->find($this->deletingId);

        if (! $service) {
            session()->flash('error', 'Service not found.');
            $this->cancelDelete();
            return;
        }

        DB::transaction(function () use ($service) {
            Post::where('service_id', $service->id)->delete();
            $service->delete();
        });

        $this->cancelDelete();
        unset($this->services);

        session()->flash('success', 'Service deleted successfully.');
    }
};