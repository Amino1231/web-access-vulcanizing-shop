<div class="bg-white dark:bg-gray-900 transition-colors duration-300">

    <main>

        {{-- ════════════════════════════════════════════════════ --}}
        {{-- HERO --}}
        {{-- ════════════════════════════════════════════════════ --}}
        <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
            <div class="absolute inset-0 opacity-5 dark:opacity-10">
                <div class="absolute top-20 left-10 w-72 h-72 bg-orange-500 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 right-10 w-96 h-96 bg-orange-400 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 lg:py-32">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                    {{-- Left: copy --}}
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 text-xs sm:text-sm font-semibold rounded-full mb-6">
                            <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                            {{ $this->openNowCount }} {{ \Illuminate\Support\Str::plural('shop', $this->openNowCount) }} open now
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-tight mb-6">
                            Find your
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600">Vulcanizing Shop</span>
                            near you
                        </h1>

                        <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-lg mx-auto lg:mx-0">
                            Locate trusted vulcanizing shops near you. Book appointments instantly, get roadside help, and get back on the road faster.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
                            <a href="#map"
                               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl shadow-lg shadow-orange-500/30 transition-all hover:shadow-orange-500/40 hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Find shops near me
                            </a>
                            <a href="#map"
                               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                Browse shops
                            </a>
                        </div>

                        <div class="grid grid-cols-3 gap-4 sm:gap-6">
                            <div class="text-center lg:text-left">
                                <p class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">{{ $this->shopCount }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Partner Shops</p>
                            </div>
                            <div class="text-center lg:text-left">
                                <p class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">{{ $this->openNowCount }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Open Right Now</p>
                            </div>
                            <div class="text-center lg:text-left">
                                <p class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">24/7</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Roadside Help</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right: mini-map preview card --}}
                    <div class="relative">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl shadow-gray-900/10 dark:shadow-black/30 overflow-hidden border border-gray-200 dark:border-gray-700">
                            <div class="relative h-64 sm:h-80 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                                <div class="text-center px-6">
                                    <svg class="w-14 h-14 text-orange-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Live map below</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $this->shopCount }} shops on the map · click "Find shops near me" to sort by distance
                                    </p>
                                </div>

                                <div class="absolute top-12 left-16 w-3 h-3 bg-orange-500 rounded-full animate-ping"></div>
                                <div class="absolute top-12 left-16 w-3 h-3 bg-orange-500 rounded-full"></div>
                                <div class="absolute bottom-16 right-20 w-3 h-3 bg-orange-500 rounded-full animate-ping"></div>
                                <div class="absolute bottom-16 right-20 w-3 h-3 bg-orange-500 rounded-full"></div>
                            </div>

                            <div class="p-5 sm:p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Verified shops only</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Every shop on the map is reviewed and approved by our team.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════ --}}
        {{-- LIVE MAP --}}
        {{-- ════════════════════════════════════════════════════ --}}
        <section id="map" class="py-16 sm:py-20 bg-white dark:bg-gray-900"
                 wire:poll.15s="pollRefresh">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <span class="text-xs sm:text-sm font-semibold text-orange-500 uppercase tracking-wider">Locator</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white mt-2 mb-4">Shops near you</h2>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Click "Locate me" to sort shops by distance. Every pin is a verified partner shop you can book with.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1fr_360px]">

                    {{-- Map --}}
                    <div
                        wire:ignore
                        x-data="window.createLandingMap({
                            shops: @js($showShops ? $this->shops : []),
                            initialCenter: [{{ $centerLat }}, {{ $centerLng }}],
                            initialZoom: {{ $zoom }},
                        })"
                        @map:update.window="updateData($event.detail)"
                        @map:center.window="centerOn($event.detail)"
                        class="relative rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm"
                    >
                        <div id="landing-map" class="h-[500px] sm:h-[600px] w-full"></div>

                        {{-- Live pill --}}
                        <div class="absolute right-4 top-4 z-10 flex items-center gap-2 rounded-full bg-white/95 dark:bg-gray-900/95 backdrop-blur border border-slate-200 dark:border-gray-700 px-3 py-1.5 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-gray-200">Live</span>
                        </div>

                        {{-- Locate button --}}
                        <button type="button" id="landing-locate" @click="locateMe()"
                                class="absolute bottom-4 right-4 z-10 flex h-11 w-11 items-center justify-center rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 shadow-lg text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-800"
                                :class="locating && 'animate-pulse'"
                                title="Center on my location">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3" />
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
                            </svg>
                        </button>

                        {{-- Locate CTA --}}
                        @if ($userLat === null)
                            <button type="button" @click="document.getElementById('landing-locate')?.click()"
                                    class="absolute bottom-4 left-4 z-10 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="3" />
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
                                </svg>
                                Locate me
                            </button>
                        @endif
                    </div>

                    {{-- Side panel --}}
                    <div class="space-y-3">
                        @if ($this->selectedShop)
                            @php $s = $this->selectedShop; @endphp
                            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Shop</p>
                                        <h3 class="mt-1 truncate font-bold text-slate-900 dark:text-white">{{ $s['name'] }}</h3>
                                    </div>
                                    <button wire:click="clearSelection" type="button"
                                            class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                @if ($s['distance_label'])
                                    <div class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-orange-50 dark:bg-orange-500/10 px-2.5 py-1 text-xs font-bold text-[#FF5E14]">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $s['distance_label'] }} away
                                    </div>
                                @endif

                                <div class="space-y-2 text-sm">
                                    @if ($s['status'])
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block h-2 w-2 rounded-full bg-{{ $s['status_key'] === 'open' ? 'emerald' : ($s['status_key'] === 'closing_soon' ? 'amber' : 'slate') }}-500"></span>
                                            <span class="font-medium text-slate-700 dark:text-gray-200">{{ $s['status'] }}</span>
                                        </div>
                                    @endif
                                    @if ($s['address'])
                                        <p class="text-slate-600 dark:text-gray-400">{{ $s['address'] }}</p>
                                    @endif
                                    @if ($s['phone'])
                                        <p class="text-slate-600 dark:text-gray-400">{{ $s['phone'] }}</p>
                                    @endif
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <button type="button" wire:click="centerOn({{ $s['lat'] }}, {{ $s['lng'] }}, 16)"
                                            class="flex-1 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50">
                                        Zoom in
                                    </button>
                                    <a href="{{ $s['url'] }}" @if ($s['is_authed']) wire:navigate @endif
                                       class="flex-1 inline-flex items-center justify-center rounded-xl bg-[#FF5E14] px-3 py-2 text-xs font-semibold text-white hover:bg-[#D94E10]">
                                        {{ $s['is_authed'] ? 'View services' : 'Sign in to view' }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                                        All shops
                                    </h3>
                                    @if ($userLat !== null)
                                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                            Sorted by distance
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-2 max-h-[520px] overflow-y-auto">
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
                                                            default        => 'slate',
                                                        };
                                                    @endphp

                                                    <div class="mt-1 flex items-center gap-1">
                                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-{{ $listDot }}-500"></span>
                                                        <span class="text-[10px] font-medium text-slate-500 dark:text-gray-500">
                                                            {{ $shop['status'] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                @if ($shop['distance_label'])
                                                    <div class="shrink-0 text-right">
                                                        <p class="text-sm font-bold text-[#FF5E14]">{{ $shop['distance_label'] }}</p>
                                                        @if ($index === 0 && $userLat !== null)
                                                            <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                                                Nearest
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </button>
                                    @empty
                                        <p class="text-sm text-slate-500 dark:text-gray-400">
                                            No active shops with location data yet.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════ --}}
        {{-- SERVICES --}}
        {{-- ════════════════════════════════════════════════════ --}}
        <section class="py-16 sm:py-24 bg-gray-50 dark:bg-gray-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 sm:mb-16">
                    <span class="text-xs sm:text-sm font-semibold text-orange-500 uppercase tracking-wider">Our Services</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white mt-2 mb-4">What you can book</h2>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        From minor punctures to complete tire replacement, partner shops on the platform cover it all.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">

                    @php
                        $services = [
                            ['title' => 'Tire Vulcanizing',  'desc' => 'Hot and cold vulcanizing for permanent tire repairs. Stronger than patches, built to last.', 'icon' => 'cog'],
                            ['title' => 'Tire Replacement',  'desc' => 'New and used tires for all vehicle types. Find the perfect fit for your ride.', 'icon' => 'refresh'],
                            ['title' => 'Puncture Repair',   'desc' => 'Quick and reliable puncture fixes. Most repairs done in under 30 minutes.', 'icon' => 'plus'],
                            ['title' => 'Tire Inflation',    'desc' => 'Proper tire pressure for better fuel efficiency and longer tire life.', 'icon' => 'bolt'],
                            ['title' => 'Wheel Balancing',   'desc' => 'Smooth rides with precision wheel balancing. Reduce vibration and tire wear.', 'icon' => 'shield'],
                            ['title' => 'Emergency Roadside','desc' => 'Stranded? Request roadside help through the map and a nearby shop will respond.', 'icon' => 'calendar'],
                        ];
                    @endphp

                    @foreach ($services as $svc)
                        <div class="group p-6 sm:p-8 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 hover:border-orange-200 dark:hover:border-orange-500/30 hover:shadow-xl hover:shadow-orange-500/5 transition-all duration-300">
                            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/20 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                                @if ($svc['icon'] === 'cog')
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                @elseif ($svc['icon'] === 'refresh')
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                @elseif ($svc['icon'] === 'plus')
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif ($svc['icon'] === 'bolt')
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                @elseif ($svc['icon'] === 'shield')
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $svc['title'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $svc['desc'] }}</p>
                            <a href="#map"
                               class="mt-5 flex items-center text-sm font-medium text-orange-500 group-hover:gap-2 transition-all">
                                Find a shop
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════ --}}
        {{-- CTA --}}
        {{-- ════════════════════════════════════════════════════ --}}
        <section class="py-16 sm:py-20 bg-gradient-to-r from-orange-500 to-orange-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-4">
                    Need tire service now?
                </h2>
                <p class="text-orange-100 max-w-xl mx-auto mb-8">
                    Find a shop near you, browse their services, and book in seconds.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#map"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-orange-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Find shops near me
                    </a>
                    @guest
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-orange-400/30 hover:bg-orange-400/40 text-white font-semibold rounded-xl border border-white/30 transition-all hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Create an account
                    </a>
                    @endguest
                </div>
            </div>
        </section>

    </main>

    <style>
        #landing-map.leaflet-container {
            z-index: 0 !important;
            isolation: isolate;
        }
        #landing-map .leaflet-pane         { z-index: 1 !important; }
        #landing-map .leaflet-tile-pane    { z-index: 100 !important; }
        #landing-map .leaflet-overlay-pane { z-index: 200 !important; }
        #landing-map .leaflet-marker-pane  { z-index: 300 !important; }
        #landing-map .leaflet-popup-pane   { z-index: 400 !important; }
        #landing-map .leaflet-top,
        #landing-map .leaflet-bottom       { z-index: 500 !important; }
        #landing-map .leaflet-control      { z-index: 500 !important; }

        @keyframes landing-user-pulse {
            0%   { transform: scale(1);   opacity: 0.6; }
            70%  { transform: scale(2.5); opacity: 0;   }
            100% { transform: scale(2.5); opacity: 0;   }
        }
        .landing-user-pulse-ring { animation: landing-user-pulse 2s ease-out infinite; }
    </style>

    @script
    <script>
        window.createLandingMap = function (config) {
            return {
                map: null,
                shopLayer: null,
                userMarker: null,
                userAccuracyCircle: null,
                locating: false,
                userLocation: null,
                shops: config.shops || [],

                init() {
                    if (this.map) {
                        try { this.map.off(); this.map.remove(); } catch (e) {}
                        this.map = null;
                    }

                    const container = document.getElementById('landing-map');
                    if (container) {
                        if (container._leaflet_id) {
                            try { delete container._leaflet_id; } catch (e) { container._leaflet_id = null; }
                        }
                        if (container.children.length > 0) container.innerHTML = '';
                    }

                    this.$nextTick(() => {
                        const el = document.getElementById('landing-map');
                        if (! el) return;

                        this.map = L.map(el, {
                            center: config.initialCenter || [14.5995, 120.9842],
                            zoom: config.initialZoom || 6,
                            zoomControl: true,
                            scrollWheelZoom: true,
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        }).addTo(this.map);

                        this.shopLayer = L.layerGroup().addTo(this.map);

                        this.renderMarkers();

                        setTimeout(() => this.map && this.map.invalidateSize(), 200);
                    });
                },

                destroy() {
                    if (this.map) {
                        try { this.map.off(); this.map.remove(); } catch (e) {}
                        this.map = null;
                    }
                },

                updateData(detail) {
                    this.shops = detail.shops || [];
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
                            <div style="min-width: 200px; font-family: inherit;">
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
                                        ${shop.is_authed ? 'View services →' : 'Sign in to view →'}
                                    </a>
                                </div>
                            </div>
                        `);

                        marker.on('click', () => $wire.selectTenant(shop.id));
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

                            this.map.flyTo([latitude, longitude], 11, { duration: 1 });
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

                    this.userMarker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'user-pin',
                            html: `<div style="position: relative;">
                                <div class="landing-user-pulse-ring" style="position: absolute; inset: -9px; background: rgba(59,130,246,0.4); border-radius: 50%;"></div>
                                <div style="width: 18px; height: 18px; background: #3b82f6; border: 3px solid #fff; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3); position: relative; z-index: 1;"></div>
                            </div>`,
                            iconSize:   [18, 18],
                            iconAnchor: [9, 9],
                        }),
                        zIndexOffset: 2000,
                    }).addTo(this.map);

                    this.userMarker.bindPopup('📍 You are here').openPopup();
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