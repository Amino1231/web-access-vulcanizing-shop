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

    public $logo;
    public ?string $existingLogo = null;

    public array $business_hours = [];
    public array $permit_documents = [];
    public array $existingAttachments = [];

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

    public function save(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        $this->validate([
            'shop_name'               => ['required', 'string', 'min:2', 'max:255'],
            'shop_email'              => ['required', 'email', 'max:255'],
            'phone'                   => ['required', 'string', 'min:7', 'max:30'],
            'address'                 => ['required', 'string', 'min:10', 'max:500'],
            'logo'                    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'business_hours'          => ['nullable', 'array'],
            'business_hours.*.open'   => ['nullable', 'string'],
            'business_hours.*.close'  => ['nullable', 'string'],
            'permit_documents'        => ['nullable', 'array', 'max:5'],
            'permit_documents.*'      => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:4048'],
        ]);

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
        });

        // Clear temp uploads
        $this->logo             = null;
        $this->permit_documents = [];
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

        $path = $this->existingAttachments[$index];
        if ($path) Storage::disk('public')->delete($path);

        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);
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
};