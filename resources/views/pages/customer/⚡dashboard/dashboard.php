<?php

use App\Models\Post;
use App\Models\ProductCategory;
use App\Models\Tenant;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.customer')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $tenantFilter = '';
    public string $sort = 'latest';
    public int $perPage = 12;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingTenantFilter(): void { $this->resetPage(); }
    public function updatingSort(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->tenantFilter = '';
        $this->sort = 'latest';
        $this->resetPage();
    }

    #[Computed]
    public function cartCount(): int
    {
        return CartService::count();
    }

    #[Computed]
    public function categories(): array
    {
        return ProductCategory::query()
            ->select('id', 'name')
            ->whereIn('id', function ($q) {
                $q->select('product_category_id')
                    ->from('posts')
                    ->where('status', 'published')
                    ->whereNull('archived_at')
                    ->whereNotNull('product_category_id');
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    #[Computed]
    public function tenants(): array
    {
        return Tenant::query()
            ->select('id', 'name')
            ->whereIn('id', function ($q) {
                $q->select('tenant_id')
                    ->from('posts')
                    ->where('status', 'published')
                    ->whereNull('archived_at');
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
            ->toArray();
    }

    #[Computed]
    public function products()
    {
        return Post::query()
            ->published()
            ->whereNotNull('product_id')
            ->with([
                'product' => fn ($q) => $q->withoutGlobalScope('tenant')
                    ->select('id', 'tenant_id', 'category_id', 'name', 'sku', 'stock', 'low_stock_alert'),
                'product.category:id,name',
                'tenant:id,name',
            ])
            ->select([
                'id', 'tenant_id', 'product_id', 'product_category_id',
                'name', 'slug', 'image', 'price', 'description', 'created_at',
            ])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('posts.name', 'like', $term)
                        ->orWhere('posts.description', 'like', $term);
                });
            })
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('product_category_id', $this->categoryFilter))
            ->when($this->tenantFilter !== '', fn ($q) => $q->where('tenant_id', $this->tenantFilter))
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('price', 'asc'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderBy('price', 'desc'))
            ->when($this->sort === 'name', fn ($q) => $q->orderBy('name', 'asc'))
            ->when($this->sort === 'latest', fn ($q) => $q->orderBy('created_at', 'desc'))
            ->paginate($this->perPage);
    }

    #[Computed]
    public function featured()
    {
        return Post::query()
            ->published()
            ->whereNotNull('product_id')
            ->with(['tenant:id,name'])
            ->select(['id', 'tenant_id', 'product_id', 'name', 'price', 'image'])
            ->latest()
            ->limit(6)
            ->get();
    }
};
?>

