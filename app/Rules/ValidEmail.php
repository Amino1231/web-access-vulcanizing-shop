<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEmail implements ValidationRule
{
    /**
     * Domains we always reject — reserved for testing, or known disposable.
     */
    protected array $blockedDomains = [
        // RFC reserved / test domains
        'example.com', 'example.org', 'example.net',
        'test.com', 'test.test', 'test.local',
        'localhost', 'localhost.localdomain',

        // Disposable email services
        'mailinator.com', 'tempmail.com', 'temp-mail.org',
        '10minutemail.com', 'guerrillamail.com', 'sharklasers.com',
        'throwawaymail.com', 'yopmail.com', 'trashmail.com',
        'getnada.com', 'maildrop.cc', 'fakeinbox.com',
        'dispostable.com', 'spamgourmet.com', 'mintemail.com',
    ];

    /**
     * Local-part patterns that signal a fake/test address.
     */
    protected array $blockedLocalPatterns = [
        '/^(test|testing|fake|asdf|qwerty|abc|xyz|admin|root|nobody|user|sample)/i',
        '/^(sadasdad|sdasd|asdasd|blah|foobar|foo|bar)/i',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower(trim((string) $value));

        // Basic syntax — belt + suspenders on top of Laravel's `email` rule
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fail('Please enter a valid email address.');
            return;
        }

        $at = strrpos($email, '@');
        if ($at === false || $at === 0 || $at === strlen($email) - 1) {
            $fail('Please enter a valid email address.');
            return;
        }

        $local  = substr($email, 0, $at);
        $domain = substr($email, $at + 1);

        // Local part length
        if (strlen($local) < 2) {
            $fail('Email username is too short.');
            return;
        }

        // Domain must contain at least one dot
        if (! str_contains($domain, '.')) {
            $fail('Email domain must include a valid extension like .com or .ph.');
            return;
        }

        // TLD must be at least 2 chars and alphabetic
        $tld = substr($domain, strrpos($domain, '.') + 1);
        if (strlen($tld) < 2 || ! preg_match('/^[a-z]+$/', $tld)) {
            $fail('Please enter a valid email extension (e.g., .com, .ph).');
            return;
        }

        // Reject TLDs that are all the same letter (blocks .ggg, .ttt, .aaa)
        if (preg_match('/^(.)\1+$/', $tld)) {
            $fail('Please enter a valid email domain.');
            return;
        }

        // Domain total length sanity
        if (strlen($domain) < 5) {
            $fail('Please enter a valid email domain.');
            return;
        }

        // Block known test / disposable domains
        foreach ($this->blockedDomains as $blocked) {
            if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) {
                $fail('Please use a real email address — this provider is not supported.');
                return;
            }
        }

        // Block obvious fake local-parts
        foreach ($this->blockedLocalPatterns as $pattern) {
            if (preg_match($pattern, $local)) {
                $fail('Please use a real email address.');
                return;
            }
        }
    }
}