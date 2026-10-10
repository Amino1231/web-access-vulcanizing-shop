<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Archived  = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Published => 'Published',
            self::Archived  => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'slate',
            self::Published => 'emerald',
            self::Archived  => 'rose',
        };
    }

    public function isVisible(): bool
    {
        return $this === self::Published;
    }

    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}