<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.shop_owner')] class extends Component
{
    use WithFileUploads;

    // Account fields
    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $gender = null;
    public ?string $birth_date = null;
    public $avatar = null;

    // Shop fields
    public ?int $tenantId = null;
    public string $shop_name = '';
    public ?string $shop_email = null;
    public ?string $shop_phone = null;
    public ?string $shop_address = null;
    public $shop_logo = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->address = $user->address;
        $this->gender = $user->gender;
        $this->birth_date = $user->birth_date?->format('Y-m-d');

        $tenant = $user->tenant;

        if ($tenant) {
            $this->tenantId = $tenant->id;
            $this->shop_name = $tenant->name;
            $this->shop_email = $tenant->email;
            $this->shop_phone = $tenant->phone;
            $this->shop_address = $tenant->address;
        }
    }

    public function save(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'      => ['nullable', 'string', 'max:11', 'min:11'],
            'address'    => ['nullable', 'string', 'max:255'],
            'gender'     => ['nullable', 'in:male,female,prefer_not_to_say'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'avatar'     => ['nullable', 'image', 'max:2048'],

            'shop_name'    => [$this->tenantId ? 'required' : 'nullable', 'string', 'max:255'],
            'shop_email'   => ['nullable', 'email', 'max:255', $this->tenantId ? Rule::unique('tenants', 'email')->ignore($this->tenantId) : 'nullable'],
            'shop_phone'   => ['nullable', 'string', 'max:11', 'min:11'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'shop_logo'    => ['nullable', 'image', 'max:2048'],
        ]);

        // Update user account
        $userPayload = [
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
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

        // Update shop, if it exists
        if ($this->tenantId) {
            $tenant = Tenant::findOrFail($this->tenantId);

            $tenantPayload = [
                'name'    => $validated['shop_name'],
                'email'   => $validated['shop_email'],
                'phone'   => $validated['shop_phone'],
                'address' => $validated['shop_address'],
            ];

            if ($this->shop_logo) {
                if ($tenant->logo) {
                    Storage::disk('public')->delete($tenant->logo);
                }
                $tenantPayload['logo'] = $this->shop_logo->store('tenant-logos', 'public');
            }

            $tenant->update($tenantPayload);
        }

        $this->reset(['avatar', 'shop_logo']);

        $this->dispatch('notify', type: 'success', message: 'Profile updated.');
        $this->redirectRoute('owner.profile');
    }
};
?>