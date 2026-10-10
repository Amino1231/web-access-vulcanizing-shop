<?php

namespace App\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'position',
        'commission_rate',
        'phone',
        'hired_at',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'hired_at'        => 'date',
        'is_active'       => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(EmployeeLocation::class);
    }

    public function roadSideAssignments(): HasMany
    {
        return $this->hasMany(RoadSideAssignment::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'served_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}