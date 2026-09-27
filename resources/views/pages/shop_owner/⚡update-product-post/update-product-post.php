<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithFileUploads;

    public int $productId;

    // Product fields
    public string $name = '';
    public string $sku = '';
    public string $description = '';
    public string $category_id = '';
    public string $barcode = '';
    public int $stock = 0;
    public float $cost_price = 0;
    public float $selling_price = 0;
    public int $low_stock_alert = 5;

    // Post status
    public string $status = 'published';

    // Images
    public $mainImage;
    public ?string $existingMainImage = null;

    public array $attachmentImages = [];
    public array $existingAttachments = [];

    // Category inline creation
    public array $categories = [];
    public string $newCategoryName = '';
    public string $newCategoryDescription = '';

    public function mount(int $product): void
    {
        $tenant = Auth::user()?->tenant;

        $productModel = Product::query()
            ->where('tenant_id', $tenant?->id)
            ->with('post:id,product_id,status')
            ->findOrFail($product);

        $this->productId = $productModel->id;
        $this->name = $productModel->name;
        $this->sku = $productModel->sku;
        $this->description = (string) $productModel->description;
        $this->category_id = (string) $productModel->category_id;
        $this->barcode = (string) $productModel->barcode;
        $this->stock = (int) $productModel->stock;
        $this->cost_price = (float) $productModel->cost_price;
        $this->selling_price = (float) $productModel->selling_price;
        $this->low_stock_alert = (int) $productModel->low_stock_alert;

        // Load post status (fallback to published)
        $this->status = $productModel->post?->status?->value
            ?? PostStatus::Published->value;

        $this->existingMainImage = $productModel->ft_img;
        $this->existingAttachments = $productModel->attachments ?? [];

        $this->categories = ProductCategory::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    /* ---------------- Inline category ---------------- */

    public function createInlineCategory(): void
    {
        $tenant = Auth::user()?->tenant;
        if (! $tenant) return;

        $this->validate([
            'newCategoryName' => ['required', 'string', 'min:2', 'max:255'],
            'newCategoryDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $categoryName = trim($this->newCategoryName);
        $slug = Str::slug($categoryName) ?: 'category';

        if (ProductCategory::where('tenant_id', $tenant->id)->where('slug', $slug)->exists()) {
            $this->addError('newCategoryName', 'This category already exists.');
            return;
        }

        $category = ProductCategory::create([
            'tenant_id' => $tenant->id,
            'name' => $categoryName,
            'slug' => $slug,
            'description' => trim($this->newCategoryDescription),
        ]);

        $this->category_id = (string) $category->id;
        $this->newCategoryName = '';
        $this->newCategoryDescription = '';

        $this->categories = ProductCategory::where('tenant_id', $tenant->id)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    /* ---------------- Image management ---------------- */

    public function removeExistingAttachment(int $index): void
    {
        if (! isset($this->existingAttachments[$index])) return;

        $path = $this->existingAttachments[$index];
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);
    }

    public function removeMainImage(): void
    {
        if ($this->existingMainImage && Storage::disk('public')->exists($this->existingMainImage)) {
            Storage::disk('public')->delete($this->existingMainImage);
        }
        $this->existingMainImage = null;
    }

    /* ---------------- SKU ---------------- */

    public function regenerateSku(): void
    {
        $base = strtoupper(Str::slug($this->name, '-'));

        if ($base === '') {
            return;
        }

        $sku = $base . '-' . strtoupper(Str::random(4));

        while (
            Product::where('sku', $sku)
                ->where('id', '!=', $this->productId)
                ->exists()
        ) {
            $sku = $base . '-' . strtoupper(Str::random(4));
        }

        $this->sku = $sku;
    }

    /* ---------------- Save ---------------- */

    public function save(): void
    {
        $tenant = Auth::user()?->tenant;
        if (! $tenant) {
            session()->flash('error', 'No tenant account linked.');
            return;
        }

        $product = Product::where('tenant_id', $tenant->id)->findOrFail($this->productId);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required', 'string', 'max:100',
                Rule::unique('products', 'sku')->ignore($this->productId),
            ],
            'description' => ['nullable', 'string'],
            'category_id' => [
                'required',
                Rule::exists('product_categories', 'id')->where('tenant_id', $tenant->id),
            ],
            'barcode' => ['nullable', 'string', 'max:100'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'low_stock_alert' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_column(PostStatus::cases(), 'value'))],
            'mainImage' => ['nullable', 'image', 'max:2048'],
            'attachmentImages' => ['nullable', 'array', 'max:5'],
            'attachmentImages.*' => ['image', 'max:2048'],
        ]);

        DB::transaction(function () use ($tenant, $product) {
            /* ---- Main image resolution ---- */
            $mainImagePath = $product->ft_img;
            $oldMainImage = $product->ft_img;

            if ($this->mainImage) {
                // New upload replaces old
                if ($oldMainImage && Storage::disk('public')->exists($oldMainImage)) {
                    Storage::disk('public')->delete($oldMainImage);
                }
                $mainImagePath = $this->mainImage->store("tenant/{$tenant->id}/products/main", 'public');
            } elseif ($this->existingMainImage === null && $oldMainImage) {
                // Removed via UI
                if (Storage::disk('public')->exists($oldMainImage)) {
                    Storage::disk('public')->delete($oldMainImage);
                }
                $mainImagePath = null;
            }

            /* ---- Attachments merge (kept + new) ---- */
            $attachmentPaths = $this->existingAttachments;
            foreach ($this->attachmentImages as $attachment) {
                $attachmentPaths[] = $attachment->store("tenant/{$tenant->id}/products/attachments", 'public');
            }

            /* ---- Unique slug ---- */
            $slug = Str::slug($this->name) ?: $product->slug;
            $original = $slug;
            $i = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $original . '-' . $i++;
            }

            /* ---- Update product ---- */
            $product->update([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'sku' => $this->sku,
                'slug' => $slug,
                'barcode' => $this->barcode ?: null,
                'cost_price' => $this->cost_price,
                'selling_price' => $this->selling_price,
                'stock' => $product->variants()->exists() ? $product->stock : (int) $this->stock,
                'low_stock_alert' => $this->low_stock_alert,
                'description' => $this->description,
                'ft_img' => $mainImagePath,
                'attachments' => $attachmentPaths,
            ]);

            /* ---- Sync the public post (drives customer storefront) ---- */
            $post = Post::firstOrNew(['product_id' => $product->id]);

            $post->fill([
                'tenant_id' => $tenant->id,
                'product_category_id' => $product->category_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'type' => 'product',
                'image' => $mainImagePath,
                'price' => $product->selling_price,
                'description' => $product->description,
                'status' => $this->status,
            ]);

            // Auto-clear archived_at if republishing
            if ($this->status === PostStatus::Published->value) {
                $post->archived_at = null;
            }

            $post->save();
        });

        session()->flash('success', 'Product updated successfully.');
        $this->redirectRoute('owner.products', $product->id);
    }
};
?>
