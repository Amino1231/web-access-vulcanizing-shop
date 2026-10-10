<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadSideService extends Model
{
    protected $fillable = [
        'tenant_id',
        'roadside_request_id',
        'service_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(RoadSideRequest::class, 'roadside_request_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}