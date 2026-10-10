<?php

use App\Models\Post;
use App\Models\ProductCategory;
use App\Models\Tenant;
use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.customer')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $categoryFilter = '';

    #[Url(history: true)]
    public string $tenantFilter = '';

    #[Url(history: true)]
    public string $sort = 'latest';

    public int $perPage = 24;

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingTenantFilter(): void   { $this->resetPage(); }
    public function updatingSort(): void           { $this->resetPage(); }

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
        return method_exists(CartService::class, 'count') ? CartService::count() : 0;
    }

    #[Computed]
    public function categories(): array
    {
        $ids = Post::query()
            ->published()
            ->whereNotNull('product_id')
            ->join('products', 'products.id', '=', 'posts.product_id')
            ->whereNotNull('products.category_id')
            ->distinct()
            ->pluck('products.category_id')
            ->all();

        if (empty($ids)) {
            return [];
        }

        return ProductCategory::query()
            ->select('id', 'name')
            ->whereIn('id', $ids)
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
                    ->select('id', 'tenant_id', 'category_id', 'name', 'sku', 'stock', 'low_stock_alert')
                    ->withCount(['reviews as reviews_count' => fn ($r) => $r->visible()])
                    ->withAvg(['reviews as reviews_avg_rating' => fn ($r) => $r->visible()], 'rating'),
                'product.category:id,name',
                'tenant:id,name',
            ])
            ->select([
                'id', 'tenant_id', 'product_id',
                'name', 'slug', 'image', 'price', 'description', 'created_at',
            ])
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);
                $like = '%'.$term.'%';

                $words = array_filter(
                    preg_split('/\s+/', $term) ?: [],
                    fn ($w) => mb_strlen($w) >= 2
                );

                $q->where(function ($sub) use ($like, $words) {
                    $sub->where('posts.name', 'like', $like)
                        ->orWhere('posts.description', 'like', $like)
                        ->orWhereHas('product', fn ($p) => $p
                            ->where('name', 'like', $like)
                            ->orWhere('sku', 'like', $like)
                            ->orWhere('barcode', 'like', $like))
                        ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', $like));

                    foreach ($words as $word) {
                        $w = '%'.$word.'%';

                        $sub->orWhere('posts.name', 'like', $w)
                            ->orWhereHas('product', fn ($p) => $p
                                ->where('name', 'like', $w)
                                ->orWhere('sku', 'like', $w))
                            ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', $w));
                    }
                });
            })
            ->when($this->categoryFilter !== '', function ($q) {
                $q->whereHas('product', fn ($p) => $p->where('category_id', $this->categoryFilter));
            })
            ->when($this->tenantFilter !== '', fn ($q) => $q->where('tenant_id', $this->tenantFilter))
            ->when($this->sort === 'price_asc',  fn ($q) => $q->orderBy('price', 'asc'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderBy('price', 'desc'))
            ->when($this->sort === 'name',       fn ($q) => $q->orderBy('name', 'asc'))
            ->when($this->sort === 'latest',     fn ($q) => $q->orderBy('created_at', 'desc'))
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