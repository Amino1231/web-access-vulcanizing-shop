<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'plate_number',
        'vehicle_type',
        'brand',
        'model',
        'year_model',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function roadSideRequests(): HasMany
    {
        return $this->hasMany(RoadSideRequest::class);
    }
}