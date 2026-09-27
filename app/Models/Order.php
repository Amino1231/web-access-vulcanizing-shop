<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'order_number', 'status',
        'subtotal', 'discount', 'tax', 'total',
        'payment_method', 'notes',
        'processed_by', 'processed_at', 'completed_at',
        'cancelled_at', 'cancellation_reason', 'sale_id',
    ];

    protected $casts = [
        'status'       => OrderStatus::class,
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'total'        => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function scopePending($q)    { return $q->where('status', OrderStatus::Pending->value); }
    public function scopeProcessing($q) { return $q->where('status', OrderStatus::Processing->value); }
    public function scopeReady($q)      { return $q->where('status', OrderStatus::Ready->value); }
    public function scopeCompleted($q)  { return $q->where('status', OrderStatus::Completed->value); }
    public function scopeCancelled($q)  { return $q->where('status', OrderStatus::Cancelled->value); }
    public function scopeActive($q)     { return $q->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value]); }
}