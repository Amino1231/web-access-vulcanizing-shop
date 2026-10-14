<?php

use App\Models\ServiceCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    // ─── Create form ───────────────────────
    public string $name = '';
    public string $description = '';

    // ─── Edit modal ────────────────────────
    public bool $showEditModal = false;
    public ?int $editingId = null;
    public string $editingName = '';
    public string $editingDescription = '';

    // ─── Delete modal ──────────────────────
    public ?int $confirmingDeleteId = null;
    public ?string $confirmingDeleteName = null;

    public function mount(): void {}

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
            ->get(['id', 'name', 'slug', 'description'])
            ->map(fn ($c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'slug'        => $c->slug,
                'description' => $c->description,
            ])
            ->all();
    }

    // ─────────────────────────────────────────
    // Create
    // ─────────────────────────────────────────

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

        if (ServiceCategory::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $this->addError('name', 'This category already exists for your shop.');
            return;
        }

        try {
            ServiceCategory::create([
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
        unset($this->categories);

        session()->flash('success', 'Category created successfully.');
    }

    // ─────────────────────────────────────────
    // Edit
    // ─────────────────────────────────────────

    public function openEdit(int $id): void
    {
        $category = ServiceCategory::where('tenant_id', $this->tenantId)->find($id);
        if (! $category) {
            return;
        }

        $this->resetValidation();
        $this->editingId = $category->id;
        $this->editingName = (string) $category->name;
        $this->editingDescription = (string) ($category->description ?? '');
        $this->showEditModal = true;
    }

    public function closeEdit(): void
    {
        $this->showEditModal = false;
        $this->editingId = null;
        $this->editingName = '';
        $this->editingDescription = '';
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId || ! $this->editingId) {
            $this->closeEdit();
            return;
        }

        $this->validate([
            'editingName'        => ['required', 'string', 'max:255'],
            'editingDescription' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = ServiceCategory::where('tenant_id', $tenantId)->find($this->editingId);
        if (! $category) {
            $this->closeEdit();
            session()->flash('error', 'Category not found.');
            return;
        }

        $categoryName = trim($this->editingName);
        $slug = Str::slug($categoryName) ?: 'category';

        $duplicate = ServiceCategory::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->where('id', '!=', $category->id)
            ->exists();

        if ($duplicate) {
            $this->addError('editingName', 'Another category already uses this name.');
            return;
        }

        try {
            $category->update([
                'name'        => $categoryName,
                'slug'        => $slug,
                'description' => trim($this->editingDescription) ?: null,
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                $this->addError('editingName', 'Another category already uses this name.');
                return;
            }
            throw $e;
        }

        unset($this->categories);
        $this->closeEdit();

        session()->flash('success', 'Category updated successfully.');
    }

    // ─────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $category = ServiceCategory::where('tenant_id', $this->tenantId)->find($id);
        if (! $category) {
            return;
        }

        $this->confirmingDeleteId = $category->id;
        $this->confirmingDeleteName = $category->name;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->confirmingDeleteName = null;
    }

    public function delete(): void
    {
        if (! $this->confirmingDeleteId) {
            return;
        }

        $category = ServiceCategory::where('tenant_id', $this->tenantId)
            ->find($this->confirmingDeleteId);

        if (! $category) {
            $this->cancelDelete();
            return;
        }

        // Block deletion if services are still using this category.
        if (method_exists($category, 'services') && $category->services()->exists()) {
            $name = $category->name;
            $this->cancelDelete();
            session()->flash('error', "Cannot delete \"{$name}\" — it still has services assigned.");
            return;
        }

        $name = $category->name;
        $category->delete();

        unset($this->categories);
        $this->cancelDelete();

        session()->flash('success', "Category \"{$name}\" deleted.");
    }
};