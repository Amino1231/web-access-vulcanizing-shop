<?php

use App\Models\RoadSideRequest;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Notifications\RoadsideRequested;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

new #[Layout('layouts.customer')] class extends Component
{
    public bool $showShops = true;
    public bool $showRequests = true;

    public ?int $selectedTenantId = null;
    public ?int $selectedRequestId = null;

    public float $centerLat = 14.5995;
    public float $centerLng = 120.9842;
    public int $zoom = 11;

    /** User's GPS coords (set when they click locate) */
    public ?float $userLat = null;
    public ?float $userLng = null;

    // ─────────────────────────────────────────
    // Roadside request modal state
    // ─────────────────────────────────────────

    public bool $showRoadsideModal = false;
    public string $roadsideProblemType = 'flat_tire';
    public ?int $roadsideVehicleId = null;
    public string $roadsideDescription = '';
    public string $roadsideAddress = '';

    // ─────────────────────────────────────────
    // Shops — with distance when user location is known
    // ─────────────────────────────────────────

    #[Computed]
    public function shops()
    {
        $rows = Tenant::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'slug', 'address', 'phone', 'logo', 'latitude', 'longitude', 'business_hours', 'is_active', 'business_setup_completed'])
            ->map(function (Tenant $t) {
                $lat = (float) $t->latitude;
                $lng = (float) $t->longitude;

                $distance = ($this->userLat !== null && $this->userLng !== null)
                    ? $this->haversine($this->userLat, $this->userLng, $lat, $lng)
                    : null;

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
                    'url'            => route('customer.services.index', ['tenant' => $t->id]),
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
    public function nearestOpenShopForRoadside(): ?array
    {
        $shops = $this->shops;

        if (empty($shops) || $this->userLat === null) {
            return null;
        }

        return collect($shops)
            ->filter(fn ($s) => in_array($s['status_key'], ['open', 'closing_soon'], true))
            ->sortBy('distance')
            ->first();
    }

    #[Computed]
    public function vehicles()
    {
        if (! Auth::check()) {
            return collect();
        }

        return Vehicle::query()
            ->where('customer_id', Auth::id())
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'brand', 'model']);
    }

    // ─────────────────────────────────────────
    // Roadside requests
    // ─────────────────────────────────────────

    #[Computed]
    public function roadsideRequests()
    {
        return RoadSideRequest::query()
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['customer:id,name', 'vehicle:id,plate_number,brand,model'])
            ->latest()
            ->get()
            ->map(function (RoadSideRequest $r) {
                $lat = (float) $r->latitude;
                $lng = (float) $r->longitude;

                $distance = ($this->userLat !== null && $this->userLng !== null)
                    ? $this->haversine($this->userLat, $this->userLng, $lat, $lng)
                    : null;

                return [
                    'id'             => $r->id,
                    'request_number' => $r->request_number,
                    'problem_type'   => $r->problem_type,
                    'status'         => $r->status,
                    'address'        => $r->address,
                    'lat'            => $lat,
                    'lng'            => $lng,
                    'customer'       => $r->customer?->name ?? 'Anonymous',
                    'vehicle'        => $r->vehicle?->plate_number,
                    'distance'       => $distance,
                    'distance_label' => $distance !== null ? $this->formatDistance($distance) : null,
                    'created_at'     => $r->created_at?->diffForHumans(),
                    'requested_at'   => $r->requested_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function shopTimeContext(): array
    {
        $now = now()->timezone('Asia/Manila');
        $openCount = 0;
        $closingSoonCount = 0;

        foreach ($this->shops as $shop) {
            if ($shop['status_key'] === 'open')         $openCount++;
            if ($shop['status_key'] === 'closing_soon') $closingSoonCount++;
        }

        return [
            'time'         => $now->format('g:i A'),
            'day'          => $now->format('l'),
            'open_count'   => $openCount,
            'closing_soon' => $closingSoonCount,
            'total'        => count($this->shops),
        ];
    }

    // ─────────────────────────────────────────
    // Roadside submission
    // ─────────────────────────────────────────

    public function roadsideProblemTypes(): array
    {
        return [
            'flat_tire'      => 'Flat tire',
            'dead_battery'   => 'Dead battery',
            'out_of_fuel'    => 'Out of fuel',
            'overheating'    => 'Engine overheating',
            'lockout'        => 'Keys locked inside',
            'engine_trouble' => 'Engine trouble / won\'t start',
            'tow_needed'     => 'Tow needed',
            'other'          => 'Something else',
        ];
    }

    public function openRoadsideModal(): void
    {
        if (! Auth::check()) {
            $this->addError('roadside', 'Please sign in to request roadside help.');
            return;
        }

        if ($this->userLat === null || $this->userLng === null) {
            $this->addError('roadside', 'Location required. Enable location first.');
            return;
        }

        if (! $this->nearestOpenShopForRoadside) {
            $this->addError('roadside', 'No shops are currently open nearby. Try again later.');
            return;
        }

        $this->resetValidation();
        $this->roadsideProblemType = 'flat_tire';
        $this->roadsideVehicleId   = null;
        $this->roadsideDescription = '';
        $this->roadsideAddress     = '';
        $this->showRoadsideModal   = true;
    }

    public function closeRoadsideModal(): void
    {
        $this->showRoadsideModal = false;
        $this->resetValidation();
    }

    public function submitRoadsideRequest(): void
    {
        if (! Auth::check()) {
            $this->addError('roadside', 'Please sign in to request roadside help.');
            return;
        }

        $this->validate([
            'roadsideProblemType' => ['required', 'string', 'in:' . implode(',', array_keys($this->roadsideProblemTypes()))],
            'roadsideVehicleId'   => ['nullable', 'integer', 'exists:vehicles,id'],
            'roadsideDescription' => ['nullable', 'string', 'max:500'],
            'roadsideAddress'     => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->userLat === null || $this->userLng === null) {
            $this->addError('roadside', 'Location missing.');
            return;
        }

        $nearest = $this->nearestOpenShopForRoadside;

        if (! $nearest) {
            $this->addError('roadside', 'No shops are currently open. Please try again later.');
            return;
        }

        $vehicleId = null;
        if ($this->roadsideVehicleId) {
            $vehicleId = Vehicle::where('customer_id', Auth::id())
                ->whereKey($this->roadsideVehicleId)
                ->value('id');
        }

        $request = DB::transaction(function () use ($nearest, $vehicleId) {
            return RoadSideRequest::create([
                'tenant_id'      => $nearest['id'],
                'customer_id'    => Auth::id(),
                'vehicle_id'     => $vehicleId,
                'request_number' => 'RSR-' . strtoupper(Str::random(8)),
                'latitude'       => $this->userLat,
                'longitude'      => $this->userLng,
                'address'        => trim($this->roadsideAddress) ?: null,
                'problem_type'   => $this->roadsideProblemType,
                'description'    => trim($this->roadsideDescription) ?: null,
                'status'         => 'pending',
                'requested_at'   => now(),
            ]);
        });

        $request->tenant?->user?->notify(new RoadsideRequested($request));

        $this->showRoadsideModal   = false;
        $this->roadsideProblemType = 'flat_tire';
        $this->roadsideVehicleId   = null;
        $this->roadsideDescription = '';
        $this->roadsideAddress     = '';

        unset($this->roadsideRequests, $this->shopTimeContext);

        session()->flash('roadside_success', "Request {$request->request_number} sent to {$nearest['name']}.");
        $this->dispatchMapUpdate();
    }

    // ─────────────────────────────────────────
    // Location handling
    // ─────────────────────────────────────────

    public function setUserLocation(float $lat, float $lng): void
    {
        $this->userLat   = $lat;
        $this->userLng   = $lng;
        $this->centerLat = $lat;
        $this->centerLng = $lng;
        $this->zoom      = 15;

        unset($this->shops, $this->nearestShop, $this->nearestOpenShopForRoadside, $this->roadsideRequests, $this->shopTimeContext);
        $this->dispatchMapUpdate();
    }

    // ─────────────────────────────────────────
    // Layers / selection
    // ─────────────────────────────────────────

    public function toggleShops(): void
    {
        $this->showShops = ! $this->showShops;
        $this->dispatchMapUpdate();
    }

    public function toggleRequests(): void
    {
        $this->showRequests = ! $this->showRequests;
        $this->dispatchMapUpdate();
    }

    public function selectTenant(int $id): void
    {
        $this->selectedTenantId = $id;
        $this->selectedRequestId = null;
    }

    public function selectRequest(int $id): void
    {
        $this->selectedRequestId = $id;
        $this->selectedTenantId = null;
    }

    public function clearSelection(): void
    {
        $this->selectedTenantId = null;
        $this->selectedRequestId = null;
    }

    public function centerOn(float $lat, float $lng, int $zoom = 15): void
    {
        $this->centerLat = $lat;
        $this->centerLng = $lng;
        $this->zoom = $zoom;

        $this->dispatch('map:center', lat: $lat, lng: $lng, zoom: $zoom);
    }

    protected function dispatchMapUpdate(): void
    {
        $this->dispatch('map:update',
            shops:    $this->showShops ? $this->shops : [],
            requests: $this->showRequests ? $this->roadsideRequests : [],
        );
    }

    public function mount(): void
    {
        $this->dispatchMapUpdate();
    }

    #[On('echo:roadside,.roadside.request.created')]
    public function onRoadsideRequestCreated(): void
    {
        unset($this->roadsideRequests, $this->shopTimeContext);
        $this->dispatchMapUpdate();
    }

    #[On('echo:roadside,.roadside.request.updated')]
    public function onRoadsideRequestUpdated(): void
    {
        unset($this->roadsideRequests, $this->shopTimeContext);
        $this->dispatchMapUpdate();
    }

    public function pollRefresh(): void
    {
        unset($this->shops, $this->nearestShop, $this->nearestOpenShopForRoadside, $this->roadsideRequests, $this->shopTimeContext);
        $this->dispatchMapUpdate();
    }

    // ─────────────────────────────────────────
    // Distance helpers
    // ─────────────────────────────────────────

    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

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

?>

<div class="lm-root bg-gray-50 dark:bg-gray-950 min-h-screen">

    <style>
        header.sticky { z-index: 1000 !important; }

        /* ───────── Leaflet stacking ───────── */
        #live-map.leaflet-container {
            z-index: 0 !important;
            isolation: isolate;
        }
        #live-map .leaflet-pane         { z-index: 1 !important; }
        #live-map .leaflet-tile-pane    { z-index: 100 !important; }
        #live-map .leaflet-overlay-pane { z-index: 200 !important; }
        #live-map .leaflet-marker-pane  { z-index: 300 !important; }
        #live-map .leaflet-popup-pane   { z-index: 400 !important; }
        #live-map .leaflet-top,
        #live-map .leaflet-bottom       { z-index: 500 !important; }
        #live-map .leaflet-control      { z-index: 500 !important; }

        /* ───────── Responsive layout (plain CSS: no Tailwind rebuild needed) ───────── */
        .lm-root { overflow-x: clip; }

        .lm-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }
        .lm-grid > * { min-width: 0; }

        .lm-map-wrap { position: relative; min-width: 0; }

        #live-map {
            width: 100%;
            height: 60vh;              /* fallback */
            height: 60dvh;
            min-height: 320px;
            max-height: 560px;
        }

        /* Floating buttons sit above Leaflet's attribution strip */
        .lm-fab,
        .lm-locate {
            position: absolute;
            bottom: 2rem;
            z-index: 600;
        }
        .lm-fab    { left: .75rem; }
        .lm-locate { right: .75rem; }

        @media (min-width: 640px) {
            .lm-grid   { gap: 1.5rem; }
            #live-map  { height: 460px; max-height: none; }
            .lm-fab    { left: 1rem; }
            .lm-locate { right: 1rem; }
        }
        @media (min-width: 768px)  { #live-map { height: 540px; } }
        @media (min-width: 1024px) {
            .lm-grid  { grid-template-columns: minmax(0, 1fr) 360px; }
            #live-map { height: 600px; }
        }
        @media (min-width: 1280px) { #live-map { height: 640px; } }

        /* Popups never wider than the screen */
        #live-map .leaflet-popup-content-wrapper { max-width: min(260px, 72vw); }
        #live-map .leaflet-popup-content { margin: 12px 14px; overflow-wrap: anywhere; }

        /* ───────── Roadside modal (teleported to <body>) ───────── */
        .rs-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: flex-end;          /* bottom sheet on phones */
            justify-content: center;
            overflow: hidden;
            padding: 0;
            background: rgba(15, 23, 42, .7);
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
        }
        .rs-panel {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 100%;
            max-height: 92vh;               /* fallback */
            max-height: 92dvh;
            overflow: hidden;
            border-radius: 1rem 1rem 0 0;
        }
        .rs-head { flex: 0 0 auto; }
        .rs-body {
            flex: 1 1 auto;
            min-height: 0;                  /* lets only the form scroll */
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        .rs-foot {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column-reverse;
            gap: .5rem;
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
        }
        .rs-foot button { width: 100%; }

        /* 16px inputs on phones so iOS doesn't zoom the page on focus */
        .rs-panel select,
        .rs-panel textarea,
        .rs-panel input[type="text"] { font-size: 16px; }
        .rs-panel textarea { resize: none; }

        @media (min-width: 640px) {
            .rs-overlay { align-items: center; padding: 1.5rem; }
            .rs-panel   { max-width: 32rem; max-height: 90vh; max-height: 90dvh; border-radius: 1rem; }
            .rs-foot    { flex-direction: row; justify-content: flex-end; align-items: center; gap: .75rem; }
            .rs-foot button { width: auto; }
            .rs-panel select,
            .rs-panel textarea,
            .rs-panel input[type="text"] { font-size: .875rem; }
        }

        /* ───────── Animations ───────── */
        @keyframes user-pulse {
            0%   { transform: scale(1);   opacity: 0.6; }
            70%  { transform: scale(2.5); opacity: 0;   }
            100% { transform: scale(2.5); opacity: 0;   }
        }
        .user-pulse-ring { animation: user-pulse 2s ease-out infinite; }

        @keyframes marker-pulse {
            0%   { transform: scale(1);   box-shadow: 0 0 0 0 rgba(239,68,68,0.6); }
            70%  { transform: scale(1.1); box-shadow: 0 0 0 12px rgba(239,68,68,0); }
            100% { transform: scale(1);   box-shadow: 0 0 0 0 rgba(239,68,68,0); }
        }
        .marker-pulse { animation: marker-pulse 1.6s ease-out infinite; }
    </style>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                    Live view
                </p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Shop &amp; roadside map
                </h1>

                @if ($this->nearestShop)
                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                        <span class="font-semibold text-slate-700 dark:text-gray-200">{{ count($this->shops) }}</span>
                        {{ \Illuminate\Support\Str::plural('shop', count($this->shops)) }}
                        · Nearest is
                        <span class="font-semibold text-[#FF5E14]">{{ $this->nearestShop['name'] }}</span>
                        — <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $this->nearestShop['distance_label'] }}</span> away
                    </p>
                @else
                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                        {{ count($this->shops) }} active {{ \Illuminate\Support\Str::plural('shop', count($this->shops)) }}
                        · Enable location to see distances
                    </p>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-end gap-2 sm:gap-3">

                {{-- Live PH clock --}}
                <div x-data="{
                        time: '',
                        date: '',
                        tick() {
                            const now = new Date();
                            const ph = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Manila' }));
                            this.time = ph.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true });
                            this.date = ph.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                        }
                     }"
                     x-init="tick(); setInterval(() => tick(), 1000)"
                     class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-3.5 sm:px-4 py-2.5 shadow-sm">
                    <div class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#FF5E14] opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#FF5E14]"></span>
                    </div>
                    <div class="text-left sm:text-right flex-1">
                        <p class="font-mono text-base sm:text-lg font-bold leading-tight tracking-tight text-slate-900 dark:text-white" x-text="time"></p>
                        <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-500 dark:text-gray-400" x-text="date + ' · PHT'"></p>
                    </div>
                    <svg class="h-5 w-5 text-[#FF5E14] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                    </svg>
                </div>

                {{-- Use my location --}}
                @if ($userLat === null)
                    <button type="button" @click="document.getElementById('locate-trigger')?.click()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3" />
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
                        </svg>
                        Use my location
                    </button>
                @endif
            </div>
        </div>

        {{-- Success / error banners --}}
        @if (session('roadside_success'))
        <div class="mb-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
            <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                {{ session('roadside_success') }}
            </p>
        </div>
        @endif

        @error('roadside')
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            {{ $message }}
        </div>
        @enderror

        {{-- Layer toggles --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 sm:gap-3">
            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-slate-700 dark:text-gray-200 cursor-pointer">
                <input type="checkbox" wire:click="toggleShops" @checked($showShops)
                       class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" />
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                Shops
            </label>

            <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 sm:px-3.5 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-slate-700 dark:text-gray-200 cursor-pointer">
                <input type="checkbox" wire:click="toggleRequests" @checked($showRequests)
                       class="h-4 w-4 rounded border-slate-300 text-red-500 focus:ring-red-500" />
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500 animate-pulse"></span>
                Roadside requests
            </label>
        </div>

        <div class="lm-grid">

            {{-- MAP --}}
            <div
                wire:ignore
                x-data="window.createLiveMap({
                    shops: @js($showShops ? $this->shops : []),
                    requests: @js($showRequests ? $this->roadsideRequests : []),
                    initialCenter: [{{ $centerLat }}, {{ $centerLng }}],
                    initialZoom: {{ $zoom }},
                })"
                @map:update.window="updateData($event.detail)"
                @map:center.window="centerOn($event.detail)"
                class="lm-map-wrap rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm"
            >
                <div id="live-map"></div>

                {{-- Live pill --}}
                <div class="absolute right-3 top-3 sm:right-4 sm:top-4 z-10 flex items-center gap-1.5 sm:gap-2 rounded-full bg-white/95 dark:bg-gray-900/95 backdrop-blur border border-slate-200 dark:border-gray-700 px-2.5 sm:px-3 py-1 sm:py-1.5 shadow-sm" style="z-index: 600;">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-gray-200">Live</span>
                </div>

                {{-- Request roadside help FAB --}}
                @auth
                <button type="button"
                        @click="requestRoadside()"
                        class="lm-fab inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-red-600 px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-white shadow-lg shadow-red-500/30 transition hover:bg-red-700"
                        :class="locating && 'opacity-60 cursor-not-allowed'">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span x-show="!locating">Roadside help</span>
                    <span x-show="locating" x-cloak>Getting location…</span>
                </button>
                @endauth

                {{-- Locate button --}}
                <button type="button" id="locate-trigger" @click="locateMe()"
                        class="lm-locate flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 shadow-lg text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-800"
                        :class="locating && 'animate-pulse'"
                        title="Center on my location">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3" />
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
                    </svg>
                </button>
            </div>

            {{-- SIDE PANEL --}}
            <div class="space-y-3" wire:poll.10s="pollRefresh">
                @if ($selectedTenantId)
                    @php $shop = collect($this->shops)->firstWhere('id', $selectedTenantId); @endphp
                    @if ($shop)
                        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Shop</p>
                                    <h2 class="mt-1 truncate font-bold text-slate-900 dark:text-white">{{ $shop['name'] }}</h2>
                                </div>
                                <button wire:click="clearSelection" type="button"
                                        class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            @if ($shop['distance_label'])
                                <div class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-orange-50 dark:bg-orange-500/10 px-2.5 py-1 text-xs font-bold text-[#FF5E14]">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $shop['distance_label'] }} away
                                </div>
                            @endif

                            <div class="space-y-2 text-sm">
                                @if ($shop['status'])
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block h-2 w-2 rounded-full bg-{{ $shop['status_key'] === 'open' ? 'emerald' : ($shop['status_key'] === 'closing_soon' ? 'amber' : 'slate') }}-500"></span>
                                        <span class="font-medium text-slate-700 dark:text-gray-200">{{ $shop['status'] }}</span>
                                    </div>
                                @endif
                                @if ($shop['address'])
                                    <p class="text-slate-600 dark:text-gray-400 break-words">{{ $shop['address'] }}</p>
                                @endif
                                @if ($shop['phone'])
                                    <p class="text-slate-600 dark:text-gray-400 break-words">{{ $shop['phone'] }}</p>
                                @endif
                            </div>

                            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                                <button type="button" wire:click="centerOn({{ $shop['lat'] }}, {{ $shop['lng'] }}, 16)"
                                        class="flex-1 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50">
                                    Zoom in
                                </button>
                                <a href="{{ $shop['url'] }}" wire:navigate
                                   class="flex-1 inline-flex items-center justify-center rounded-xl bg-[#FF5E14] px-3 py-2 text-xs font-semibold text-white hover:bg-[#D94E10]">
                                    View services
                                </a>
                            </div>
                        </div>
                    @endif
                @elseif ($selectedRequestId)
                    @php $req = collect($this->roadsideRequests)->firstWhere('id', $selectedRequestId); @endphp
                    @if ($req)
                        <div class="rounded-2xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-red-600 dark:text-red-400">Roadside request</p>
                                    <h2 class="mt-1 truncate font-bold text-slate-900 dark:text-white">{{ $req['request_number'] }}</h2>
                                </div>
                                <button wire:click="clearSelection" type="button"
                                        class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            @if ($req['distance_label'])
                                <div class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-red-100 dark:bg-red-500/20 px-2.5 py-1 text-xs font-bold text-red-700 dark:text-red-300">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $req['distance_label'] }} from you
                                </div>
                            @endif

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-block h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                                    <span class="font-medium capitalize text-red-700 dark:text-red-300">{{ str_replace('_', ' ', $req['status']) }}</span>
                                </div>
                                <p class="font-semibold capitalize text-slate-700 dark:text-gray-200">{{ str_replace('_', ' ', $req['problem_type']) }}</p>
                                @if ($req['vehicle'])
                                    <p class="text-slate-600 dark:text-gray-400">Vehicle: {{ $req['vehicle'] }}</p>
                                @endif
                                @if ($req['address'])
                                    <p class="text-slate-600 dark:text-gray-400 break-words">{{ $req['address'] }}</p>
                                @endif
                                <p class="text-xs text-slate-500 dark:text-gray-500">Requested {{ $req['created_at'] }}</p>
                            </div>

                            <button type="button" wire:click="centerOn({{ $req['lat'] }}, {{ $req['lng'] }}, 16)"
                                    class="mt-4 w-full rounded-xl border border-red-200 dark:border-red-500/30 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">
                                Zoom to location
                            </button>
                        </div>
                    @endif
                @else
                    {{-- List view --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-5">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                                Nearby shops
                            </h2>
                            @if ($userLat !== null)
                                <span class="rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                    Sorted by distance
                                </span>
                            @endif
                        </div>
                        <div class="space-y-2 max-h-[280px] sm:max-h-[360px] lg:max-h-[520px] overflow-y-auto -mx-1 px-1">
                            @forelse ($this->shops as $index => $shop)
                                <button type="button"
                                        wire:click="selectTenant({{ $shop['id'] }}); centerOn({{ $shop['lat'] }}, {{ $shop['lng'] }}, 15)"
                                        class="w-full text-left rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 px-3 py-2.5 transition hover:border-orange-300 hover:bg-orange-50 dark:hover:bg-orange-500/10">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ $shop['name'] }}
                                            </p>
                                            <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $shop['address'] }}</p>
                                            @php
                                                $listDot = match ($shop['status_key']) {
                                                    'open'         => 'emerald',
                                                    'closing_soon' => 'amber',
                                                    'closed'       => 'slate',
                                                    'closed_today' => 'slate',
                                                    default        => 'slate',
                                                };
                                            @endphp
                                            <div class="mt-1 flex items-center gap-1">
                                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-{{ $listDot }}-500"></span>
                                                <span class="text-[10px] font-medium text-slate-500 dark:text-gray-500">{{ $shop['status'] }}</span>
                                            </div>
                                        </div>
                                        @if ($shop['distance_label'])
                                            <div class="shrink-0 text-right">
                                                <p class="text-sm font-bold text-[#FF5E14]">{{ $shop['distance_label'] }}</p>
                                                @if ($index === 0 && $userLat !== null)
                                                    <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Nearest</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </button>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-gray-400">No active shops with location data.</p>
                            @endforelse
                        </div>
                    </div>

                    @if (count($this->roadsideRequests) > 0)
                        <div class="rounded-2xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 p-4 sm:p-5">
                            <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-red-600 dark:text-red-400 mb-3">
                                Active requests
                            </h2>
                            <div class="space-y-2 max-h-[220px] overflow-y-auto -mx-1 px-1">
                                @foreach ($this->roadsideRequests as $req)
                                    <button type="button"
                                            wire:click="selectRequest({{ $req['id'] }}); centerOn({{ $req['lat'] }}, {{ $req['lng'] }}, 16)"
                                            class="w-full text-left rounded-xl border border-red-200 dark:border-red-500/30 bg-white dark:bg-gray-900 px-3 py-2.5 transition hover:bg-red-100 dark:hover:bg-red-500/20">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-semibold text-red-700 dark:text-red-300">{{ $req['request_number'] }}</p>
                                                <p class="truncate text-xs text-slate-600 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $req['problem_type']) }}</p>
                                            </div>
                                            @if ($req['distance_label'])
                                                <p class="shrink-0 text-xs font-bold text-red-600 dark:text-red-400">{{ $req['distance_label'] }}</p>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Roadside request modal — teleported to <body> --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($showRoadsideModal)
        @teleport('body')
            @php $assignedShop = $this->nearestOpenShopForRoadside; @endphp
            <div x-data="{
                    init() { document.body.style.overflow = 'hidden' },
                    destroy() { document.body.style.overflow = '' }
                }"
                @keydown.escape.window="$wire.closeRoadsideModal()"
                class="rs-overlay">

                <div class="rs-panel border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

                    {{-- Header --}}
                    <div class="rs-head flex items-start gap-3 sm:gap-4 border-b border-slate-200 dark:border-gray-800 px-4 sm:px-6 py-4 sm:py-5">
                        <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Request roadside help</h3>
                            <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm text-slate-600 dark:text-gray-400">
                                We'll send this to the nearest open shop.
                            </p>
                        </div>
                        <button wire:click="closeRoadsideModal" type="button"
                                class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="rs-body px-4 sm:px-6 py-5 sm:py-6 space-y-4 sm:space-y-5">

                        {{-- Assigned shop --}}
                        @if ($assignedShop)
                        <div class="rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Sending to</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $assignedShop['name'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-600 dark:text-gray-400 break-words">
                                {{ $assignedShop['address'] ?? 'No address' }}
                                @if ($assignedShop['distance_label'])
                                    · <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ $assignedShop['distance_label'] }}</span> away
                                @endif
                            </p>
                        </div>
                        @endif

                        {{-- Problem type --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                What's the problem?
                            </label>
                            <select wire:model="roadsideProblemType"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-red-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-red-100 dark:focus:ring-red-500/20">
                                @foreach ($this->roadsideProblemTypes() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('roadsideProblemType') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Details <span class="text-slate-400">(optional)</span>
                            </label>
                            <textarea wire:model="roadsideDescription" rows="2"
                                      placeholder="e.g. Rear-left tire completely flat, no spare."
                                      class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-red-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-red-100 dark:focus:ring-red-500/20"></textarea>
                            @error('roadsideDescription') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Vehicle --}}
                        @if ($this->vehicles->isNotEmpty())
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Vehicle <span class="text-slate-400">(optional)</span>
                            </label>
                            <select wire:model="roadsideVehicleId"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-red-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-red-100 dark:focus:ring-red-500/20">
                                <option value="">Not specified</option>
                                @foreach ($this->vehicles as $v)
                                    <option value="{{ $v->id }}">
                                        {{ $v->plate_number }} — {{ trim(($v->brand ?? '') . ' ' . ($v->model ?? '')) ?: 'Vehicle' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('roadsideVehicleId') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        {{-- Landmark --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Landmark <span class="text-slate-400">(optional)</span>
                            </label>
                            <input wire:model="roadsideAddress" type="text"
                                   placeholder="Near what landmark or street?"
                                   class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-red-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-red-100 dark:focus:ring-red-500/20" />
                            @error('roadsideAddress') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Location pin --}}
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Your location</p>
                            <p class="mt-1 font-mono text-xs text-slate-700 dark:text-gray-300 break-all">
                                {{ number_format($userLat ?? 0, 5) }}, {{ number_format($userLng ?? 0, 5) }}
                            </p>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="rs-foot border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-4 sm:px-6 pt-4">
                        <button wire:click="closeRoadsideModal" type="button"
                                class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button wire:click="submitRoadsideRequest"
                                wire:loading.attr="disabled" wire:target="submitRoadsideRequest"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-red-500/30 transition hover:bg-red-700 disabled:opacity-60">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <span wire:loading.remove wire:target="submitRoadsideRequest">Send request</span>
                            <span wire:loading wire:target="submitRoadsideRequest">Sending…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    {{-- Alpine map engine --}}
    @script
    <script>
        window.createLiveMap = function (config) {
            return {
                map: null,
                shopLayer: null,
                requestLayer: null,
                userMarker: null,
                userAccuracyCircle: null,
                locating: false,
                userLocation: null,
                shops: config.shops || [],
                requests: config.requests || [],
                _ro: null,

                init() {
                    if (this.map) {
                        try { this.map.off(); this.map.remove(); } catch (e) {}
                        this.map = null;
                    }

                    const container = document.getElementById('live-map');
                    if (container) {
                        if (container._leaflet_id) {
                            try { delete container._leaflet_id; } catch (e) { container._leaflet_id = null; }
                        }
                        if (container.children.length > 0) container.innerHTML = '';
                    }

                    this.$nextTick(() => {
                        const el = document.getElementById('live-map');
                        if (! el) return;

                        this.map = L.map(el, {
                            center: config.initialCenter || [14.5995, 120.9842],
                            zoom: config.initialZoom || 11,
                            zoomControl: true,
                            scrollWheelZoom: true,
                            tap: true,
                            touchZoom: true,
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        }).addTo(this.map);

                        this.shopLayer    = L.layerGroup().addTo(this.map);
                        this.requestLayer = L.layerGroup().addTo(this.map);

                        this.renderMarkers();

                        setTimeout(() => this.map && this.map.invalidateSize(), 200);

                        if (typeof ResizeObserver !== 'undefined') {
                            this._ro = new ResizeObserver(() => {
                                if (this.map) this.map.invalidateSize();
                            });
                            this._ro.observe(el);
                        }

                        // Phone rotation / tablet split-screen
                        window.addEventListener('orientationchange', () => {
                            setTimeout(() => this.map && this.map.invalidateSize(), 300);
                        });
                    });
                },

                destroy() {
                    if (this._ro) { try { this._ro.disconnect(); } catch (e) {} this._ro = null; }
                    if (this.map) {
                        try { this.map.off(); this.map.remove(); } catch (e) {}
                        this.map = null;
                    }
                },

                updateData(detail) {
                    this.shops    = detail.shops || [];
                    this.requests = detail.requests || [];
                    this.renderMarkers();
                },

                haversine(lat1, lon1, lat2, lon2) {
                    const R = 6371000;
                    const toRad = (d) => (d * Math.PI) / 180;
                    const dLat = toRad(lat2 - lat1);
                    const dLon = toRad(lon2 - lon1);
                    const a = Math.sin(dLat / 2) ** 2 +
                              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                              Math.sin(dLon / 2) ** 2;
                    return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                },

                formatDistance(meters) {
                    if (meters < 1000) return Math.round(meters) + ' m';
                    return (meters / 1000).toFixed(1) + ' km';
                },

                renderMarkers() {
                    if (! this.map) return;

                    this.shopLayer.clearLayers();
                    this.requestLayer.clearLayers();

                    // Keeps popups clear of the sticky header and the screen edges
                    const popupOpts = {
                        maxWidth: 240,
                        autoPanPaddingTopLeft: [16, 90],
                        autoPanPaddingBottomRight: [16, 16],
                    };

                    this.shops.forEach((shop) => {
                        const statusColor = {
                            open:         '#10b981',
                            closing_soon: '#f59e0b',
                            closed:       '#94a3b8',
                            closed_today: '#cbd5e1',
                            inactive:     '#64748b',
                        }[shop.status_key] || '#94a3b8';

                        const distance = shop.distance
                            ?? (this.userLocation
                                ? this.haversine(this.userLocation.lat, this.userLocation.lng, shop.lat, shop.lng)
                                : null);

                        const distanceLabel = shop.distance_label
                            ?? (distance !== null ? this.formatDistance(distance) : null);

                        const icon = L.divIcon({
                            className: 'custom-pin',
                            html: `<div style="position: relative;"><div style="width: 32px; height: 32px; background: ${statusColor}; border: 3px solid #fff; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); box-shadow: 0 4px 10px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center;"><div style="width: 10px; height: 10px; background: #fff; border-radius: 50%;"></div></div></div>`,
                            iconSize:   [32, 32],
                            iconAnchor: [16, 32],
                            popupAnchor: [0, -32],
                        });

                        const marker = L.marker([shop.lat, shop.lng], { icon }).addTo(this.shopLayer);

                        marker.bindPopup(`
                            <div style="min-width: 0; font-family: inherit;">
                                <strong style="font-size: 14px;">${this.escapeHtml(shop.name)}</strong>
                                ${distanceLabel ? `
                                    <div style="font-size: 12px; color: #FF5E14; font-weight: 700; margin-top: 4px;">
                                        📍 ${distanceLabel} away
                                    </div>
                                ` : ''}
                                <div style="font-size: 12px; opacity: 0.75; margin-top: 4px;">
                                    ${this.escapeHtml(shop.address || '')}
                                </div>
                                <div style="font-size: 12px; margin-top: 8px; display: inline-block; padding: 2px 8px; border-radius: 999px; background: ${statusColor}22; color: ${statusColor}; font-weight: 600;">
                                    ${this.escapeHtml(shop.status)}
                                </div>
                                <div style="margin-top: 10px;">
                                    <a href="${shop.url}" style="font-size: 12px; font-weight: 600; color: #FF5E14; text-decoration: none;">
                                        View services →
                                    </a>
                                </div>
                            </div>
                        `, popupOpts);

                        marker.on('click', () => $wire.selectTenant(shop.id));
                    });

                    this.requests.forEach((req) => {
                        const distance = req.distance
                            ?? (this.userLocation
                                ? this.haversine(this.userLocation.lat, this.userLocation.lng, req.lat, req.lng)
                                : null);

                        const distanceLabel = req.distance_label
                            ?? (distance !== null ? this.formatDistance(distance) : null);

                        const icon = L.divIcon({
                            className: 'custom-pin-pulse',
                            html: `<div style="position: relative; width: 24px; height: 24px;"><div class="marker-pulse" style="position: absolute; inset: 0; background: #ef4444; border: 3px solid #fff; border-radius: 50%; box-shadow: 0 4px 10px rgba(239,68,68,0.5);"></div><div style="position: absolute; inset: 6px; background: #fff; border-radius: 50%;"></div></div>`,
                            iconSize:   [24, 24],
                            iconAnchor: [12, 12],
                            popupAnchor: [0, -12],
                        });

                        const marker = L.marker([req.lat, req.lng], { icon, zIndexOffset: 1000 }).addTo(this.requestLayer);

                        marker.bindPopup(`
                            <div style="min-width: 0; font-family: inherit;">
                                <strong style="font-size: 14px; color: #dc2626;">🆘 ${this.escapeHtml(req.request_number)}</strong>
                                ${distanceLabel ? `
                                    <div style="font-size: 12px; color: #dc2626; font-weight: 700; margin-top: 4px;">
                                        📍 ${distanceLabel} from you
                                    </div>
                                ` : ''}
                                <div style="font-size: 12px; margin-top: 6px;">
                                    <div style="text-transform: capitalize; font-weight: 600;">
                                        ${this.escapeHtml(req.problem_type.replace(/_/g, ' '))}
                                    </div>
                                    ${req.vehicle ? `<div style="opacity: 0.75;">Vehicle: ${this.escapeHtml(req.vehicle)}</div>` : ''}
                                    ${req.address ? `<div style="opacity: 0.75; margin-top: 4px;">${this.escapeHtml(req.address)}</div>` : ''}
                                </div>
                            </div>
                        `, popupOpts);

                        marker.on('click', () => $wire.selectRequest(req.id));
                    });
                },

                centerOn(detail) {
                    if (! this.map) return;
                    this.map.flyTo([detail.lat, detail.lng], detail.zoom || 15, { duration: 1 });
                },

                locateMe() {
                    if (! navigator.geolocation) {
                        alert('Geolocation is not supported by this browser.');
                        return;
                    }

                    this.locating = true;

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.locating = false;
                            const { latitude, longitude, accuracy } = pos.coords;

                            this.userLocation = { lat: latitude, lng: longitude, accuracy };

                            this.map.flyTo([latitude, longitude], 16, { duration: 1 });
                            this.drawUserMarker(latitude, longitude, accuracy);

                            $wire.setUserLocation(latitude, longitude);
                            this.renderMarkers();
                        },
                        (err) => {
                            this.locating = false;
                            const messages = {
                                1: 'Location access denied. Enable it in your browser settings.',
                                2: 'Position unavailable. Check your GPS or network connection.',
                                3: 'Location request timed out. Please try again.',
                            };
                            alert(messages[err.code] || 'Could not get your location.');
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0,
                        }
                    );
                },

                requestRoadside() {
                    if (this.locating) return;

                    if (this.userLocation) {
                        $wire.openRoadsideModal();
                        return;
                    }

                    if (! navigator.geolocation) {
                        alert('Geolocation is not supported by this browser.');
                        return;
                    }

                    this.locating = true;

                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            const { latitude, longitude, accuracy } = pos.coords;

                            this.userLocation = { lat: latitude, lng: longitude, accuracy };

                            this.map.flyTo([latitude, longitude], 16, { duration: 1 });
                            this.drawUserMarker(latitude, longitude, accuracy);

                            await $wire.setUserLocation(latitude, longitude);
                            this.renderMarkers();

                            this.locating = false;
                            $wire.openRoadsideModal();
                        },
                        (err) => {
                            this.locating = false;
                            const messages = {
                                1: 'Location access denied. Enable it in your browser settings.',
                                2: 'Position unavailable. Check your GPS or network connection.',
                                3: 'Location request timed out. Please try again.',
                            };
                            alert(messages[err.code] || 'Could not get your location.');
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                },

                drawUserMarker(lat, lng, accuracy) {
                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    if (this.userAccuracyCircle) this.map.removeLayer(this.userAccuracyCircle);

                    if (accuracy && accuracy > 5) {
                        this.userAccuracyCircle = L.circle([lat, lng], {
                            radius: accuracy,
                            color: '#3b82f6',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.10,
                            weight: 1,
                            opacity: 0.4,
                        }).addTo(this.map);
                    }

                    const accuracyText = accuracy
                        ? `accurate to ~${Math.round(accuracy)} m`
                        : 'precise location';

                    this.userMarker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'user-pin',
                            html: `<div style="position: relative;">
                                <div class="user-pulse-ring" style="position: absolute; inset: -9px; background: rgba(59,130,246,0.4); border-radius: 50%;"></div>
                                <div style="width: 18px; height: 18px; background: #3b82f6; border: 3px solid #fff; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3); position: relative; z-index: 1;"></div>
                            </div>`,
                            iconSize:   [18, 18],
                            iconAnchor: [9, 9],
                        }),
                        zIndexOffset: 2000,
                    }).addTo(this.map);

                    this.userMarker.bindPopup(`📍 You are here<br><span style="opacity: 0.6; font-size: 11px;">${accuracyText}</span>`).openPopup();
                },

                escapeHtml(str) {
                    if (! str) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                },
            };
        };
    </script>
    @endscript
</div>