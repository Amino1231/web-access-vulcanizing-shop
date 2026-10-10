<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $email = '';
    public bool $sent = false;

    protected array $rules = [
        'email' => 'required|email',
    ];

    protected array $messages = [
        'email.required' => 'Please enter your email address.',
        'email.email'    => 'Enter a valid email address.',
    ];

    public function sendResetLink()
    {
        $this->validate();

        // Laravel's broker throttles per-user (60s) — no extra limiter needed
        Password::sendResetLink(['email' => $this->email]);

        // Always show success to prevent email enumeration
        $this->sent = true;
    }
};