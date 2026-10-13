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

    public $avatar = null; // uploaded file

    public function mount(): void
    {
        $user = Auth::user();
        $employee = $user?->currentEmployee();

        abort_unless($user && $employee, 403);

        $this->name       = (string) $user->name;
        $this->email      = (string) $user->email;
        $this->phone      = (string) ($employee->phone ?? $user->phone ?? '');
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
            'address'    => ['nullable', 'string', 'max:255'],
            'gender'     => ['nullable', 'in:male,female,other'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'avatar'     => ['nullable', 'image', 'max:2048'],
        ];
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

                $user->update([
                    'name'       => trim($this->name),
                    'email'      => trim(strtolower($this->email)),
                    'phone'      => $this->phone ?: null,
                    'address'    => $this->address ?: null,
                    'gender'     => $this->gender ?: null,
                    'birth_date' => $this->birth_date ?: null,
                    'avatar'     => $avatarPath,
                ]);

                // Phone also lives on the Employee row — keep in sync
                if ($employee) {
                    $employee->update(['phone' => $this->phone ?: null]);
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