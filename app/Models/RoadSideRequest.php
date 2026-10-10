<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadSideRequest extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'vehicle_id',
        'request_number',
        'latitude',
        'longitude',
        'address',
        'problem_type',
        'description',
        'status',
        'requested_at',
        'accepted_at',
        'completed_at',
    ];

    protected $casts = [
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'requested_at' => 'datetime',
        'accepted_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RoadSideAssignment::class, 'roadside_request_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(RoadSideService::class, 'roadside_request_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}