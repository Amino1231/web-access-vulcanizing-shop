<?php

use App\Enums\PostStatus;
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
    public string $barcode = '';
    public int $stock = 0;
    public float $cost_price = 0;
    public float $selling_price = 0;
    public int $low_stock_alert = 5;

    public $mainImage;
    public array $attachmentImages = [];

    public bool $hasVariants = false;
    public array $variantGroups = [
        ['title' => 'Color', 'options' => ['Red', 'Blue', 'Black']],
    ];

    public string $status = 'published';

    public string $newCategoryName = '';
    public string $newCategoryDescription = '';

    public function mount(): void
    {
      
    }

    #[Computed]
    public function categories(): array
    {
        $tenant = Auth::user()?->tenant;

        if (! $tenant) {
            return [];
        }

        return ProductCategory::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }


    public function addVariantGroup(): void
    {
        if (count($this->variantGroups) >= 3) {
            return;
        }

        $this->variantGroups[] = ['title' => '', 'options' => ['']];
    }

    public function removeVariantGroup(int $index): void
    {
        if (count($this->variantGroups) <= 1) {
            return;
        }

        unset($this->variantGroups[$index]);
        $this->variantGroups = array_values($this->variantGroups);
    }

    public function addOption(int $groupIndex): void
    {
        if (! isset($this->variantGroups[$groupIndex])) {
            return;
        }

        $this->variantGroups[$groupIndex]['options'][] = '';
    }

    public function removeOption(int $groupIndex, int $optionIndex): void
    {
        if (! isset($this->variantGroups[$groupIndex]['options'][$optionIndex])) {
            return;
        }

        unset($this->variantGroups[$groupIndex]['options'][$optionIndex]);
        $this->variantGroups[$groupIndex]['options'] = array_values($this->variantGroups[$groupIndex]['options']);
    }

    public function createInlineCategory(): void
    {
        $tenant = Auth::user()?->tenant;

        if (! $tenant) {
            session()->flash('error', 'No tenant account is linked to this owner profile.');
            return;
        }

        $this->validate([
            'newCategoryName' => ['required', 'string', 'min:2', 'max:255'],
            'newCategoryDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $categoryName = trim($this->newCategoryName);
        $slug = Str::slug($categoryName) ?: 'category';

        if (ProductCategory::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->exists()) {
            $this->addError('newCategoryName', 'This category already exists.');
            return;
        }

        try {
            $category = ProductCategory::create([
                'tenant_id' => $tenant->id,
                'name' => $categoryName,
                'slug' => $slug,
                'description' => trim($this->newCategoryDescription),
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                $this->addError('newCategoryName', 'This category already exists.');
                return;
            }
            throw $exception;
        }

        $this->category_id = (string) $category->id;
        $this->newCategoryName = '';
        $this->newCategoryDescription = '';

        unset($this->categories);
    }

    public function save(): void
    {
        $tenant = Auth::user()?->tenant;

        if (! $tenant) {
            session()->flash('error', 'No tenant account is linked to this owner profile.');
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
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
            'mainImage' => ['required', 'image', 'max:2048'],
            'attachmentImages' => ['nullable', 'array', 'max:5'],
            'attachmentImages.*' => ['image', 'max:2048'],
            'status' => ['required', Rule::in(array_column(PostStatus::cases(), 'value'))],
            'variantGroups' => ['nullable', 'array'],
            'variantGroups.*.title' => ['nullable', 'string', 'max:100'],
            'variantGroups.*.options' => ['nullable', 'array'],
            'variantGroups.*.options.*' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($tenant) {
            $mainImagePath = $this->mainImage?->store("tenant/{$tenant->id}/products/main", 'public');
            $attachmentPaths = [];

            foreach ($this->attachmentImages as $attachment) {
                $attachmentPaths[] = $attachment->store("tenant/{$tenant->id}/products/attachments", 'public');
            }

            $slug = $this->uniqueSlug($this->name);
            $sku = $this->uniqueSku($this->name);
            $productStock = $this->hasVariants ? 0 : (int) $this->stock;

            $product = Product::create([
                'tenant_id' => $tenant->id,
                'category_id' => $this->category_id,
                'brand_id' => null,
                'name' => $this->name,
                'slug' => $slug,
                'sku' => $sku,
                'barcode' => $this->barcode ?: null,
                'cost_price' => $this->cost_price,
                'selling_price' => $this->selling_price,
                'stock' => $productStock,
                'low_stock_alert' => $this->low_stock_alert,
                'description' => $this->description,
                'ft_img' => $mainImagePath,
                'attachments' => $attachmentPaths,
            ]);

            if (! $this->hasVariants) {
                Inventory::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'type' => 'stock_in',
                    'quantity' => $productStock,
                    'before_stock' => 0,
                    'after_stock' => $productStock,
                    'reference_type' => 'product_post',
                    'reference_id' => $product->id,
                    'remarks' => 'Initial stock added on product creation.',
                ]);
            }

            if ($this->hasVariants) {
                $variantCombos = $this->buildVariantCombinations($this->variantGroups);
                $variantTotal = 0;

                foreach ($variantCombos as $combo) {
                    $label = $this->variantLabel($combo);

                    if ($label === '') {
                        continue;
                    }

                    $variantSku = $sku . '-' . strtoupper(Str::slug($label, '-'));

                    $variant = Variant::create([
                        'tenant_id' => $tenant->id,
                        'product_id' => $product->id,
                        'sku' => $variantSku,
                        'price' => $this->selling_price,
                        'stock_quantity' => 0,
                    ]);

                    $variantTotal += $variant->stock_quantity;
                }

                $product->stock = $variantTotal;
                $product->save();
            }

            Post::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'product_category_id' => $product->category_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'type' => 'product',
                'image' => $mainImagePath,
                'price' => $product->selling_price,
                'description' => $product->description,
                'status' => $this->status,
            ]);
        });

        $this->reset([
            'name',
            'description',
            'category_id',
            'barcode',
            'stock',
            'cost_price',
            'selling_price',
            'low_stock_alert',
            'mainImage',
            'attachmentImages',
            'hasVariants',
            'variantGroups',
            'newCategoryName',
            'newCategoryDescription',
        ]);

        $this->variantGroups = [['title' => 'Color', 'options' => ['Red', 'Blue', 'Black']]];
        $this->status = PostStatus::Published->value;

        session()->flash('success', 'Product post created successfully.');
        $this->redirectRoute('owner.products');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function uniqueSku(string $name): string
    {
        $base = strtoupper(Str::slug($name, '-')) ?: 'PRODUCT';

        do {
            $sku = $base . '-' . Str::upper(Str::random(4));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    protected function buildVariantCombinations(array $groups): array
    {
        $results = [[]];

        foreach ($groups as $group) {
            $groupTitle = trim((string) ($group['title'] ?? ''));
            $options = array_values(array_filter(
                array_map(fn ($option) => trim((string) $option), $group['options'] ?? []),
                fn ($option) => $option !== ''
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

        // Drop the initial empty-combo seed when no groups were valid
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
};
?>