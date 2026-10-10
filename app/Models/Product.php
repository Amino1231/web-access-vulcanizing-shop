<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'tenant_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'cost_price',
        'selling_price',
        'stock',
        'low_stock_alert',
        'description',
        'ft_img',
        'attachments',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_alert' => 'integer',
        'attachments' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Stock denormalization ───────────────

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    public function isStockEditable(): bool
    {
        return ! $this->hasVariants();
    }

    public function syncStockFromVariants(): void
    {
        if (! $this->hasVariants()) {
            return;
        }

        $sum = (int) $this->variants()->sum('stock_quantity');

        if ((int) $this->stock !== $sum) {
            $this->stock = $sum;
            $this->saveQuietly();
        }
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Average rating across all reviews for this product.
     */
    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    /**
     * Count of reviews for this product.
     */
    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }
}
