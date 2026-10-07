<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|string|min:8',
    ];

    protected array $messages = [
        'email.required' => 'Email is required.',
        'email.email' => 'Enter a valid email address.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
    ];

    public function login()
    {
        $this->validate();
        $this->email = trim(Str::lower($this->email));

        $key = $this->email . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Too many login attempts. Try again in {$seconds} seconds.");
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->addError('email', 'Invalid email or password.');
            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();

        return $this->redirectBasedOnRole();
    }

    /**
     * Send the user to the correct dashboard based on their Spatie role.
     */
    protected function redirectBasedOnRole()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Spatie: check roles in priority order (highest privilege first)
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('owner')) {
            // Owners must have an approved shop to access the dashboard.
            // Adjust 'shop' and 'is_approved' to match your actual relation/column.
            $shop = $user->shop ?? null;

            if (! $shop || ! ($shop->is_approved ?? false)) {
                return redirect()->route('owner.business_setup');
            }

            return redirect()->route('owner.dashboard');
        }

        if ($user->hasRole('employee')) {
            // You don't have an employee.dashboard route yet.
            // Point employees to the owner dashboard for now, or create their own.
            return redirect()->route('owner.dashboard');
        }

        if ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
        }

        // No role assigned — log them out and tell them why.
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->addError('email', 'Your account has no assigned role. Please contact support.');

        return null;
    }
};