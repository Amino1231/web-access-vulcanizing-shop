<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'gender',
        'birth_date',
        'email',
        'phone',
        'address',
        'avatar',
        'cover_photo',
        'is_active',
        'last_login_at',
        'last_logout_at',
        'email_verified_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_logout_at' => 'datetime',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Shortcut to the current tenant for layout/sidebar purposes.
     */
    public function currentTenant(): ?Tenant
    {
        $activeId = session('active_tenant_id');

        if ($activeId) {
            $active = $this->ownedTenants()->whereKey($activeId)->first()
                ?? $this->tenants()->whereKey($activeId)->first();

            if ($active) {
                return $active;
            }
        }

        // Prefer an approved shop — never dump the user into a pending shop
        // just because it's newer.
        $fallback = $this->ownedTenants()
            ->where('verification_status', VerificationStatus::Approved->value)
            ->latest()
            ->first()
            ?? $this->tenants()
                ->where('verification_status', VerificationStatus::Approved->value)
                ->latest()
                ->first()
            ?? $this->ownedTenants()->latest()->first()
            ?? $this->tenants()->latest()->first();

        if ($fallback) {
            session(['active_tenant_id' => $fallback->id]);
        }

        return $fallback;
    }

    /** Every shop this user can access (owned + shared). */
    public function allTenants(): Collection
    {
        return $this->ownedTenants()
            ->get()
            ->merge($this->tenants()->get())
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    // ─── Tenants ────────────────────────────────

    /** Shops this user owns (via tenants.user_id). */
    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'user_id');
    }

    /** All shops this user has access to (via tenant_users pivot). */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users');
    }

    // ─── Customer-side relations ────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'customer_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function roadSideRequests(): HasMany
    {
        return $this->hasMany(RoadSideRequest::class, 'customer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    /** Employee profile linked to this user, if any. */
    public function employee(): HasMany
    {
        return $this->hasMany(Employee::class, 'user_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
