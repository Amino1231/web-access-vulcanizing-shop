<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Ready      = 'ready';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::Processing => 'Processing',
            self::Ready      => 'Ready for pickup',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
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

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    public function canBeCancelled(): bool
    {
        return $this === self::Pending;
    }

    public function canBeProcessed(): bool
    {
        return $this === self::Pending;
    }

    public function canBeMarkedReady(): bool
    {
        return $this === self::Processing;
    }

    public function canBeCompleted(): bool
    {
        return $this === self::Ready;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->all();
    }
}