<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'product_id',
        'service_id',
        'employee_id',
        'sale_id',
        'rating',
        'review',
        'is_hidden',
        'flagged_at',
        'hidden_at',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'is_hidden'  => 'boolean',
        'flagged_at' => 'datetime',
        'hidden_at'  => 'datetime',
    ];

    // ─── Relations ─────────────────────────────

    public function tenant(): BelongsTo     { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo   { return $this->belongsTo(User::class, 'customer_id'); }
    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
    public function service(): BelongsTo    { return $this->belongsTo(Service::class); }
    public function employee(): BelongsTo   { return $this->belongsTo(Employee::class); }
    public function sale(): BelongsTo       { return $this->belongsTo(Sale::class); }

    // ─── Scopes ────────────────────────────────

    /**
     * Only reviews that should be visible to customers.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Reviews that have been flagged for admin follow-up.
     */
    public function scopeFlagged(Builder $query): Builder
    {
        return $query->whereNotNull('flagged_at');
    }

    /**
     * Reviews that are currently hidden from the public.
     */
    public function scopeHidden(Builder $query): Builder
    {
        return $query->where('is_hidden', true);
    }

    // ─── Helpers ───────────────────────────────

    public function kind(): string
    {
        return match (true) {
            $this->product_id !== null  => 'Product',
            $this->service_id !== null  => 'Service',
            $this->sale_id !== null     => 'Sale',
            $this->employee_id !== null => 'Staff',
            default                     => 'General',
        };
    }

    public function subjectLabel(): string
    {
        if ($this->product_id && $this->product) {
            return 'Product · ' . $this->product->name;
        }

        if ($this->service_id && $this->service) {
            return 'Service · ' . $this->service->name;
        }

        if ($this->sale_id && $this->sale) {
            return 'Sale · ' . $this->sale->invoice_number;
        }

        if ($this->employee_id && $this->employee) {
            return 'Staff · ' . $this->employee->name;
        }

        return 'General feedback';
    }
}