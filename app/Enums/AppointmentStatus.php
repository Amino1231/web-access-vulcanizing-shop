<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled  = 'scheduled';
    case Confirmed  = 'confirmed';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case NoShow     = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled  => 'Scheduled',
            self::Confirmed  => 'Confirmed',
            self::InProgress => 'In progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
            self::NoShow     => 'No-show',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled  => 'slate',
            self::Confirmed  => 'blue',
            self::InProgress => 'amber',
            self::Completed  => 'emerald',
            self::Cancelled  => 'red',
            self::NoShow     => 'zinc',
        };
    }

    public function badge(): array
    {
        return match ($this) {
            self::Scheduled => [
                'bg'   => 'bg-slate-100 dark:bg-gray-800',
                'text' => 'text-slate-700 dark:text-gray-300',
                'dot'  => 'bg-slate-500',
            ],
            self::Confirmed => [
                'bg'   => 'bg-blue-50 dark:bg-blue-500/10',
                'text' => 'text-blue-700 dark:text-blue-300',
                'dot'  => 'bg-blue-500',
            ],
            self::InProgress => [
                'bg'   => 'bg-amber-50 dark:bg-amber-500/10',
                'text' => 'text-amber-700 dark:text-amber-300',
                'dot'  => 'bg-amber-500',
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
            self::NoShow => [
                'bg'   => 'bg-slate-100 dark:bg-gray-800',
                'text' => 'text-slate-600 dark:text-gray-400',
                'dot'  => 'bg-slate-400',
            ],
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Scheduled, self::Confirmed, self::InProgress], true);
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::NoShow], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Scheduled, self::Confirmed], true);
    }

    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}