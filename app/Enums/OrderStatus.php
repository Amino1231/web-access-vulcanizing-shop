<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Ready      = 'ready';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    // ─────────────────────────────────────────
    // Display
    // ─────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::Processing => 'Processing',
            self::Ready      => 'Ready',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'amber',
            self::Processing => 'blue',
            self::Ready      => 'indigo',
            self::Completed  => 'emerald',
            self::Cancelled  => 'red',
        };
    }

    public function badge(): array
    {
        return match ($this) {
            self::Pending => [
                'bg'   => 'bg-amber-50 dark:bg-amber-500/10',
                'text' => 'text-amber-700 dark:text-amber-300',
                'dot'  => 'bg-amber-500',
            ],
            self::Processing => [
                'bg'   => 'bg-blue-50 dark:bg-blue-500/10',
                'text' => 'text-blue-700 dark:text-blue-300',
                'dot'  => 'bg-blue-500',
            ],
            self::Ready => [
                'bg'   => 'bg-indigo-50 dark:bg-indigo-500/10',
                'text' => 'text-indigo-700 dark:text-indigo-300',
                'dot'  => 'bg-indigo-500',
            ],
            self::Completed => [
                'bg'   => 'bg-emerald-50 dark:bg-emerald-500/10',
                'text' => 'text-emerald-700 dark:text-emerald-300',
                'dot'  => 'bg-emerald-500',
            ],
            self::Cancelled => [
                'bg'   => 'bg-red-50 dark:bg-red-500/10',
                'text' => 'text-red-700 dark:text-red-300',
                'dot'  => 'bg-red-500',
            ],
        };
    }

    // ─────────────────────────────────────────
    // State transitions
    // ─────────────────────────────────────────

    /** Can this order be accepted (Pending → Processing)? */
    public function canBeProcessed(): bool
    {
        return $this === self::Pending;
    }

    /** Can this order be marked ready (Processing → Ready)? */
    public function canBeMarkedReady(): bool
    {
        return $this === self::Processing;
    }

    /** Can this order be completed (Ready → Completed)? */
    public function canBeCompleted(): bool
    {
        return $this === self::Ready;
    }

    /** Can this order be cancelled? */
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Pending, self::Processing], true);
    }

    /** Is this order done — no more transitions? */
    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}