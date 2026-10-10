<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'appointment_id',
        'roadside_request_id',
        'served_by',
        'invoice_number',
        'subtotal',
        'discount',
        'tax',
        'final_amount',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function roadSideRequest(): BelongsTo
    {
        return $this->belongsTo(RoadSideRequest::class, 'roadside_request_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'served_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}