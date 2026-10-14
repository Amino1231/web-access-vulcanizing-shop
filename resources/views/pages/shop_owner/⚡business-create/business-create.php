<?php

use App\Enums\VerificationStatus;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithFileUploads;

    public string $shop_name = '';
    public string $shop_email = '';
    public string $phone = '';
    public string $address = '';

    public ?float $latitude = null;
    public ?float $longitude = null;

    public $logo;
    public array $business_hours = [];
    public array $permit_documents = [];

    public array $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    public array $timeOptions = [];

    public function mount(): void
    {
        $this->timeOptions = $this->generateTimeOptions();
        $this->business_hours = $this->defaultBusinessHours();

        $user = Auth::user();
        $this->shop_email = (string) ($user?->email ?? '');
        $this->phone      = (string) ($user?->phone ?? '');
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
    // Save
    // ─────────────────────────────────────────

    public function save()
    {
        $user = Auth::user();
        if (! $user) abort(403, 'Unauthorized');

        $this->validate([
            'shop_name'              => ['required', 'string', 'min:2', 'max:255'],
            'shop_email'             => ['required', 'email', 'max:255'],
            'phone'                  => ['required', 'string', 'min:7', 'max:30'],
            'address'                => ['required', 'string', 'min:10', 'max:500'],
            'latitude'               => ['required', 'numeric', 'between:-90,90'],
            'longitude'              => ['required', 'numeric', 'between:-180,180'],
            'logo'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'business_hours'         => ['nullable', 'array'],
            'business_hours.*.open'  => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'business_hours.*.close' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'permit_documents'       => ['required', 'array', 'min:1', 'max:5'],
            'permit_documents.*'     => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4048'],
        ], [
            'latitude.required'         => 'Drop a pin on the map to set your shop location.',
            'longitude.required'        => 'Drop a pin on the map to set your shop location.',
            'latitude.between'          => 'Latitude must be between -90 and 90.',
            'longitude.between'         => 'Longitude must be between -180 and 180.',
            'permit_documents.required' => 'Upload at least one business permit or document for verification.',
            'permit_documents.min'      => 'Upload at least one business permit or document for verification.',
            'permit_documents.*.mimes'  => 'Permits must be PDF, JPG, PNG, or WEBP files.',
            'permit_documents.*.max'    => 'Each permit file must be 4 MB or smaller.',
        ]);

        // ─── Hours sanity: close must be after open ───
        foreach ($this->business_hours as $day => $hours) {
            $open  = $hours['open']  ?? null;
            $close = $hours['close'] ?? null;

            if ($open && $close && $open === $close) {
                $this->addError("business_hours.{$day}.close", "{$day}: open and close can't be the same.");
                return;
            }
        }

        $newTenant = DB::transaction(function () use ($user) {
            $logoPath = $this->logo
                ? $this->logo->store('tenant-logos', 'public')
                : null;

            $paths = [];
            foreach ($this->permit_documents as $file) {
                if ($file) $paths[] = $file->store('tenant-documents', 'public');
            }

            $tenant = Tenant::create([
                'user_id'                  => $user->id,
                'name'                     => trim(strip_tags($this->shop_name)),
                'slug'                     => $this->uniqueSlug($this->shop_name),
                'phone'                    => trim(strip_tags($this->phone)),
                'email'                    => strtolower(trim($this->shop_email)),
                'address'                  => trim(strip_tags($this->address)),
                'latitude'                 => $this->latitude,
                'longitude'                => $this->longitude,
                'logo'                     => $logoPath,
                'business_hours'           => $this->normalizeBusinessHours($this->business_hours),
                'attachment'               => $paths ?: null,
                'business_setup_completed' => true,
                'verification_status'      => VerificationStatus::Pending->value,
                'submitted_at'             => now(),
                'is_active'                => false,
            ]);

            $user->tenants()->syncWithoutDetaching([$tenant->id]);

            return $tenant;
        });

        session(['active_tenant_id' => $newTenant->id]);

        $this->logo             = null;
        $this->permit_documents = [];
        $this->resetValidation();

        session()->flash('success', 'New shop submitted for review. It will appear in your shop switcher.');

        return redirect()->route('owner.business_status');
    }

    public function removeLogo(): void
    {
        $this->logo = null;
        $this->resetValidation('logo');
    }

    public function removePermitDocument(int $index): void
    {
        if (! isset($this->permit_documents[$index])) return;

        unset($this->permit_documents[$index]);
        $this->permit_documents = array_values($this->permit_documents);

        $this->resetValidation('permit_documents');
        $this->resetValidation('permit_documents.*');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug(trim($name)) ?: 'shop';
        $slug = $base;
        $i = 2;

        while (Tenant::where('slug', $slug)->exists()) {
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

    /**
     * Normalize a Philippine phone number to "+63 9XX XXXX XXX".
     * Accepts: +639XXXXXXXXX, 639XXXXXXXXX, 09XXXXXXXXX, 9XXXXXXXXX, and
     * with spaces / dashes / dots / parens in any position.
     */
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
};