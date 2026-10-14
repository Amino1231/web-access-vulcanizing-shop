<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    public string $name = '';
    public string $description = '';
    public string $category_id = '';
    public ?int $duration_minutes = 30;
    public float $price = 0;
    public bool $is_vehicle_service = true;
    public bool $is_active = true;
    public string $status = 'published';

    public string $newCategoryName = '';
    public string $newCategoryDescription = '';

    public function mount(): void
    {
        // Nothing yet
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

        return ServiceCategory::query()
            ->where('tenant_id', $this->tenantId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

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

        if (ServiceCategory::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $this->addError('newCategoryName', 'This category already exists.');
            return;
        }

        try {
            $category = ServiceCategory::create([
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

    public function save(): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            session()->flash('error', 'No tenant account is linked to this owner profile.');
            return;
        }

        $this->validate([
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'category_id'        => [
                'required',
                Rule::exists('service_categories', 'id')->where('tenant_id', $tenantId),
            ],
            'duration_minutes'   => ['nullable', 'integer', 'min:0', 'max:1440'],
            'price'              => ['required', 'numeric', 'min:0'],
            'is_vehicle_service' => ['boolean'],
            'is_active'          => ['boolean'],
            'status'             => ['required', Rule::in(array_column(PostStatus::cases(), 'value'))],
        ]);

        DB::transaction(function () use ($tenantId) {
            $slug = $this->uniqueSlug($tenantId, $this->name);

            $service = Service::create([
                'tenant_id'           => $tenantId,
                'service_category_id' => $this->category_id,
                'name'                => $this->name,
                'description'         => $this->description,
                'duration_minutes'    => $this->duration_minutes,
                'price'               => $this->price,
                'is_vehicle_service'  => $this->is_vehicle_service,
                'is_active'           => $this->is_active,
            ]);

            Post::create([
                'tenant_id'   => $tenantId,
                'service_id'  => $service->id,
                'product_id'  => null,
                'name'        => $service->name,
                'slug'        => $slug,
                'attachment'  => null,
                'image'       => null,
                'price'       => $service->price,
                'description' => $service->description,
                'status'      => $this->status,
            ]);
        });

        // Reset form
        $this->reset([
            'name', 'description', 'category_id',
            'price', 'newCategoryName', 'newCategoryDescription',
        ]);
        $this->duration_minutes = 30;
        $this->is_vehicle_service = true;
        $this->is_active = true;
        $this->status = PostStatus::Published->value;
        $this->resetValidation();

        session()->flash('success', 'Service created successfully.');
        $this->redirectRoute('owner.services', navigate: true);
    }

    protected function uniqueSlug(int $tenantId, string $name): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $i    = 1;

        while (Post::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
};