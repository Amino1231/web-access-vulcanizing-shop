<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Pending        = 'pending';
    case UnderReview    = 'under_review';
    case Approved       = 'approved';
    case Rejected       = 'rejected';
    case NeedsMoreInfo  = 'needs_more_info';

    public function label(): string
    {
        return match ($this) {
            self::Pending       => 'Pending review',
            self::UnderReview   => 'Under review',
            self::Approved      => 'Approved',
            self::Rejected      => 'Rejected',
            self::NeedsMoreInfo => 'Needs more info',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending       => 'amber',
            self::UnderReview   => 'blue',
            self::Approved      => 'emerald',
            self::Rejected      => 'rose',
            self::NeedsMoreInfo => 'orange',
        };
    }

    public function isApproved(): bool
    {
        return $this === self::Approved;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected], true);
    }

    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}