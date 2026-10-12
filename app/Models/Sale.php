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
        'platform_commission',
        'shop_net_revenue',
        'commission_rate_applied',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'final_amount' => 'decimal:2',
        'platform_commission'     => 'decimal:2',
        'shop_net_revenue'        => 'decimal:2',
        'commission_rate_applied' => 'decimal:2',
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

    // ─────────────────────────────────────────
    // Commission helpers
    // ─────────────────────────────────────────

    /**
     * Calculate and apply platform commission on this sale.
     * Call BEFORE save() on a new Sale instance.
     *
     * @param  float  $rate  Commission % (0-100). e.g. 10.00 for 10%.
     */
    public function applyCommission(float $rate): self
    {
        // Clamp rate to a valid 0–100 range
        $rate = max(0.0, min(100.0, $rate));

        $finalAmount = (float) $this->final_amount;

        $this->commission_rate_applied = $rate;
        $this->platform_commission     = round($finalAmount * ($rate / 100), 2);
        $this->shop_net_revenue        = round($finalAmount - $this->platform_commission, 2);

        return $this;
    }

    /**
     * Convenience: does this sale have a commission applied?
     */
    public function hasCommission(): bool
    {
        return (float) $this->platform_commission > 0;
    }
}