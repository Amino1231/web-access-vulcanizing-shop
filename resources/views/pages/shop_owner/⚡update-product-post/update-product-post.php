<?php

use App\Models\Brand;
use App\Models\Inventory;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Variant;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
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
    public string $brand_id = '';
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

    // Brand inline creation
    public array $brands = [];
    public string $newBrandName = '';

    // ─── Variant state ────────────────────────
    /** Existing variants, editable. [id, sku, price, stock_quantity] */
    public array $variants = [];

    /** Ids of existing variants the user removed. */
    public array $variantsToDelete = [];

    /** Attribute builder: [['title' => 'Color', 'options' => ['Red','Blue']], ...] */
    public array $variantGroups = [
        ['title' => '', 'options' => ['']],
    ];

    /** Preview combos generated from attribute builder. */
    public array $variantCombos = [];

    #[Computed]
    public function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    #[Computed]
    public function combosTotalQty(): int
    {
        return (int) collect($this->variantCombos)->sum('stock_quantity');
    }

    public function mount(int $product): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            abort(403, 'No tenant account linked to this owner.');
        }

        $productModel = Product::query()
            ->where('tenant_id', $tenantId)
            ->with('post:id,product_id,status')
            ->findOrFail($product);

        $this->productId        = $productModel->id;
        $this->name             = $productModel->name;
        $this->sku              = $productModel->sku;
        $this->description      = (string) $productModel->description;
        $this->category_id      = (string) $productModel->category_id;
        $this->brand_id         = (string) ($productModel->brand_id ?? '');
        $this->barcode          = (string) $productModel->barcode;
        $this->stock            = (int) $productModel->stock;
        $this->cost_price       = (float) $productModel->cost_price;
        $this->selling_price    = (float) $productModel->selling_price;
        $this->low_stock_alert  = (int) $productModel->low_stock_alert;

        $this->status = $productModel->post?->status?->value
            ?? PostStatus::Published->value;

        $this->existingMainImage   = $productModel->ft_img;
        $this->existingAttachments = is_array($productModel->attachments) ? $productModel->attachments : [];

        $this->categories = $this->loadCategories();
        $this->brands     = $this->loadBrands();

        // Load existing variants as editable rows
        $this->variants = $productModel->variants
            ->map(fn ($v) => [
                'id'             => $v->id,
                'sku'            => $v->sku,
                'price'          => (float) $v->price,
                'stock_quantity' => (int) $v->stock_quantity,
            ])
            ->toArray();
    }

    /* ---------------- Existing variant management ---------------- */

    public function addVariant(): void
    {
        $this->variants[] = [
            'id'             => null,
            'sku'            => '',
            'price'          => (float) $this->selling_price,
            'stock_quantity' => 0,
        ];
    }

    public function removeVariant(int $index): void
    {
        if (! isset($this->variants[$index])) {
            return;
        }

        $variant = $this->variants[$index];

        if (! empty($variant['id'])) {
            $this->variantsToDelete[] = $variant['id'];
        }

        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    /* ---------------- Attribute builder (generates NEW variants) ---------------- */

    public function addVariantGroup(): void
    {
        if (count($this->variantGroups) >= 3) {
            return;
        }

        $this->variantGroups[] = ['title' => '', 'options' => ['']];
        $this->rebuildCombos();
    }

    public function removeVariantGroup(int $index): void
    {
        unset($this->variantGroups[$index]);
        $this->variantGroups = array_values($this->variantGroups);
        $this->rebuildCombos();
    }

    public function addOption(int $groupIndex): void
    {
        if (! isset($this->variantGroups[$groupIndex])) {
            return;
        }

        $this->variantGroups[$groupIndex]['options'][] = '';
        $this->rebuildCombos();
    }

    public function removeOption(int $groupIndex, int $optionIndex): void
    {
        if (! isset($this->variantGroups[$groupIndex]['options'][$optionIndex])) {
            return;
        }

        unset($this->variantGroups[$groupIndex]['options'][$optionIndex]);
        $this->variantGroups[$groupIndex]['options'] = array_values($this->variantGroups[$groupIndex]['options']);
        $this->rebuildCombos();
    }

    public function updatedVariantGroups(): void
    {
        $this->rebuildCombos();
    }

    /**
     * Generate preview combos from attributes. Preserves user edits by label.
     */
    protected function rebuildCombos(): void
    {
        $combos = $this->buildVariantCombinations($this->variantGroups);

        if (empty($combos)) {
            $this->variantCombos = [];
            return;
        }

        $existing = collect($this->variantCombos)->keyBy('label');
        $baseSku  = strtoupper(Str::slug($this->name ?: 'PRODUCT', '-')) ?: 'PRODUCT';

        $rebuilt = [];
        foreach ($combos as $combo) {
            $label = $this->variantLabel($combo);
            if ($label === '') {
                continue;
            }

            $old = $existing->get($label);

            $rebuilt[] = [
                'label'          => $label,
                'sku'            => $old['sku']            ?? $this->generateVariantSku($baseSku, $combo),
                'price'          => $old['price']          ?? (float) $this->selling_price,
                'stock_quantity' => $old['stock_quantity'] ?? 0,
            ];
        }

        $this->variantCombos = $rebuilt;
    }

    public function regenerateAllVariantSkus(): void
    {
        $baseSku = strtoupper(Str::slug($this->name ?: 'PRODUCT', '-')) ?: 'PRODUCT';

        foreach ($this->variantCombos as $i => $combo) {
            $comboData = $this->labelToCombo($combo['label'] ?? '');
            $this->variantCombos[$i]['sku'] = $this->generateVariantSku($baseSku, $comboData);
        }
    }

    protected function labelToCombo(string $label): array
    {
        $combo = [];

        foreach (explode(' | ', $label) as $part) {
            if (! str_contains($part, ':')) continue;
            [$k, $v] = explode(':', $part, 2);
            $combo[trim($k)] = trim($v);
        }

        return $combo;
    }

    protected function generateVariantSku(string $baseSku, array $combo): string
    {
        $parts = [];
        foreach ($combo as $option) {
            $slug = strtoupper(Str::slug((string) $option, ''));
            if ($slug !== '') {
                $parts[] = $slug;
            }
        }

        if (empty($parts)) {
            return $baseSku . '-' . Str::upper(Str::random(4));
        }

        return $baseSku . '-' . implode('-', $parts);
    }

    protected function buildVariantCombinations(array $groups): array
    {
        $results = [[]];

        foreach ($groups as $group) {
            $groupTitle = trim((string) ($group['title'] ?? ''));
            $options = array_values(array_filter(
                array_map(fn ($o) => trim((string) $o), $group['options'] ?? []),
                fn ($o) => $o !== ''
            ));

            if ($groupTitle === '' || $options === []) {
                continue;
            }

            $newResults = [];
            foreach ($results as $result) {
                foreach ($options as $option) {
                    $newResults[] = array_merge($result, [$groupTitle => $option]);
                }
            }
            $results = $newResults;
        }

        if (count($results) === 1 && $results[0] === []) {
            return [];
        }

        return $results;
    }

    protected function variantLabel(array $combo): string
    {
        $parts = [];
        foreach ($combo as $title => $option) {
            $parts[] = $title . ': ' . $option;
        }
        return implode(' | ', $parts);
    }

    /**
     * Convert preview combos into real variant rows (append to $variants).
     */
    public function addCombosToVariants(): void
    {
        foreach ($this->variantCombos as $combo) {
            if (empty($combo['sku'])) continue;

            $this->variants[] = [
                'id'             => null,
                'sku'            => $combo['sku'],
                'price'          => (float) ($combo['price'] ?? $this->selling_price),
                'stock_quantity' => max(0, (int) ($combo['stock_quantity'] ?? 0)),
            ];
        }

        // Reset the attribute builder + combos after adding
        $this->variantGroups = [['title' => '', 'options' => ['']]];
        $this->variantCombos = [];
    }

    /* ---------------- Inline category ---------------- */

    public function createInlineCategory(): void
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) return;

        $this->validate([
            'newCategoryName'        => ['required', 'string', 'min:2', 'max:255'],
            'newCategoryDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $categoryName = trim($this->newCategoryName);
        $slug = Str::slug($categoryName) ?: 'category';

        if (ProductCategory::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $this->addError('newCategoryName', 'This category already exists.');
            return;
        }

        $category = ProductCategory::create([
            'tenant_id'   => $tenantId,
            'name'        => $categoryName,
            'slug'        => $slug,
            'description' => trim($this->newCategoryDescription) ?: null,
        ]);

        $this->category_id            = (string) $category->id;
        $this->newCategoryName        = '';
        $this->newCategoryDescription = '';
        $this->categories             = $this->loadCategories();
    }

    /* ---------------- Inline brand ---------------- */

    public function createInlineBrand(): void
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) return;

        $this->validate([
            'newBrandName' => ['required', 'string', 'min:1', 'max:120'],
        ]);

        $brandName = trim($this->newBrandName);
        $slug = Str::slug($brandName) ?: 'brand';

        if (Brand::where('tenant_id', $tenantId)->where('brand_slug', $slug)->exists()) {
            $this->addError('newBrandName', 'This brand already exists.');
            return;
        }

        $brand = Brand::create([
            'tenant_id'  => $tenantId,
            'brand_name' => $brandName,
            'brand_slug' => $slug,
        ]);

        $this->brand_id     = (string) $brand->id;
        $this->newBrandName = '';
        $this->brands       = $this->loadBrands();
    }

    protected function loadCategories(): array
    {
        return ProductCategory::where('tenant_id', $this->tenantId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    protected function loadBrands(): array
    {
        return Brand::where('tenant_id', $this->tenantId)
            ->orderBy('brand_name')
            ->get(['id', 'brand_name'])
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->brand_name])
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
        $tenantId = $this->tenantId;
        if (! $tenantId) return;

        $base = strtoupper(Str::slug($this->name, '-'));
        if ($base === '') return;

        do {
            $sku = $base.'-'.strtoupper(Str::random(4));
        } while (
            Product::where('tenant_id', $tenantId)
                ->where('sku', $sku)
                ->where('id', '!=', $this->productId)
                ->exists()
        );

        $this->sku = $sku;
    }

    /* ---------------- Save ---------------- */

    public function save(): void
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) {
            session()->flash('error', 'No tenant account linked.');
            return;
        }

        $product = Product::where('tenant_id', $tenantId)->findOrFail($this->productId);

        $this->validate([
            'name'                => ['required', 'string', 'max:255'],
            'sku'                 => [
                'required', 'string', 'max:100',
                Rule::unique('products', 'sku')
                    ->where('tenant_id', $tenantId)
                    ->ignore($this->productId),
            ],
            'description'         => ['nullable', 'string'],
            'category_id'         => [
                'required',
                Rule::exists('product_categories', 'id')->where('tenant_id', $tenantId),
            ],
            'brand_id'            => [
                'nullable',
                Rule::exists('brands', 'id')->where('tenant_id', $tenantId),
            ],
            'barcode'             => ['nullable', 'string', 'max:100'],
            'stock'               => ['nullable', 'integer', 'min:0'],
            'cost_price'          => ['required', 'numeric', 'min:0'],
            'selling_price'       => ['required', 'numeric', 'min:0'],
            'low_stock_alert'     => ['required', 'integer', 'min:0'],
            'status'              => ['required', Rule::in(array_column(PostStatus::cases(), 'value'))],
            'mainImage'           => ['nullable', 'image', 'max:2048'],
            'attachmentImages'    => ['nullable', 'array', 'max:5'],
            'attachmentImages.*'  => ['image', 'max:2048'],
            'variants'            => ['nullable', 'array'],
            'variants.*.id'       => ['nullable', 'integer'],
            'variants.*.sku'      => ['nullable', 'string', 'max:100'],
            'variants.*.price'    => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($tenantId, $product) {
            $oldStock = (int) $product->stock;

            /* ---- Main image ---- */
            $mainImagePath = $product->ft_img;
            $oldMainImage  = $product->ft_img;

            if ($this->mainImage) {
                if ($oldMainImage && Storage::disk('public')->exists($oldMainImage)) {
                    Storage::disk('public')->delete($oldMainImage);
                }
                $mainImagePath = $this->mainImage->store("tenant/{$tenantId}/products/main", 'public');
            } elseif ($this->existingMainImage === null && $oldMainImage) {
                if (Storage::disk('public')->exists($oldMainImage)) {
                    Storage::disk('public')->delete($oldMainImage);
                }
                $mainImagePath = null;
            }

            /* ---- Attachments ---- */
            $attachmentPaths = $this->existingAttachments;
            foreach ($this->attachmentImages as $attachment) {
                $attachmentPaths[] = $attachment->store("tenant/{$tenantId}/products/attachments", 'public');
            }

            /* ---- Unique slug ---- */
            $slug = Str::slug($this->name) ?: $product->slug;
            $original = $slug;
            $i = 1;

            while (
                Product::where('tenant_id', $tenantId)
                    ->where('slug', $slug)
                    ->where('id', '!=', $product->id)
                    ->exists()
            ) {
                $slug = $original.'-'.$i++;
            }

            $hasVariantsBefore = $product->variants()->exists();

            /* ---- Update product ---- */
            $product->update([
                'category_id'     => $this->category_id,
                'brand_id'        => $this->brand_id ?: null,
                'name'            => $this->name,
                'sku'             => $this->sku,
                'slug'            => $slug,
                'barcode'         => $this->barcode ?: null,
                'cost_price'      => $this->cost_price,
                'selling_price'   => $this->selling_price,
                'stock'           => $hasVariantsBefore ? $product->stock : (int) $this->stock,
                'low_stock_alert' => $this->low_stock_alert,
                'description'     => $this->description,
                'ft_img'          => $mainImagePath,
                'attachments'     => $attachmentPaths ?: null,
            ]);

            /* ---- Handle variants (existing + newly added) ---- */
            $hadVariants    = $product->variants()->exists();
            $hasVariantsNow = ! empty($this->variants);

            if ($hasVariantsNow) {
                $seenSkus = [];

                foreach ($this->variants as $v) {
                    $variantSku = trim((string) ($v['sku'] ?? ''));

                    if ($variantSku === '') continue;

                    if (isset($seenSkus[$variantSku])) {
                        throw new \RuntimeException("Duplicate variant SKU: {$variantSku}");
                    }
                    $seenSkus[$variantSku] = true;

                    $variantData = [
                        'tenant_id'      => $tenantId,
                        'product_id'     => $product->id,
                        'sku'            => $variantSku,
                        'price'          => (float) ($v['price'] ?? $this->selling_price),
                        'stock_quantity' => max(0, (int) ($v['stock_quantity'] ?? 0)),
                    ];

                    if (! empty($v['id'])) {
                        $variant = Variant::where('tenant_id', $tenantId)
                            ->where('product_id', $product->id)
                            ->find($v['id']);

                        if ($variant) {
                            $variant->update($variantData);
                        }
                    } else {
                        Variant::create($variantData);
                    }
                }

                if (! empty($this->variantsToDelete)) {
                    Variant::where('tenant_id', $tenantId)
                        ->where('product_id', $product->id)
                        ->whereIn('id', $this->variantsToDelete)
                        ->delete();
                }

                $product->refresh()->syncStockFromVariants();
            } elseif ($hadVariants) {
                $product->update(['stock' => (int) $this->stock]);
            }

            /* ---- Inventory log ---- */
            $product->refresh();
            $newStock = (int) $product->stock;

            if ($oldStock !== $newStock) {
                $delta = $newStock - $oldStock;

                Inventory::create([
                    'tenant_id'      => $tenantId,
                    'product_id'     => $product->id,
                    'type'           => $delta > 0 ? 'stock_in' : 'stock_out',
                    'quantity'       => $delta,
                    'before_stock'   => $oldStock,
                    'after_stock'    => $newStock,
                    'reference_type' => 'product_edit',
                    'reference_id'   => $product->id,
                    'remarks'        => 'Manual stock adjustment via product edit',
                ]);
            }

            /* ---- Sync public post ---- */
            $post = Post::firstOrNew([
                'tenant_id'  => $tenantId,
                'product_id' => $product->id,
            ]);

            $post->fill([
                'service_id'  => null,
                'name'        => $product->name,
                'slug'        => $product->slug,
                'attachment'  => null,
                'image'       => $mainImagePath,
                'price'       => $product->selling_price,
                'description' => $product->description,
                'status'      => $this->status,
            ]);

            if ($this->status === PostStatus::Published->value) {
                $post->archived_at = null;
            }

            $post->save();
        });

        session()->flash('success', 'Product updated successfully.');
        $this->redirectRoute('owner.products', navigate: true);
    }
};