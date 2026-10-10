<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $errorMessage = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    protected function rules(): array
    {
        return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    protected array $messages = [
        'password.confirmed' => 'Passwords do not match.',
        'password.min'       => 'Password must be at least 8 characters.',
    ];

    public function resetPassword()
    {
        $this->validate();
        $this->errorMessage = '';

        $status = Password::reset(
            [
                'email'                 => $this->email,
                'password'              => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token'                 => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('success', 'Password updated. You can now sign in.');
        }

        $this->errorMessage = match ($status) {
            Password::INVALID_TOKEN => 'This reset link is no longer valid. Please request a new one.',
            Password::INVALID_USER  => 'We couldn\'t find an account with that email address.',
            default                 => 'We couldn\'t reset your password. Please try again.',
        };
    }
};