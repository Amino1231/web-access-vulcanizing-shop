<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventory extends Model
{
    protected $fillable = [
        'tenant_id',
        'product_id',
        'type',
        'quantity',
        'before_stock',
        'after_stock',
        'reference_type',
        'reference_id',
        'remarks',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'before_stock' => 'integer',
        'after_stock'  => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Whatever caused this movement: Sale, Order, Appointment, etc. */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}