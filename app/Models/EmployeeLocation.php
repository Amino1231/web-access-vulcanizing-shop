<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLocation extends Model
{
    protected $fillable = [
        'tenant_id',
        'employee_id',
        'latitude',
        'longitude',
        'recorded_at',
    ];

    protected $casts = [
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
        'recorded_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}