<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'phone',
        'email',
        'address',
        'logo',
        'is_active',
        'business_setup_completed',
        'business_hours',
        'attachment',
        'latitude',
        'longitude',
        'verification_status',
        'rejection_reason',
        'missing_requirements',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'business_setup_completed' => 'boolean',
        'business_hours' => 'array',
        'attachment' => 'array',
        'missing_requirements' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // ─── Owner & members ────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_users');
    }

    // ─── Catalog ────────────────────────────────

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    // ─── Operations ─────────────────────────────

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function employeeLocations(): HasMany
    {
        return $this->hasMany(EmployeeLocation::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function roadSideRequests(): HasMany
    {
        return $this->hasMany(RoadSideRequest::class);
    }

    public function roadSideAssignments(): HasMany
    {
        return $this->hasMany(RoadSideAssignment::class);
    }

    public function roadSideServices(): HasMany
    {
        return $this->hasMany(RoadSideService::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─── Availability (real-time open/closed) ───

    public function todayHours(): ?array
    {
        if (! is_array($this->business_hours)) {
            return null;
        }

        $day = now()->format('l');
        $entry = $this->business_hours[$day] ?? null;

        if (! $entry || empty($entry['open']) || empty($entry['close'])) {
            return null;
        }

        return [
            'open' => $entry['open'],
            'close' => $entry['close'],
        ];
    }

    public function isOpenNow(): bool
    {
        if (! $this->is_active || ! $this->business_setup_completed) {
            return false;
        }

        $hours = $this->todayHours();
        if (! $hours) {
            return false;
        }

        $now = now();
        $openAt = Carbon::parse($now->toDateString().' '.$hours['open']);
        $closeAt = Carbon::parse($now->toDateString().' '.$hours['close']);

        return $now->between($openAt, $closeAt);
    }

    public function availabilityStatus(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        $hours = $this->todayHours();
        if (! $hours) {
            return 'closed_today';
        }

        if (! $this->isOpenNow()) {
            return 'closed';
        }

        $closeAt = Carbon::parse(now()->toDateString().' '.$hours['close']);
        if ($closeAt->diffInMinutes(now()) <= 60) {
            return 'closing_soon';
        }

        return 'open';
    }

    public function availabilityLabel(): string
    {
        return match ($this->availabilityStatus()) {
            'open' => 'Open now',
            'closing_soon' => 'Closing soon',
            'closed' => 'Closed',
            'closed_today' => 'Closed today',
            'inactive' => 'Offline',
            default => 'Unknown',
        };
    }
}
