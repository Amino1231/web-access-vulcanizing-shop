<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Update profile</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Manage your account details.</p>
        </div>

        <a href="{{ route('owner.profile') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">

        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-gray-400">Account</h2>

            <div class="flex items-center gap-4">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-gray-700" />
                @elseif (Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-gray-700" />
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-900 dark:bg-gray-700 text-lg font-bold text-white">
                        {{ strtoupper(substr($name ?: 'U', 0, 2)) }}
                    </div>
                @endif

                <label class="cursor-pointer rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 transition">
                    Change photo
                    <input type="file" wire:model="avatar" accept="image/*" class="hidden" />
                </label>
            </div>
            @error('avatar') <p class="text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Full name</label>
                    <input type="text" wire:model="name"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('name') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Email</label>
                    <input type="email" wire:model="email"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('email') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Phone with auto-formatting --}}
                <div
                    x-data="{
                        raw: '',
                        formatted: '',
                        init() {
                            this.raw = ($wire.get('phone') ?? '').replace(/\D+/g, '');
                            if (this.raw.startsWith('63')) this.raw = this.raw.slice(2);
                            if (this.raw.startsWith('0'))  this.raw = this.raw.slice(1);
                            this.raw = this.raw.slice(0, 10);
                            this.update();
                        },
                        onInput(e) {
                            let digits = e.target.value.replace(/\D+/g, '');
                            if (digits.startsWith('63'))      digits = digits.slice(2);
                            else if (digits.startsWith('0'))  digits = digits.slice(1);
                            digits = digits.slice(0, 10);
                            this.raw = digits;
                            this.update();
                        },
                        update() {
                            const d = this.raw;
                            if (d.length === 0) {
                                this.formatted = '';
                            } else if (d.length <= 3) {
                                this.formatted = '+63 ' + d;
                            } else if (d.length <= 7) {
                                this.formatted = '+63 ' + d.slice(0, 3) + ' ' + d.slice(3);
                            } else {
                                this.formatted = '+63 ' + d.slice(0, 3) + ' ' + d.slice(3, 7) + ' ' + d.slice(7, 10);
                            }
                            this.$refs.input.value = this.formatted;
                            $wire.set('phone', this.formatted, false);
                        }
                    }"
                >
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Phone</label>
                    <input
                        x-ref="input"
                        x-on:input="onInput($event)"
                        wire:model.blur="phone"
                        type="tel"
                        inputmode="numeric"
                        autocomplete="tel"
                        maxlength="20"
                        class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                        placeholder="+63 977 2854 041" />
                    <p class="mt-1.5 text-[11px] text-slate-500 dark:text-gray-400">Format: +63 9XX XXXX XXX</p>
                    @error('phone') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Birth date</label>
                    <input type="date" wire:model="birth_date"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('birth_date') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Gender</label>
                    <select wire:model="gender"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                        <option value="">Prefer not to say</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    @error('gender') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Address + Map --}}
            <div class="grid gap-6 lg:grid-cols-2 pt-2">
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Your address</label>
                        <div class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-100 dark:bg-gray-800/50 px-3.5 py-2.5 text-sm text-slate-700 dark:text-gray-300 min-h-[42px]">
                            @if ($address)
                                {{ $address }}
                            @else
                                <span class="italic text-slate-400">Click on the map to auto-fill this address.</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            Auto-filled from the map pin. To change it, move the pin.
                        </p>
                        @error('address') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="use-location-btn"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3" />
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
                            </svg>
                            Use my location
                        </button>

                        <button type="button" id="clear-pin-btn"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-700 dark:text-red-400 transition hover:bg-red-100">
                            Clear pin
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-500 dark:text-gray-400">
                        The pin is only used to help fill your address — nothing is stored on the map.
                    </p>
                </div>

                <div wire:ignore
                    x-data="shopLocationPicker({
                        initialLat: 14.5995,
                        initialLng: 120.9842,
                        hasPin: false,
                    })"
                    class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-100"
                    style="min-height: 320px;"
                >
                    <div x-ref="map" id="update-profile-map" class="h-[320px] w-full"></div>

                    <div class="pointer-events-none absolute left-3 top-3 z-10 rounded-lg bg-white/95 px-3 py-1.5 text-[11px] font-medium text-slate-700 shadow-sm backdrop-blur">
                        Click on the map or drag the pin
                    </div>

                    <div x-show="locating" x-cloak
                        class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-sm">
                        <div class="flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 shadow-lg">
                            <svg class="h-4 w-4 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-xs font-semibold text-slate-700">Locating…</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="save,avatar"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>

    <style>
        #update-profile-map.leaflet-container {
            z-index: 0 !important;
            isolation: isolate;
        }
        #update-profile-map .leaflet-pane         { z-index: 1 !important; }
        #update-profile-map .leaflet-tile-pane    { z-index: 100 !important; }
        #update-profile-map .leaflet-overlay-pane { z-index: 200 !important; }
        #update-profile-map .leaflet-marker-pane  { z-index: 300 !important; }
        #update-profile-map .leaflet-popup-pane   { z-index: 400 !important; }
        #update-profile-map .leaflet-top,
        #update-profile-map .leaflet-bottom       { z-index: 500 !important; }
        #update-profile-map .leaflet-control      { z-index: 500 !important; }
    </style>

    @script
    <script>
        window.shopLocationPicker = function (config) {
            return {
                map: null,
                marker: null,
                locating: false,
                lat: config.initialLat,
                lng: config.initialLng,

                init() {
                    this.$nextTick(() => {
                        const el = this.$refs.map;
                        if (! el) return;

                        if (el._leaflet_id) {
                            try { delete el._leaflet_id; } catch (e) { el._leaflet_id = null; }
                        }

                        this.map = L.map(el, {
                            center: [config.initialLat, config.initialLng],
                            zoom: config.hasPin ? 15 : 11,
                            zoomControl: true,
                            scrollWheelZoom: true,
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(this.map);

                        if (config.hasPin) {
                            this.placeMarker(config.initialLat, config.initialLng, false);
                        }

                        this.map.on('click', (e) => {
                            this.placeMarker(e.latlng.lat, e.latlng.lng, true);
                        });

                        setTimeout(() => this.map.invalidateSize(), 200);

                        document.getElementById('use-location-btn')?.addEventListener('click', () => this.locateMe());
                        document.getElementById('clear-pin-btn')?.addEventListener('click', () => this.clearPin());
                    });
                },

                placeMarker(lat, lng, pushToLivewire) {
                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    } else {
                        this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);

                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.onPinMoved(pos.lat, pos.lng);
                        });
                    }

                    this.lat = lat;
                    this.lng = lng;

                    if (pushToLivewire) {
                        this.onPinMoved(lat, lng);
                    }
                },

                onPinMoved(lat, lng) {
                    this.lat = lat;
                    this.lng = lng;
                    this.reverseGeocode(lat, lng);
                },

                clearPin() {
                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                        this.marker = null;
                    }

                    this.lat = null;
                    this.lng = null;

                    $wire.set('address', null, false);
                },

                locateMe() {
                    if (! navigator.geolocation) {
                        alert('Geolocation is not supported.');
                        return;
                    }

                    this.locating = true;

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const { latitude, longitude } = pos.coords;

                            this.map.flyTo([latitude, longitude], 16, { duration: 1 });
                            this.placeMarker(latitude, longitude, true);

                            this.locating = false;
                        },
                        (err) => {
                            this.locating = false;
                            alert('Could not get your location. Check your browser permissions.');
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                },

                async reverseGeocode(lat, lng) {
                    try {
                        const url = 'https://nominatim.openstreetmap.org/reverse?'
                            + new URLSearchParams({
                                format: 'json',
                                lat: String(lat),
                                lon: String(lng),
                                zoom: '18',
                                addressdetails: '1',
                            });

                        const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();

                        if (data && data.display_name) {
                            $wire.setAddressFromMap(data.display_name);
                        }
                    } catch (e) {
                        // Silent — reverse geocode is best-effort
                    }
                },
            };
        };
    </script>
    @endscript
</div>