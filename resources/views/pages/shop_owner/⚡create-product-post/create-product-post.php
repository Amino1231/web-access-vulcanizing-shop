<?php

use App\Enums\PostStatus;
use App\Models\Brand;
use App\Models\Inventory;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Variant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $description = '';
    public string $category_id = '';
    public string $brand_id = '';
    public string $barcode = '';
    public int $stock = 0;
    public float $cost_price = 0;
    public float $selling_price = 0;
    public int $low_stock_alert = 5;

    public $mainImage;
    public array $attachmentImages = [];

    public bool $hasVariants = false;

    /** Attribute builder: [['title' => 'Color', 'options' => ['Red','Blue']], ...] */
    public array $variantGroups = [
        ['title' => 'Color', 'options' => ['Red', 'Blue', 'Black']],
    ];

    /**
     * Generated combinations with editable SKU / price / stock.
     * [['label' => 'Color: Red', 'sku' => '...', 'price' => 0, 'stock_quantity' => 0], ...]
     */
    public array $variantCombos = [];

    public string $status = 'published';

    public string $newCategoryName = '';
    public string $newCategoryDescription = '';

    public string $newBrandName = '';

    public function mount(): void
    {
        // Nothing yet
    }

    // ─────────────────────────────────────────────
    // Tenant + lists
    // ─────────────────────────────────────────────

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

        return ProductCategory::query()
            ->where('tenant_id', $this->tenantId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    #[Computed]
    public function brands(): array
    {
        if (! $this->tenantId) {
            return [];
        }

        return Brand::query()
            ->where('tenant_id', $this->tenantId)
            ->orderBy('brand_name')
            ->get(['id', 'brand_name'])
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->brand_name])
            ->toArray();
    }

    #[Computed]
    public function combosTotalQty(): int
    {
        return (int) collect($this->variantCombos)->sum('stock_quantity');
    }

    // ─────────────────────────────────────────────
    // Variant group editing
    // ─────────────────────────────────────────────

    public function updatedHasVariants(): void
    {
        if ($this->hasVariants) {
            $this->rebuildCombos();
        } else {
            $this->variantCombos = [];
        }
    }

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
        if (count($this->variantGroups) <= 1) {
            return;
        }

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

    /**
     * Livewire fires this automatically whenever variantGroups changes
     * via wire:model bindings (title / option inputs).
     */
    public function updatedVariantGroups(): void
    {
        $this->rebuildCombos();
    }

    /**
     * Build combos from attribute groups. Preserves any SKU/price/qty
     * the user already edited on rows that still exist (matched by label).
     */
    protected function rebuildCombos(): void
    {
        $combos = $this->buildVariantCombinations($this->variantGroups);

        if (empty($combos)) {
            $this->variantCombos = [];
            return;
        }

        // Index existing combos by label to preserve user edits
        $existing = collect($this->variantCombos)->keyBy('label');

        $baseSku = strtoupper(Str::slug($this->name ?: 'PRODUCT', '-')) ?: 'PRODUCT';

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

    /**
     * Regenerate every SKU from the current product name + combo options.
     * Wipes any manual SKU edits on the combos.
     */
    public function regenerateAllVariantSkus(): void
    {
        $baseSku = strtoupper(Str::slug($this->name ?: 'PRODUCT', '-')) ?: 'PRODUCT';

        foreach ($this->variantCombos as $i => $combo) {
            $comboData = $this->labelToCombo($combo['label'] ?? '');
            $this->variantCombos[$i]['sku'] = $this->generateVariantSku($baseSku, $comboData);
        }
    }

    /**
     * Reverse-engineer a combo array from a label string "Color: Red | Size: S"
     */
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

    // ─────────────────────────────────────────────
    // Inline category
    // ─────────────────────────────────────────────

    public function createInlineCategory(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            session()->flash('error', 'No tenant account is linked to this owner profile.');
            return;
        }

        $this->validate([
            'newCategoryName'        => ['required', 'string', 'min:2', 'max:255'],
            'newCategoryDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $categoryName = trim($this->newCategoryName);
        $slug         = Str::slug($categoryName) ?: 'category';

        if (ProductCategory::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $this->addError('newCategoryName', 'This category already exists.');
            return;
        }

        try {
            $category = ProductCategory::create([
                'tenant_id'   => $tenantId,
                'name'        => $categoryName,
                'slug'        => $slug,
                'description' => trim($this->newCategoryDescription) ?: null,
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                $this->addError('newCategoryName', 'This category already exists.');
                return;
            }
            throw $e;
        }

        $this->category_id            = (string) $category->id;
        $this->newCategoryName        = '';
        $this->newCategoryDescription = '';

        unset($this->categories);
    }

    // ─────────────────────────────────────────────
    // Inline brand
    // ─────────────────────────────────────────────

    public function createInlineBrand(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            session()->flash('error', 'No tenant account is linked to this owner profile.');
            return;
        }

        $this->validate([
            'newBrandName' => ['required', 'string', 'min:1', 'max:120'],
        ]);

        $brandName = trim($this->newBrandName);
        $slug      = Str::slug($brandName) ?: 'brand';

        if (Brand::where('tenant_id', $tenantId)->where('brand_slug', $slug)->exists()) {
            $this->addError('newBrandName', 'This brand already exists.');
            return;
        }

        try {
            $brand = Brand::create([
                'tenant_id'  => $tenantId,
                'brand_name' => $brandName,
                'brand_slug' => $slug,
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                $this->addError('newBrandName', 'This brand already exists.');
                return;
            }
            throw $e;
        }

        $this->brand_id     = (string) $brand->id;
        $this->newBrandName = '';

        unset($this->brands);
    }

    // ─────────────────────────────────────────────
    // Save product
    // ─────────────────────────────────────────────

    public function save(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            session()->flash('error', 'No tenant account is linked to this owner profile.');
            return;
        }

        $this->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'category_id'          => [
                'required',
                Rule::exists('product_categories', 'id')->where('tenant_id', $tenantId),
            ],
            'brand_id'             => [
                'nullable',
                Rule::exists('brands', 'id')->where('tenant_id', $tenantId),
            ],
            'barcode'              => ['nullable', 'string', 'max:100'],
            'stock'                => ['nullable', 'integer', 'min:0'],
            'cost_price'           => ['required', 'numeric', 'min:0'],
            'selling_price'        => ['required', 'numeric', 'min:0'],
            'low_stock_alert'      => ['required', 'integer', 'min:0'],
            'mainImage'            => ['required', 'image', 'max:2048'],
            'attachmentImages'     => ['nullable', 'array', 'max:5'],
            'attachmentImages.*'   => ['image', 'max:2048'],
            'status'               => ['required', Rule::in(array_column(PostStatus::cases(), 'value'))],
            'variantCombos'        => ['nullable', 'array'],
            'variantCombos.*.sku'  => ['nullable', 'string', 'max:100'],
            'variantCombos.*.price'=> ['nullable', 'numeric', 'min:0'],
            'variantCombos.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        // ─── Variant validation ─────────────────
        if ($this->hasVariants) {
            if (empty($this->variantCombos)) {
                $this->addError('variantCombos', 'Add at least one attribute + option so combinations can be generated.');
                return;
            }

            $seen = [];
            foreach ($this->variantCombos as $i => $row) {
                $vSku = trim((string) ($row['sku'] ?? ''));

                if ($vSku === '') {
                    $this->addError("variantCombos.{$i}.sku", 'SKU is required.');
                    return;
                }

                if (isset($seen[$vSku])) {
                    $this->addError("variantCombos.{$i}.sku", 'Duplicate SKU in variants.');
                    return;
                }
                $seen[$vSku] = true;

                if (Variant::where('tenant_id', $tenantId)->where('sku', $vSku)->exists()) {
                    $this->addError("variantCombos.{$i}.sku", 'That SKU already exists.');
                    return;
                }
            }
        }

        DB::transaction(function () use ($tenantId) {
            // ─── Upload images ─────────────────────
            $mainImagePath = $this->mainImage
                ? $this->mainImage->store("tenant/{$tenantId}/products/main", 'public')
                : null;

            $attachmentPaths = [];
            foreach ($this->attachmentImages as $attachment) {
                $attachmentPaths[] = $attachment->store("tenant/{$tenantId}/products/attachments", 'public');
            }

            // ─── Create product ────────────────────
            $slug = $this->uniqueSlug($tenantId, $this->name);
            $sku  = $this->uniqueSku($tenantId, $this->name);

            $productStock = $this->hasVariants ? 0 : (int) $this->stock;

            $product = Product::create([
                'tenant_id'       => $tenantId,
                'category_id'     => $this->category_id,
                'brand_id'        => $this->brand_id ?: null,
                'name'            => $this->name,
                'slug'            => $slug,
                'sku'             => $sku,
                'barcode'         => $this->barcode ?: null,
                'cost_price'      => $this->cost_price,
                'selling_price'   => $this->selling_price,
                'stock'           => $productStock,
                'low_stock_alert' => $this->low_stock_alert,
                'description'     => $this->description,
                'ft_img'          => $mainImagePath,
                'attachments'     => $attachmentPaths ?: null,
            ]);

            // ─── Initial inventory log (no variants) ─
            if (! $this->hasVariants) {
                Inventory::create([
                    'tenant_id'      => $tenantId,
                    'product_id'     => $product->id,
                    'type'           => 'stock_in',
                    'quantity'       => $productStock,
                    'before_stock'   => 0,
                    'after_stock'    => $productStock,
                    'reference_type' => 'product_post',
                    'reference_id'   => $product->id,
                    'remarks'        => 'Initial stock added on product creation.',
                ]);
            }

            // ─── Variants (from editable combos) ───
            if ($this->hasVariants && ! empty($this->variantCombos)) {
                foreach ($this->variantCombos as $row) {
                    $variantSku = trim((string) $row['sku']);
                    $qty        = max(0, (int) ($row['stock_quantity'] ?? 0));
                    $price      = (float) ($row['price'] ?? $this->selling_price);

                    $variant = Variant::create([
                        'tenant_id'      => $tenantId,
                        'product_id'     => $product->id,
                        'sku'            => $variantSku,
                        'price'          => $price,
                        'stock_quantity' => $qty,
                    ]);

                    if ($qty > 0) {
                        Inventory::create([
                            'tenant_id'      => $tenantId,
                            'product_id'     => $product->id,
                            'type'           => 'stock_in',
                            'quantity'       => $qty,
                            'before_stock'   => 0,
                            'after_stock'    => $qty,
                            'reference_type' => 'variant_post',
                            'reference_id'   => $variant->id,
                            'remarks'        => "Initial stock for variant {$variantSku}.",
                        ]);
                    }
                }

                $product->refresh()->syncStockFromVariants();
            }

            // ─── Post (marketplace listing) ────────
            Post::create([
                'tenant_id'   => $tenantId,
                'product_id'  => $product->id,
                'service_id'  => null,
                'name'        => $product->name,
                'slug'        => $product->slug,
                'attachment'  => null,
                'image'       => $mainImagePath,
                'price'       => $product->selling_price,
                'description' => $product->description,
                'status'      => $this->status,
            ]);
        });

        // ─── Reset form ────────────────────────
        $this->reset([
            'name', 'description', 'category_id', 'brand_id', 'barcode',
            'stock', 'cost_price', 'selling_price', 'low_stock_alert',
            'mainImage', 'attachmentImages',
            'hasVariants',
            'variantCombos',
            'newCategoryName', 'newCategoryDescription',
            'newBrandName',
        ]);

        $this->variantGroups = [
            ['title' => 'Color', 'options' => ['Red', 'Blue', 'Black']],
        ];
        $this->status = PostStatus::Published->value;
        $this->resetValidation();

        session()->flash('success', 'Product post created successfully.');
        $this->redirectRoute('owner.products', navigate: true);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    protected function uniqueSlug(int $tenantId, string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i    = 1;

        while (Product::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function uniqueSku(int $tenantId, string $name): string
    {
        $base = strtoupper(Str::slug($name, '-')) ?: 'PRODUCT';

        do {
            $sku = $base . '-' . Str::upper(Str::random(4));
        } while (Product::where('tenant_id', $tenantId)->where('sku', $sku)->exists());

        return $sku;
    }
};