<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
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
    public string $stockFilter = '';
    public string $statusFilter = '';  
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public ?int $deletingId = null;
    public ?string $deletingName = null;
    public bool $showViewModal = false;
    public ?array $viewingProduct = null;
    public array $viewingVariants = [];

    public function mount(): void {}

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingStockFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        $allowed = ['name', 'sku', 'selling_price', 'stock', 'created_at'];
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
        $this->stockFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->tenant?->id;
    }

    #[Computed]
    public function categories(): array
    {
        if (! $this->tenantId) {
            return [];
        }

        return ProductCategory::query()
            ->where('tenant_id', $this->tenantId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    #[Computed]
    public function products()
    {
        if (! $this->tenantId) {
            return Product::query()->whereRaw('0 = 1')->paginate($this->perPage);
        }

        $sortable = ['name', 'sku', 'selling_price', 'stock', 'created_at'];
        $sortField = in_array($this->sortField, $sortable, true) ? $this->sortField : 'created_at';
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Product::query()
            ->with([
                'category:id,name',
                'post:id,product_id,status,archived_at,created_at',
                'variants:id,product_id,sku,price,stock_quantity',
            ])
            ->select([
                'id', 'tenant_id', 'category_id', 'name', 'sku', 'barcode',
                'cost_price', 'selling_price', 'stock', 'low_stock_alert',
                'ft_img', 'created_at',
            ])
            ->where('tenant_id', $this->tenantId)
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('barcode', 'like', $term);
                });
            })
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('stock', '<=', 'low_stock_alert')->where('stock', '>', 0))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', '<=', 0))
            ->when($this->stockFilter === 'in', fn ($q) => $q->where('stock', '>', 0))
            ->when($this->statusFilter !== '', function ($q) {
                $q->whereHas('post', fn ($p) => $p->where('status', $this->statusFilter));
            })
            ->orderBy($sortField, $sortDir)
            ->paginate($this->perPage);
    }

    public function viewProduct(int $id): void
    {
        if (! $this->tenantId) {
            return;
        }

        $product = Product::query()
            ->where('tenant_id', $this->tenantId)
            ->with([
                'category:id,name',
                'post:id,product_id,status,archived_at,created_at',
                'variants:id,product_id,sku,price,stock_quantity',
            ])
            ->find($id);

        if (! $product) {
            session()->flash('error', 'Product not found.');
            return;
        }

        $this->viewingProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => $product->category?->name ?? 'Uncategorized',
            'stock' => (int) $product->stock,
            'low_stock_alert' => (int) $product->low_stock_alert,
            'cost_price' => (float) $product->cost_price,
            'selling_price' => (float) $product->selling_price,
            'description' => $product->description,
            'image' => $product->ft_img ? asset('storage/' . $product->ft_img) : asset('images/default-product.png'),
            'attachments' => collect($product->attachments ?? [])
                ->map(fn ($path) => asset('storage/' . $path))
                ->toArray(),
            'created_at' => $product->created_at?->format('M d, Y g:i A'),
            'updated_at' => $product->updated_at?->format('M d, Y g:i A'),
            'post_status' => $product->post?->status?->value,
            'post_status_label' => $product->post?->status?->label() ?? 'No post',
        ];

        $this->viewingVariants = $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'sku' => $v->sku,
            'price' => (float) $v->price,
            'stock_quantity' => (int) $v->stock_quantity,
        ])->toArray();

        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingProduct = null;
        $this->viewingVariants = [];
    }

    public function togglePublish(int $productId): void
    {
        if (! $this->tenantId) {
            return;
        }

        $post = Post::query()
            ->where('tenant_id', $this->tenantId)
            ->where('product_id', $productId)
            ->first();

        if (! $post) {
            session()->flash('error', 'This product has no post to update.');
            return;
        }

        $post->status = $post->status === PostStatus::Published
            ? PostStatus::Draft
            : PostStatus::Published;

        if ($post->status === PostStatus::Published) {
            $post->archived_at = null;
        }

        $post->save();

        unset($this->products);

        session()->flash('success', 'Product ' . strtolower($post->status->label()) . '.');
    }

    public function confirmDelete(int $id): void
    {
        if (! $this->tenantId) {
            return;
        }

        $product = Product::query()
            ->where('tenant_id', $this->tenantId)
            ->select('id', 'name')
            ->find($id);

        if (! $product) {
            return;
        }

        $this->deletingId = $product->id;
        $this->deletingName = $product->name;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->deletingName = null;
    }

    public function delete(): void
    {
        if (! $this->tenantId || ! $this->deletingId) {
            return;
        }

        $product = Product::query()
            ->where('tenant_id', $this->tenantId)
            ->find($this->deletingId);

        if (! $product) {
            session()->flash('error', 'Product not found.');
            $this->cancelDelete();
            return;
        }

        $paths = [];

        if ($product->ft_img) {
            $paths[] = $product->ft_img;
        }

        if (is_array($product->attachments)) {
            foreach ($product->attachments as $path) {
                if ($path) {
                    $paths[] = $path;
                }
            }
        }

        DB::transaction(function () use ($product, $paths) {
            Post::where('product_id', $product->id)->delete();

            if (! empty($paths)) {
                Storage::disk('public')->delete($paths);
            }

            $product->delete();
        });

        $this->cancelDelete();
        unset($this->products);

        session()->flash('success', 'Product deleted successfully.');
    }
};
?>