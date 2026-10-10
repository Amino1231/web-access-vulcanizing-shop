<?php

use App\Models\User;
use App\Rules\ValidEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $errorMessage = '';

    // ─────────────────────────────────────────
    // Rate-limiting configuration
    // ─────────────────────────────────────────

    /** Failed-attempt cap per IP within an hour. */
    private const MAX_FAILED_ATTEMPTS = 10;

    private const FAILED_ATTEMPT_DECAY = 3600; // 1 hour

    /** Successful registrations per IP within the window. */
    private const MAX_SUCCESSFUL_PER_IP = 1;

    private const SUCCESS_DECAY = 86400; // 24 hours

    // ─────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                // Unicode letters, combining marks, spaces, apostrophes, hyphens, periods only
                'regex:/^[\p{L}\p{M}\'\-\s\.]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                'unique:users,email',
                new ValidEmail,
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    protected array $messages = [
        'name.required' => 'Please enter your full name.',
        'name.min' => 'Your name must be at least 2 characters.',
        'name.max' => 'Your name is too long.',
        'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
        'email.required' => 'Please enter your email address.',
        'email.email' => 'That doesn\'t look like a valid email address.',
        'email.unique' => 'This email is already registered. Try signing in instead.',
        'password.required' => 'Please choose a password.',
        'password.confirmed' => 'Your passwords don\'t match.',
    ];

    public function updated(string $field): void
    {
        $this->errorMessage = '';

        if (in_array($field, ['name', 'email', 'password', 'password_confirmation'], true)) {
            try {
                $this->validateOnly($field);
            } catch (ValidationException $e) {
                // Livewire handles the messages — we don't need to do anything here
            }
        }
    }

    public function register()
    {
        $this->errorMessage = '';
        $ip = request()->ip();

        $failedKey = 'register-failed:'.$ip;
        $successKey = 'register-success:'.$ip;

        // ─── 1. Block if this IP already registered in the window ─────
        if (RateLimiter::tooManyAttempts($successKey, self::MAX_SUCCESSFUL_PER_IP)) {
            $seconds = RateLimiter::availableIn($successKey);
            $hours = max(1, (int) ceil($seconds / 3600));
            $this->errorMessage = "You've already created an account from this device today. Please try again in {$hours} hour(s).";

            return;
        }

        // ─── 2. Block if too many failed attempts recently ────────────
        if (RateLimiter::tooManyAttempts($failedKey, self::MAX_FAILED_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($failedKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            $this->errorMessage = "Too many attempts. Please try again in {$minutes} minute(s).";

            return;
        }

        // ─── 3. Validate ──────────────────────────────────────────────
        try {
            $this->validate();
        } catch (ValidationException $e) {
            RateLimiter::hit($failedKey, self::FAILED_ATTEMPT_DECAY);
            throw $e;
        }

        // ─── 4. Create user inside transaction ────────────────────────
        try {
            $user = DB::transaction(function () {
                $user = User::create([
                    'name' => $this->sanitizeName($this->name),
                    'email' => $this->normalizeEmail($this->email),
                    'password' => Hash::make($this->password),
                    'is_active' => true,
                ]);

                $user->assignRole('customer');

                return $user;
            });

            // ─── 5. Log in ────────────────────────────────────────────
            Auth::login($user, false);
            session()->regenerate();

            // ─── 6. Record success, clear failures ────────────────────
            RateLimiter::hit($successKey, self::SUCCESS_DECAY);
            RateLimiter::clear($failedKey);

            Log::info('Customer registered', [
                'user_id' => $user->id,
                'ip' => $ip,
            ]);

            return redirect()
                ->route('customer.dashboard')
                ->with('success', 'Welcome! Your account is ready.');
        } catch (Throwable $e) {
            RateLimiter::hit($failedKey, self::FAILED_ATTEMPT_DECAY);

            Log::error('Registration failed', [
                'message' => $e->getMessage(),
                'ip' => $ip,
            ]);

            // Never leak internal exception details to the user
            $this->errorMessage = 'We couldn\'t create your account right now. Please try again in a moment.';
        }
    }

    // ─────────────────────────────────────────
    // Sanitization helpers
    // ─────────────────────────────────────────

    protected function sanitizeName(string $name): string
    {
        // Strip any HTML / tags
        $name = strip_tags($name);

        // Remove control characters and null bytes
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? $name;

        // Collapse multiple whitespace into one
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        // Trim edges
        $name = trim($name);

        // Title-case for consistency
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    protected function normalizeEmail(string $email): string
    {
        // Lowercase, trim, strip any whitespace inside
        $email = strtolower(trim($email));

        return preg_replace('/\s+/', '', $email) ?? $email;
    }
};
