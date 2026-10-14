<?php

use App\Enums\VerificationStatus;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
    public ?string $existingLogo = null;

    public array $business_hours = [];
    public array $permit_documents = [];
    public array $existingAttachments = [];
    public array $attachmentsToDelete = [];

    public bool $business_setup_completed = false;

    public array $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    public array $timeOptions = [];

    public function mount(): void
    {
        $this->timeOptions = $this->generateTimeOptions();

        $tenant = Auth::user()?->currentTenant();

        if ($tenant) {
            $this->shop_name       = (string) ($tenant->name ?? '');
            $this->shop_email      = (string) ($tenant->email ?? '');
            $this->phone           = (string) ($tenant->phone ?? '');
            $this->address         = (string) ($tenant->address ?? '');
            $this->latitude        = $tenant->latitude  !== null ? (float) $tenant->latitude  : null;
            $this->longitude       = $tenant->longitude !== null ? (float) $tenant->longitude : null;
            $this->existingLogo    = $tenant->logo ?: null;
            $this->business_hours  = is_array($tenant->business_hours)
                ? $tenant->business_hours
                : $this->defaultBusinessHours();
            $this->existingAttachments = is_array($tenant->attachment) ? $tenant->attachment : [];
            $this->business_setup_completed = (bool) $tenant->business_setup_completed;
        } else {
            $this->shop_email     = (string) (Auth::user()?->email ?? '');
            $this->business_hours = $this->defaultBusinessHours();
        }
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

    public function save(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        // Permit documents required only when the shop has no existing permit on file
        $permitRule = empty($this->existingAttachments) ? 'required' : 'nullable';

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
            'permit_documents'       => [$permitRule, 'array', 'min:1', 'max:5'],
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

        $wasResubmission = false;

        DB::transaction(function () use ($user, &$wasResubmission) {
            $tenant = $user->currentTenant();

            // ─── Decide target verification status ─────────
            $previousStatus = $tenant?->verification_status;
            $previousValue = $previousStatus instanceof VerificationStatus
                ? $previousStatus->value
                : $previousStatus;

            $wasResubmission = in_array($previousValue, ['needs_more_info', 'rejected'], true);

            $targetStatus = $wasResubmission
                ? VerificationStatus::UnderReview->value
                : VerificationStatus::Pending->value;

            // ─── New permit documents ─────────────
            $newPaths = [];
            foreach ($this->permit_documents as $file) {
                if ($file) {
                    $newPaths[] = $file->store('tenant-documents', 'public');
                }
            }

            // ─── Logo resolution ──────────────────
            $oldLogo = $tenant?->logo;

            if ($this->logo) {
                if ($oldLogo) Storage::disk('public')->delete($oldLogo);
                $finalLogo = $this->logo->store('tenant-logos', 'public');
            } elseif ($this->existingLogo === null && $oldLogo) {
                Storage::disk('public')->delete($oldLogo);
                $finalLogo = null;
            } else {
                $finalLogo = $oldLogo;
            }

            // ─── Attachments merge ────────────────
            $keptAttachments = is_array($this->existingAttachments) ? $this->existingAttachments : [];
            $finalAttachments = array_values(array_merge($keptAttachments, $newPaths));

            $payload = [
                'user_id'                  => $user->id,
                'name'                     => trim(strip_tags($this->shop_name)),
                'slug'                     => $this->uniqueSlug($this->shop_name, $tenant?->id),
                'phone'                    => trim(strip_tags($this->phone)),
                'email'                    => strtolower(trim($this->shop_email)),
                'address'                  => trim(strip_tags($this->address)),
                'latitude'                 => $this->latitude,
                'longitude'                => $this->longitude,
                'logo'                     => $finalLogo,
                'business_hours'           => $this->normalizeBusinessHours($this->business_hours),
                'attachment'               => $finalAttachments ?: null,
                'business_setup_completed' => true,
                'verification_status'      => $targetStatus,
                'rejection_reason'         => null,
                'missing_requirements'     => null,
                'submitted_at'             => now(),
                'is_active'                => false,
            ];

            if ($tenant) {
                $tenant->fill($payload);
                $tenant->save();
            } else {
                $newTenant = Tenant::create($payload);
                $user->tenants()->syncWithoutDetaching([$newTenant->id]);
                session(['active_tenant_id' => $newTenant->id]);
            }

            // ─── Deferred attachment deletion ──────
            foreach ($this->attachmentsToDelete as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        });

        // Clear temp uploads
        $this->logo                 = null;
        $this->permit_documents     = [];
        $this->attachmentsToDelete  = [];
        $this->resetValidation();

        session()->flash(
            'success',
            $wasResubmission
                ? 'Thanks! Your updated profile has been sent back to the review team.'
                : 'Business profile submitted for review. Your application is pending admin approval.'
        );

        if (Route::has('owner.business_status')) {
            $this->redirectRoute('owner.business_status', navigate: true);
        } elseif (Route::has('owner.profile')) {
            $this->redirectRoute('owner.profile', navigate: true);
        } else {
            $this->redirectRoute('owner.business_setup', navigate: true);
        }
    }

    // ─────────────────────────────────────────
    // File removal
    // ─────────────────────────────────────────

    public function removeLogo(): void
    {
        $this->logo = null;
        $this->resetValidation('logo');
    }

    public function removeExistingLogo(): void
    {
        $this->existingLogo = null;
    }

    public function removePermitDocument(int $index): void
    {
        if (! isset($this->permit_documents[$index])) {
            return;
        }

        unset($this->permit_documents[$index]);
        $this->permit_documents = array_values($this->permit_documents);

        $this->resetValidation('permit_documents');
        $this->resetValidation('permit_documents.*');
    }

    public function removeExistingAttachment(int $index): void
    {
        if (! isset($this->existingAttachments[$index])) {
            return;
        }

        // Queue for deletion — actual delete happens on save()
        $this->attachmentsToDelete[] = $this->existingAttachments[$index];

        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);

        $this->resetValidation('permit_documents');
        $this->resetValidation('permit_documents.*');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($name)) ?: 'shop-owner-business';
        $slug = $base;
        $i = 2;

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