<div class="space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Business setup</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Submit your shop details</h1>
            </div>

            @php
                $current = \Illuminate\Support\Facades\Auth::user()?->currentTenant();
                $currentStatus = $current?->verification_status;
                $currentStatusValue = $currentStatus instanceof \App\Enums\VerificationStatus
                    ? $currentStatus->value
                    : $currentStatus;
            @endphp

            @if ($currentStatusValue !== 'approved')
                <div
                    class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700">
                    Approval review required
                </div>
            @endif
        </div>

        @if (session('success'))
            <div
                class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                {{-- Shop name --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Shop name <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="shop_name" type="text"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        placeholder="e.g. RoadMaster Tire Hub" />
                    @error('shop_name') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Business email --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Business email <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="shop_email" type="email"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        placeholder="business@shop.com" />
                    @error('shop_email') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Phone --}}
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
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Business contact <span class="text-red-500">*</span>
                    </label>
                    <input
                        x-ref="input"
                        x-on:input="onInput($event)"
                        wire:model.blur="phone"
                        type="tel"
                        inputmode="numeric"
                        autocomplete="tel"
                        maxlength="20"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-100"
                        placeholder="+63 977 2854 041" />
                    <p class="mt-1.5 text-[11px] text-slate-500">
                        Format: +63 9XX XXXX XXX
                    </p>
                    @error('phone') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Logo --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Business logo</label>
                    <input wire:model="logo" type="file" accept="image/*"
                        class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white" />
                    @error('logo') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror

                    <div wire:loading wire:target="logo" class="mt-2 text-xs text-slate-400">Uploading…</div>

                    {{-- Preview: new upload takes priority, else show existing --}}
                    @if ($logo)
                        <div class="mt-3 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3">
                            <div class="relative">
                                <img src="{{ $logo->temporaryUrl() }}" alt="New logo preview"
                                    class="h-20 w-20 rounded-xl object-cover" />
                                <button type="button" wire:click="removeLogo" title="Remove this logo"
                                    class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow-md transition hover:bg-red-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="pt-1">
                                <p class="text-xs font-semibold text-emerald-700">New logo ready to save</p>
                                <p class="mt-0.5 text-[11px] text-emerald-600">Click the × to remove it.</p>
                            </div>
                        </div>
                    @elseif ($existingLogo)
                        <div class="mt-3 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="relative">
                                <img src="{{ asset('storage/' . $existingLogo) }}" alt="Current logo"
                                    class="h-20 w-20 rounded-xl object-cover" />
                                <button type="button" wire:click="removeExistingLogo"
                                    wire:confirm="Remove the current logo? This won't save until you click Submit."
                                    title="Remove current logo"
                                    class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow-md transition hover:bg-red-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="pt-1">
                                <p class="text-xs font-semibold text-slate-700">Current logo</p>
                                <p class="mt-0.5 text-[11px] text-slate-500">Upload a new file to replace, or click × to
                                    remove.</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Business hours --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Business hours</label>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        @foreach ($dayNames as $day)
                            <div wire:key="hours-{{ $day }}" class="grid grid-cols-[100px_1fr_1fr] items-center gap-3 py-2">
                                <span class="text-sm font-medium text-slate-700">{{ $day }}</span>
                                <select wire:model="business_hours.{{ $day }}.open"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-900">
                                    @foreach ($timeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select wire:model="business_hours.{{ $day }}.close"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-900">
                                    @foreach ($timeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error("business_hours.{$day}.close")
                                <p class="px-3 pb-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </div>
                    @error('business_hours') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- LOCATION + MAP --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Read-only address + coordinates --}}
                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            Business address <span class="text-red-500">*</span>
                        </label>
                        <div class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700">
                            @if ($address)
                                {{ $address }}
                            @else
                                <span class="italic text-slate-400">Drop a pin on the map to auto-fill this address.</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-500">
                            Auto-filled from the map pin. To change it, move the pin.
                        </p>
                        @error('address') <span class="mt-2 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="use-location-btn"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3" />
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
                            </svg>
                            Use my location
                        </button>

                        @if ($latitude !== null && $longitude !== null)
                            <button type="button" wire:click="clearLatLng"
                                class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                                Clear pin
                            </button>
                        @endif
                    </div>

                    {{-- Coordinates readout --}}
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            Coordinates <span class="text-red-500">*</span>
                        </p>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-slate-500">Latitude</p>
                                <p class="mt-0.5 font-mono text-sm text-slate-800">
                                    {{ $latitude !== null ? number_format($latitude, 6) : '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-slate-500">Longitude</p>
                                <p class="mt-0.5 font-mono text-sm text-slate-800">
                                    {{ $longitude !== null ? number_format($longitude, 6) : '—' }}
                                </p>
                            </div>
                        </div>
                        @error('latitude')  <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('longitude') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Map --}}
                <div wire:ignore
                    x-data="shopLocationPicker({
                        initialLat: {{ $latitude ?? 14.5995 }},
                        initialLng: {{ $longitude ?? 120.9842 }},
                        hasPin: {{ $latitude !== null && $longitude !== null ? 'true' : 'false' }},
                    })"
                    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-100"
                    style="min-height: 380px;"
                >
                    <div x-ref="map" id="shop-map-picker" class="h-[380px] w-full"></div>

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

            {{-- Permit documents --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Permit / business documents
                    @if (empty($existingAttachments))
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if (empty($existingAttachments))
                    <p class="mb-2 text-xs text-slate-500">
                        Upload at least one scanned PDF, or a clear photo of your printed document.
                        Accepted formats: PDF, JPG, PNG, WEBP. Max 4 MB per file. Up to 5 files.
                    </p>
                @else
                    <p class="mb-2 text-xs text-slate-500">
                        You already have documents on file. Upload additional documents below to add to the list.
                        Accepted formats: PDF, JPG, PNG, WEBP. Max 4 MB per file.
                    </p>
                @endif

                <input wire:model="permit_documents" type="file" multiple
                    class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white" />

                @error('permit_documents')
                    <div class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2">
                        <p class="text-xs font-semibold text-red-700">{{ $message }}</p>
                    </div>
                @enderror
                @error('permit_documents.*')
                    <div class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2">
                        <p class="text-xs font-semibold text-red-700">{{ $message }}</p>
                    </div>
                @enderror

                <div wire:loading wire:target="permit_documents" class="mt-2 text-xs text-slate-400">Uploading…</div>

                {{-- Newly selected files --}}
                @if (! empty($permit_documents))
                    <div class="mt-3 space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-600">
                            Ready to upload ({{ count($permit_documents) }})
                        </p>
                        @foreach ($permit_documents as $index => $permit)
                            <div wire:key="new-permit-{{ $index }}-{{ $permit->getFilename() }}"
                                class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">
                                <div class="flex min-w-0 items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="truncate text-sm font-medium text-emerald-700">
                                        {{ $permit->getClientOriginalName() }}
                                    </span>
                                </div>
                                <button type="button" wire:click="removePermitDocument({{ $index }})" title="Remove this file"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-red-100 hover:text-red-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Existing attachments from a previous save --}}
                @if (! empty($existingAttachments))
                    <div class="mt-3 space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Currently on file</p>
                        @foreach ($existingAttachments as $index => $path)
                            <div wire:key="existing-att-{{ $index }}"
                                class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <a href="{{ asset('storage/' . $path) }}" target="_blank"
                                    class="truncate text-sm font-medium text-slate-700 underline hover:text-slate-900">
                                    {{ basename($path) }}
                                </a>
                                <button type="button" wire:click="removeExistingAttachment({{ $index }})"
                                    wire:confirm="Remove this document? The change won't be saved until you click Submit."
                                    class="text-xs font-semibold text-red-600 hover:text-red-700">
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Submit --}}
            <div class="flex flex-col items-start justify-between gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-center">
                <p class="text-xs text-slate-500">
                    On submit, your shop enters the admin approval queue. You'll be able to list products once approved.
                </p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('owner.dashboard') }}" wire:navigate
                        class="text-sm font-medium text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save,logo,permit_documents"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Submit for approval</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Submitting…
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        #shop-map-picker.leaflet-container {
            z-index: 0 !important;
            isolation: isolate;
        }
        #shop-map-picker .leaflet-pane         { z-index: 1 !important; }
        #shop-map-picker .leaflet-tile-pane    { z-index: 100 !important; }
        #shop-map-picker .leaflet-overlay-pane { z-index: 200 !important; }
        #shop-map-picker .leaflet-marker-pane  { z-index: 300 !important; }
        #shop-map-picker .leaflet-popup-pane   { z-index: 400 !important; }
        #shop-map-picker .leaflet-top,
        #shop-map-picker .leaflet-bottom       { z-index: 500 !important; }
        #shop-map-picker .leaflet-control      { z-index: 500 !important; }
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
                    $wire.setLatLng(lat, lng);
                    this.reverseGeocode(lat, lng);
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