<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithFileUploads;

    public int $tenantId;

    public string $shop_name = '';
    public string $shop_email = '';
    public string $phone = '';
    public string $address = '';

    public ?float $latitude = null;
    public ?float $longitude = null;

    public $logo;
    public ?string $existingLogo = null;
    public bool $removeExistingLogo = false;

    public array $business_hours = [];
    public array $permit_documents = [];
    public array $existingAttachments = [];

    public array $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    public array $timeOptions = [];

    public function mount(): void
    {
        $user = Auth::user();
        $tenant = $user?->currentTenant();

        if (! $tenant) {
            abort(403, 'No active shop selected.');
        }

        $this->timeOptions = $this->generateTimeOptions();
        $this->tenantId = $tenant->id;

        $this->shop_name  = (string) $tenant->name;
        $this->shop_email = (string) $tenant->email;
        $this->phone      = $this->normalizePhone((string) $tenant->phone);
        $this->address    = (string) $tenant->address;
        $this->latitude   = $tenant->latitude  !== null ? (float) $tenant->latitude  : null;
        $this->longitude  = $tenant->longitude !== null ? (float) $tenant->longitude : null;

        $this->existingLogo        = $tenant->logo ?: null;
        $this->business_hours      = is_array($tenant->business_hours)
            ? $tenant->business_hours
            : $this->defaultBusinessHours();
        $this->existingAttachments = is_array($tenant->attachment) ? $tenant->attachment : [];
    }

    protected function ownedTenantQuery(int $tenantId)
    {
        $userId = Auth::id();

        return Tenant::query()
            ->whereKey($tenantId)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('users', fn ($qq) => $qq->where('users.id', $userId));
            });
    }

    #[Computed]
    public function tenant(): Tenant
    {
        return $this->ownedTenantQuery($this->tenantId)->firstOrFail();
    }

    // ─────────────────────────────────────────
    // Map / location callbacks
    // ─────────────────────────────────────────

    public function setLatLng(float $lat, float $lng): void
    {
        $this->latitude  = round($lat, 7);
        $this->longitude = round($lng, 7);
        $this->resetValidation(['latitude', 'longitude']);
    }

    public function clearLatLng(): void
    {
        $this->latitude  = null;
        $this->longitude = null;
    }

    public function setAddressFromMap(string $address): void
    {
        $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($address)) ?? '');

        if ($clean !== '' && mb_strlen($clean) <= 500) {
            $this->address = $clean;
        }
    }

    // ─────────────────────────────────────────
    // Phone normalization
    // ─────────────────────────────────────────

    public function updatedPhone(string $value): void
    {
        $normalized = $this->normalizePhone($value);

        if ($normalized !== '' && $normalized !== $value) {
            $this->phone = $normalized;
        }
    }

    protected function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if ($digits === null || $digits === '') {
            return '';
        }

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10 || ! str_starts_with($digits, '9')) {
            return $digits;
        }

        return '+63 ' . substr($digits, 0, 3) . ' ' . substr($digits, 3, 4) . ' ' . substr($digits, 7, 3);
    }

    // ─────────────────────────────────────────
    // Save
    // ─────────────────────────────────────────

    public function save()
    {
        $user = Auth::user();
        if (! $user) abort(403, 'Unauthorized');

        // Permit docs are required only when the shop has no docs on file.
        $permitRule = empty($this->existingAttachments) ? 'required' : 'nullable';

        // Normalize phone before validating
        $this->phone = $this->normalizePhone($this->phone);

        $this->validate([
            'shop_name'              => ['required', 'string', 'min:2', 'max:255'],
            'shop_email'             => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('tenants', 'email')->ignore($this->tenantId),
            ],
            'phone'                  => [
                'required',
                'string',
                'regex:/^\+63\s9\d{2}\s\d{4}\s\d{3}$/',
            ],
            'address'                => ['required', 'string', 'min:10', 'max:500'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
            'logo'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'business_hours'         => ['nullable', 'array'],
            'business_hours.*.open'  => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'business_hours.*.close' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'permit_documents'       => [$permitRule, 'array', 'max:5'],
            'permit_documents.*'     => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4048'],
        ], [
            'phone.required'            => 'Enter your business contact number.',
            'phone.regex'               => 'Use a valid PH mobile number, e.g. +63 977 2854 041.',
            'latitude.between'          => 'Latitude must be between -90 and 90.',
            'longitude.between'         => 'Longitude must be between -180 and 180.',
            'shop_email.unique'         => 'That email is already used by another shop.',
            'permit_documents.required' => 'Upload at least one business permit or document for verification.',
            'permit_documents.*.mimes'  => 'Permits must be PDF, JPG, PNG, or WEBP files.',
            'permit_documents.*.max'    => 'Each permit file must be 4 MB or smaller.',
        ]);

        foreach ($this->business_hours as $day => $hours) {
            $open  = $hours['open']  ?? null;
            $close = $hours['close'] ?? null;

            if ($open && $close && $open === $close) {
                $this->addError("business_hours.{$day}.close", "{$day}: open and close can't be the same.");
                return;
            }
        }

        DB::transaction(function () use ($user) {
            $tenant = $this->ownedTenantQuery($this->tenantId)->firstOrFail();

            $oldLogo = $tenant->logo;
            $logoPath = $oldLogo;

            if ($this->logo) {
                if ($oldLogo) Storage::disk('public')->delete($oldLogo);
                $logoPath = $this->logo->store('tenant-logos', 'public');
            } elseif ($this->removeExistingLogo && $oldLogo) {
                Storage::disk('public')->delete($oldLogo);
                $logoPath = null;
            }

            $paths = $this->existingAttachments;

            foreach ($this->permit_documents as $file) {
                if ($file) $paths[] = $file->store('tenant-documents', 'public');
            }

            $tenant->update([
                'name'                     => trim(strip_tags($this->shop_name)),
                'slug'                     => $this->uniqueSlug($this->shop_name, $tenant->id),
                'phone'                    => trim(strip_tags($this->phone)),
                'email'                    => strtolower(trim($this->shop_email)),
                'address'                  => trim(strip_tags($this->address)),
                'latitude'                 => $this->latitude,
                'longitude'                => $this->longitude,
                'logo'                     => $logoPath,
                'business_hours'           => $this->normalizeBusinessHours($this->business_hours),
                'attachment'               => $paths ?: null,
                'business_setup_completed' => true,
            ]);
        });

        $this->logo               = null;
        $this->permit_documents   = [];
        $this->removeExistingLogo = false;
        $this->resetValidation();

        $this->mount();

        session()->flash('success', 'Shop updated successfully.');
    }

    // ─────────────────────────────────────────
    // File removals
    // ─────────────────────────────────────────

    public function removeLogo(): void
    {
        $this->logo = null;
        $this->resetValidation('logo');
    }

    public function removeExistingLogo(): void
    {
        $this->existingLogo       = null;
        $this->removeExistingLogo = true;
    }

    public function removePermitDocument(int $index): void
    {
        if (! isset($this->permit_documents[$index])) return;

        unset($this->permit_documents[$index]);
        $this->permit_documents = array_values($this->permit_documents);

        $this->resetValidation('permit_documents');
        $this->resetValidation('permit_documents.*');
    }

    public function removeExistingAttachment(int $index): void
    {
        if (! isset($this->existingAttachments[$index])) return;

        $path = $this->existingAttachments[$index];
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);

        $this->resetValidation('permit_documents');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($name)) ?: 'shop';
        $slug = $base;
        $i    = 2;

        while (
            Tenant::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function defaultBusinessHours(): array
    {
        return array_fill_keys($this->dayNames, ['open' => '09:00', 'close' => '18:00']);
    }

    protected function normalizeBusinessHours(array $hours): array
    {
        $normalized = [];

        foreach ($this->dayNames as $day) {
            $entry = $hours[$day] ?? ['open' => '09:00', 'close' => '18:00'];
            $normalized[$day] = [
                'open'  => (string) ($entry['open'] ?? '09:00'),
                'close' => (string) ($entry['close'] ?? '18:00'),
            ];
        }

        return $normalized;
    }

    protected function generateTimeOptions(): array
    {
        $slots = [];
        $start = strtotime('00:00');

        for ($i = 0; $i < 48; $i++) {
            $time = date('H:i', $start + ($i * 60 * 30));
            $slots[$time] = $time;
        }

        return $slots;
    }
};