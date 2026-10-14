<?php

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.employee')] #[Title('Update Profile · Employee')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $gender = '';
    public string $birth_date = '';

    public $avatar = null;

    // ─── Map picker state ──────────────────
    public bool $showMapPicker = false;

    public function mount(): void
    {
        $user = Auth::user();
        $employee = $user?->currentEmployee();

        abort_unless($user && $employee, 403);

        $this->name       = (string) $user->name;
        $this->email      = (string) $user->email;
        $this->phone      = $this->normalizePhone((string) ($employee->phone ?? $user->phone ?? ''));
        $this->address    = (string) ($user->address ?? '');
        $this->gender     = (string) ($user->gender ?? '');
        $this->birth_date = $user->birth_date?->format('Y-m-d') ?? '';
    }

    #[Computed]
    public function employee(): ?Employee
    {
        return Auth::user()?->currentEmployee();
    }

    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    protected function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'min:2', 'max:120'],
            'email'      => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->user->id)],
            'phone'      => ['nullable', 'string', 'max:30'],
            'address'    => ['nullable', 'string', 'max:500'],
            'gender'     => ['nullable', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'avatar'     => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function updatedPhone(string $value): void
    {
        $this->phone = $this->normalizePhone($value);
    }

    protected function normalizePhone(?string $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '63')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        $digits = substr($digits, 0, 10);

        if ($digits === '') {
            return '';
        }

        $formatted = '+63';
        if (strlen($digits) > 0) $formatted .= ' ' . substr($digits, 0, 3);
        if (strlen($digits) > 3) $formatted .= ' ' . substr($digits, 3, 3);
        if (strlen($digits) > 6) $formatted .= ' ' . substr($digits, 6, 4);

        return $formatted;
    }

    // ─────────────────────────────────────────
    // Map picker
    // ─────────────────────────────────────────

    public function openMapPicker(): void
    {
        $this->showMapPicker = true;
    }

    public function closeMapPicker(): void
    {
        $this->showMapPicker = false;
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $user = $this->user;
                $employee = $this->employee;

                $avatarPath = $user->avatar;

                if ($this->avatar) {
                    $avatarPath = $this->avatar->store('avatars', 'public');
                }

                $phone = $this->normalizePhone($this->phone);

                $user->update([
                    'name'       => trim($this->name),
                    'email'      => trim(strtolower($this->email)),
                    'phone'      => $phone ?: null,
                    'address'    => trim($this->address) ?: null,
                    'gender'     => $this->gender ?: null,
                    'birth_date' => $this->birth_date ?: null,
                    'avatar'     => $avatarPath,
                ]);

                if ($employee) {
                    $employee->update(['phone' => $phone ?: null]);
                }
            });

            unset($this->employee, $this->user);
            $this->avatar = null;

            $this->dispatch('notify', type: 'success', message: 'Profile updated.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'Could not save changes.');
        }
    }
};