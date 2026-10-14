<?php

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.shop_owner')] class extends Component
{
    public int $serviceId;

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

    public function mount(int $service): void
    {
        $tenantId = $this->tenantId;

        if (! $tenantId) {
            abort(403, 'No tenant account linked to this owner.');
        }

        $serviceModel = Service::query()
            ->where('tenant_id', $tenantId)
            ->with('post:id,service_id,status')
            ->findOrFail($service);

        $this->serviceId          = $serviceModel->id;
        $this->name               = $serviceModel->name;
        $this->description        = (string) $serviceModel->description;
        $this->category_id        = (string) $serviceModel->service_category_id;
        $this->duration_minutes   = (int) $serviceModel->duration_minutes;
        $this->price              = (float) $serviceModel->price;
        $this->is_vehicle_service = (bool) $serviceModel->is_vehicle_service;
        $this->is_active          = (bool) $serviceModel->is_active;

        $this->status = $serviceModel->post?->status?->value
            ?? PostStatus::Published->value;
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
        if (! $tenantId) return;

        $this->validate([
            'newCategoryName'        => ['required', 'string', 'min:2', 'max:255'],
            'newCategoryDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $categoryName = trim($this->newCategoryName);
        $slug = Str::slug($categoryName) ?: 'category';

        if (ServiceCategory::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $this->addError('newCategoryName', 'This category already exists.');
            return;
        }

        $category = ServiceCategory::create([
            'tenant_id'   => $tenantId,
            'name'        => $categoryName,
            'slug'        => $slug,
            'description' => trim($this->newCategoryDescription) ?: null,
        ]);

        $this->category_id            = (string) $category->id;
        $this->newCategoryName        = '';
        $this->newCategoryDescription = '';
        unset($this->categories);
    }

    public function save(): void
    {
        $tenantId = $this->tenantId;
        if (! $tenantId) {
            session()->flash('error', 'No tenant account linked.');
            return;
        }

        $service = Service::where('tenant_id', $tenantId)->findOrFail($this->serviceId);

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

        DB::transaction(function () use ($tenantId, $service) {
            $slug = Str::slug($this->name) ?: $service->slug ?? 'service';
            $original = $slug;
            $i = 1;

            while (
                Post::where('tenant_id', $tenantId)
                    ->where('slug', $slug)
                    ->where('service_id', '!=', $service->id)
                    ->exists()
            ) {
                $slug = $original . '-' . $i++;
            }

            $service->update([
                'service_category_id' => $this->category_id,
                'name'                => $this->name,
                'description'         => $this->description,
                'duration_minutes'    => $this->duration_minutes,
                'price'               => $this->price,
                'is_vehicle_service'  => $this->is_vehicle_service,
                'is_active'           => $this->is_active,
            ]);

            $post = Post::firstOrNew([
                'tenant_id'  => $tenantId,
                'service_id' => $service->id,
            ]);

            $post->fill([
                'product_id'  => null,
                'name'        => $service->name,
                'slug'        => $slug,
                'attachment'  => null,
                'image'       => null,
                'price'       => $service->price,
                'description' => $service->description,
                'status'      => $this->status,
            ]);

            if ($this->status === PostStatus::Published->value) {
                $post->archived_at = null;
            }

            $post->save();
        });

        session()->flash('success', 'Service updated successfully.');
        $this->redirectRoute('owner.services', navigate: true);
    }
};