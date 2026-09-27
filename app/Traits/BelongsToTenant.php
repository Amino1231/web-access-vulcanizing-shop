<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
   protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (request()->routeIs('customer.*')) {
                return;
            }

            $user = auth()->user();

            if (! $user) {
                return;
            }

            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return;
            }

            $tenantId = $user->tenant?->id ?? $user->tenant_id ?? null;

            if ($tenantId) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    $tenantId
                );
            }
        });

        static::creating(function ($model) {
            if (! $model->tenant_id) {
                $user = auth()->user();
                $tenantId = $user?->tenant?->id ?? $user?->tenant_id ?? null;

                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}