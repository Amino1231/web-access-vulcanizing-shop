<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $gender = null;
    public ?string $birth_date = null;
    public $avatar = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name       = (string) $user->name;
        $this->email      = (string) $user->email;
        $this->phone      = $this->normalizePhone((string) $user->phone);
        $this->address    = (string) $user->address;
        $this->gender     = $user->gender;
        $this->birth_date = $user->birth_date?->format('Y-m-d');
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
    // Map callback
    // ─────────────────────────────────────────

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

        // Normalize phone before validating
        $this->phone = $this->normalizePhone((string) $this->phone);

        $validated = $this->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'      => ['nullable', 'string', 'regex:/^\+63\s9\d{2}\s\d{4}\s\d{3}$/'],
            'address'    => ['nullable', 'string', 'max:500'],
            'gender'     => ['nullable', 'in:male,female,prefer_not_to_say'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'avatar'     => ['nullable', 'image', 'max:2048'],
        ], [
            'phone.regex' => 'Use a valid PH mobile number, e.g. +63 977 2854 041.',
        ]);

        $userPayload = [
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?: null,
            'address'    => $validated['address'],
            'gender'     => $validated['gender'],
            'birth_date' => $validated['birth_date'],
        ];

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userPayload['avatar'] = $this->avatar->store('avatars', 'public');
        }

        $user->update($userPayload);

        $this->reset(['avatar']);

        $this->dispatch('notify', type: 'success', message: 'Profile updated.');
        $this->redirectRoute('owner.profile');
    }
};