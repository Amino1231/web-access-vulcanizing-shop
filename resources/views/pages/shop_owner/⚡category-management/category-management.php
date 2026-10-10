<?php

use App\Models\ProductCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    public string $name = '';
    public string $description = '';

    public function mount(): void
    {
        // Nothing to preload — categories are a computed property.
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

        return ProductCategory::query()
            ->where('tenant_id', $this->tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description'])
            ->map(fn ($c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'slug'        => $c->slug,
                'description' => $c->description,
            ])
            ->all();
    }

    public function save(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            session()->flash('error', 'No tenant account is linked to this owner profile.');
            return;
        }

        $this->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $categoryName = trim($this->name);
        $slug = Str::slug($categoryName) ?: 'category';

        // Check for duplicate slug within this tenant
        if (ProductCategory::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $this->addError('name', 'This category already exists for your shop.');
            return;
        }

        try {
            ProductCategory::create([
                'tenant_id'   => $tenantId,
                'name'        => $categoryName,
                'slug'        => $slug,
                'description' => trim($this->description) ?: null,
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                $this->addError('name', 'This category already exists for your shop.');
                return;
            }
            throw $e;
        }

        $this->reset(['name', 'description']);
        $this->resetValidation();
        unset($this->categories);   // force the computed to refresh

        session()->flash('success', 'Category created successfully.');
    }
};