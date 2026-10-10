<?php

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 15;

    // ─────────────────────────────────────────
    // Form modal state
    // ─────────────────────────────────────────

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $brandName = '';

    public string $brandSlug = '';

    // ─────────────────────────────────────────
    // Delete modal state
    // ─────────────────────────────────────────

    public ?int $confirmingDeleteId = null;

    public ?string $confirmingDeleteName = null;

    /** Products count for the confirm dialog */
    public int $confirmingDeleteProductCount = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function tenantId(): ?int
    {
        return Auth::user()?->currentTenant()?->id;
    }

    // ─────────────────────────────────────────
    // Counts + list
    // ─────────────────────────────────────────

    #[Computed]
    public function counts(): array
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return ['all' => 0];
        }

        return [
            'all' => Brand::where('tenant_id', $tenantId)->count(),
        ];
    }

    #[Computed]
    public function brands()
    {
        $tenantId = $this->tenantId();
        if (! $tenantId) {
            return Brand::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return Brand::query()
            ->where('tenant_id', $tenantId)
            ->withCount('products')
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('brand_name', 'like', $term)
                        ->orWhere('brand_slug', 'like', $term);
                });
            })
            ->orderBy('brand_name')
            ->paginate($this->perPage);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    // ─────────────────────────────────────────
    // Create / Edit
    // ─────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editingId  = null;
        $this->brandName  = '';
        $this->brandSlug  = '';
        $this->showForm   = true;
    }

    public function openEdit(int $id): void
    {
        $brand = Brand::where('tenant_id', $this->tenantId())->find($id);
        if (! $brand) {
            return;
        }

        $this->resetValidation();
        $this->editingId = $brand->id;
        $this->brandName = (string) $brand->brand_name;
        $this->brandSlug = (string) $brand->brand_slug;
        $this->showForm  = true;
    }

    public function closeForm(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
        $this->resetValidation();
    }

    /**
     * Auto-fill slug when the user types the name, but ONLY if the
     * user hasn't manually edited the slug field yet.
     */
    public function updatedBrandName(): void
    {
        if ($this->editingId === null || $this->brandSlug === '') {
            $this->brandSlug = Str::slug($this->brandName);
        }
    }

    public function save(): void
    {
        $this->validate([
            'brandName' => ['required', 'string', 'min:1', 'max:120'],
            'brandSlug' => ['required', 'string', 'min:1', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ], [
            'brandSlug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
        ]);

        $tenantId = $this->tenantId();
        if (! $tenantId) {
            $this->addError('general', 'No active shop.');
            return;
        }

        $slug = Str::slug($this->brandSlug);

        // Uniqueness check (scoped to tenant)
        $duplicate = Brand::where('tenant_id', $tenantId)
            ->where('brand_slug', $slug)
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($duplicate) {
            $this->addError('brandSlug', 'That slug is already used by another brand.');
            return;
        }

        $data = [
            'brand_name' => trim($this->brandName),
            'brand_slug' => $slug,
        ];

        if ($this->editingId) {
            $brand = Brand::where('tenant_id', $tenantId)->find($this->editingId);
            if (! $brand) {
                $this->closeForm();
                session()->flash('error', 'Brand not found.');
                return;
            }

            $brand->update($data);
            session()->flash('success', "{$brand->brand_name} updated.");
        } else {
            $data['tenant_id'] = $tenantId;
            $brand = Brand::create($data);
            session()->flash('success', "{$brand->brand_name} added.");
        }

        unset($this->brands, $this->counts);
        $this->closeForm();
    }

    // ─────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $brand = Brand::where('tenant_id', $this->tenantId())
            ->withCount('products')
            ->find($id);

        if (! $brand) {
            return;
        }

        $this->confirmingDeleteId           = $brand->id;
        $this->confirmingDeleteName         = $brand->brand_name;
        $this->confirmingDeleteProductCount = (int) $brand->products_count;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId           = null;
        $this->confirmingDeleteName         = null;
        $this->confirmingDeleteProductCount = 0;
    }

    public function deleteBrand(): void
    {
        if (! $this->confirmingDeleteId) {
            return;
        }

        $brand = Brand::where('tenant_id', $this->tenantId())
            ->find($this->confirmingDeleteId);

        if (! $brand) {
            $this->cancelDelete();
            return;
        }

        $name = $brand->brand_name;
        $brand->delete(); // products.brand_id is nullOnDelete → products keep working

        unset($this->brands, $this->counts);
        $this->cancelDelete();

        session()->flash('success', "{$name} removed.");
    }
};