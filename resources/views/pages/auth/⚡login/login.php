<?php

use App\Enums\VerificationStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email'    => 'required|email',
        'password' => 'required|string|min:8',
    ];

    protected array $messages = [
        'email.required'    => 'Email is required.',
        'email.email'       => 'Enter a valid email address.',
        'password.required' => 'Password is required.',
        'password.min'      => 'Password must be at least 8 characters.',
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
        session()->forget('active_tenant_id'); // ← prevent stale shop from previous session

        return $this->redirectBasedOnRole();
    }

    /**
     * Send the authenticated user to the correct dashboard based on their Spatie role.
     */
    protected function redirectBasedOnRole()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ─── Admin ───────────────────────────────
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // ─── Owner ───────────────────────────────
        if ($user->hasRole('owner')) {
            // One user can own many shops — grab the first one.
            $tenant = $user->ownedTenants()->latest()->first()
                ?? $user->tenants()->latest()->first();

            // No shop yet → force them through business setup
            if (! $tenant) {
                return redirect()->route('owner.business_setup');
            }

            // Shop exists but isn't approved yet → send to status page
            if ($tenant->verification_status !== VerificationStatus::Approved->value
                && $tenant->verification_status !== VerificationStatus::Approved) {
                return redirect()->route('owner.business_status');
            }

            // Shop exists and is approved → dashboard
            return redirect()->route('owner.dashboard');
        }

        // ─── Employee ────────────────────────────
        if ($user->hasRole('employee')) {
            // Must have an Employee record (assigned to a shop) to use the portal
            $employee = $user->currentEmployee();

            if (! $employee) {
                $this->logoutWithError(
                    'Your account is not assigned to any shop yet. Please contact the shop owner.'
                );
                return null;
            }

            // Optional: block if the shop isn't approved
            $shop = $employee->tenant;
            $shopStatus = $shop?->verification_status instanceof VerificationStatus
                ? $shop->verification_status->value
                : $shop?->verification_status;

            if ($shop && $shopStatus !== VerificationStatus::Approved->value) {
                $this->logoutWithError(
                    'The shop you belong to is not yet approved. Please contact the shop owner.'
                );
                return null;
            }

            return redirect()->route('employee.dashboard');
        }

        // ─── Customer ────────────────────────────
        if ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
        }

        // ─── No role assigned → log out and explain ────
        $this->logoutWithError('Your account has no assigned role. Please contact support.');

        return null;
    }

    /**
     * Invalidate the session and surface a friendly error on the login form.
     */
    protected function logoutWithError(string $message): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->addError('email', $message);
    }
};