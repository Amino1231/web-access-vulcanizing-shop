<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'tenant_id',
        'service_category_id',
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_vehicle_service',
        'is_active',
    ];

    protected $casts = [
        'duration_minutes'   => 'integer',
        'price'              => 'decimal:2',
        'is_vehicle_service' => 'boolean',
        'is_active'          => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function roadSideServices(): HasMany
    {
        return $this->hasMany(RoadSideService::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }
}