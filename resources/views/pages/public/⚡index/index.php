<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedTenantId = null;

    public float $centerLat = 14.5995;
    public float $centerLng = 120.9842;
    public int $zoom = 6;

    public ?float $userLat = null;
    public ?float $userLng = null;

    public bool $showShops = true;

    // ─────────────────────────────────────────
    // Shops
    // ─────────────────────────────────────────

    #[Computed]
    public function shops()
    {
        $isAuthed = Auth::check();

        $rows = Tenant::query()
            ->where('is_active', true)
            ->where('verification_status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get([
                'id', 'name', 'slug', 'address', 'phone', 'logo',
                'latitude', 'longitude', 'business_hours',
                'is_active', 'business_setup_completed',
            ])
            ->map(function (Tenant $t) use ($isAuthed) {
                $lat = (float) $t->latitude;
                $lng = (float) $t->longitude;

                $distance = ($this->userLat !== null && $this->userLng !== null)
                    ? $this->haversine($this->userLat, $this->userLng, $lat, $lng)
                    : null;

                $servicesUrl = route('customer.services.index', ['tenant' => $t->id]);

                return [
                    'id'             => $t->id,
                    'name'           => $t->name,
                    'address'        => $t->address,
                    'phone'          => $t->phone,
                    'logo'           => $t->logo ? asset('storage/' . $t->logo) : null,
                    'lat'            => $lat,
                    'lng'            => $lng,
                    'is_open'        => $t->isOpenNow(),
                    'status'         => $t->availabilityLabel(),
                    'status_key'     => $t->availabilityStatus(),
                    'distance'       => $distance,
                    'distance_label' => $distance !== null ? $this->formatDistance($distance) : null,

                    // Guest-safe URLs
                    'url'            => $isAuthed ? $servicesUrl : route('login', ['redirect' => $servicesUrl]),
                    'is_authed'      => $isAuthed,
                ];
            })
            ->values();

        if ($this->userLat !== null && $this->userLng !== null) {
            $rows = $rows->sortBy('distance')->values();
        }

        return $rows->all();
    }

    #[Computed]
    public function nearestShop(): ?array
    {
        $shops = $this->shops;

        if (empty($shops) || $this->userLat === null) {
            return null;
        }

        return $shops[0];
    }

    #[Computed]
    public function shopCount(): int
    {
        return count($this->shops);
    }

    #[Computed]
    public function openNowCount(): int
    {
        return collect($this->shops)->where('status_key', 'open')->count();
    }

    #[Computed]
    public function selectedShop(): ?array
    {
        if (! $this->selectedTenantId) {
            return null;
        }

        return collect($this->shops)->firstWhere('id', $this->selectedTenantId);
    }

    // ─────────────────────────────────────────
    // Location + map controls
    // ─────────────────────────────────────────

    public function setUserLocation(float $lat, float $lng): void
    {
        $this->userLat   = $lat;
        $this->userLng   = $lng;
        $this->centerLat = $lat;
        $this->centerLng = $lng;
        $this->zoom      = 12;

        unset($this->shops, $this->nearestShop, $this->openNowCount, $this->shopCount, $this->selectedShop);
        $this->dispatchMapUpdate();
    }

    public function selectTenant(int $id): void
    {
        $this->selectedTenantId = $id;
    }

    public function clearSelection(): void
    {
        $this->selectedTenantId = null;
    }

    public function centerOn(float $lat, float $lng, int $zoom = 15): void
    {
        $this->centerLat = $lat;
        $this->centerLng = $lng;
        $this->zoom = $zoom;

        $this->dispatch('map:center', lat: $lat, lng: $lng, zoom: $zoom);
    }

    public function toggleShops(): void
    {
        $this->showShops = ! $this->showShops;
        $this->dispatchMapUpdate();
    }

    protected function dispatchMapUpdate(): void
    {
        $this->dispatch('map:update',
            shops: $this->showShops ? $this->shops : [],
        );
    }

    public function mount(): void
    {
        $this->dispatchMapUpdate();
    }

    public function pollRefresh(): void
    {
        unset($this->shops, $this->nearestShop, $this->openNowCount, $this->shopCount, $this->selectedShop);
        $this->dispatchMapUpdate();
    }

    // ─────────────────────────────────────────
    // Distance helpers
    // ─────────────────────────────────────────

    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * atan2(sqrt($a), sqrt(1 - $a));
    }

    protected function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return round($meters) . ' m';
        }

        return number_format($meters / 1000, 1) . ' km';
    }
};