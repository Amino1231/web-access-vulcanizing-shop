<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'tenant_id',
        'product_id',
        'service_id',
        'name',
        'slug',
        'attachment',
        'image',
        'price',
        'description',
        'status',
        'archived_at',
    ];

    protected $casts = [
        'status'      => PostStatus::class,
        'archived_at' => 'datetime',
        'price'       => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Is this a product post or a service post? */
    public function isProduct(): bool
    {
        return $this->product_id !== null;
    }

    public function isService(): bool
    {
        return $this->service_id !== null;
    }

    public function scopePublished($query)
    {
        return $query->where('status', PostStatus::Published->value)
                     ->whereNull('archived_at');
    }
    
    /**
     * Fields that can be used as filters.
     */
    public function searchableOptions(): array
    {
        return [
            'filterableAttributes' => ['tenant_id', 'category_id', 'price'],
            'sortableAttributes'   => ['price', 'created_at'],
        ];
    }
}