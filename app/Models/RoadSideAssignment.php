<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadSideAssignment extends Model
{
    protected $fillable = [
        'tenant_id',
        'roadside_request_id',
        'employee_id',
        'assigned_at',
        'arrival_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'arrival_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(RoadSideRequest::class, 'roadside_request_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}