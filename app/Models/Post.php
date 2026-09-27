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
        'service_category_id',
        'product_category_id',
        'name',
        'slug',
        'type',
        'attachment',
        'image',
        'price',
        'description',
        'status',
        'archived_at',
    ];

    protected $casts = [
        'status'     => PostStatus::class,
        'archived_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', PostStatus::Published->value)
                     ->whereNull('archived_at');
    }
}