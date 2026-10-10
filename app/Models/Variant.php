<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    protected $fillable = [
        'tenant_id',
        'product_id',
        'sku',
        'price',
        'stock_quantity',
        'image',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'attribute_value_variants',
            'variant_id',
            'attribute_value_id'
        );
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Variant $variant) {
            $variant->product?->syncStockFromVariants();
        });

        static::deleted(function (Variant $variant) {
            $variant->product?->syncStockFromVariants();
        });
    }
}