<?php

namespace App\Enums;

enum ItemTypeStatus: string
{
    case Product = 'product';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Product',
            self::Service => 'Service',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Product => 'orange',
            self::Service => 'blue',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}