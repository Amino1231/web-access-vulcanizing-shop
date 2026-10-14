<div class="mx-auto max-w-3xl space-y-6"
     x-data="{ notifications: [] }"
     @notify.window="
        notifications.push($event.detail);
        setTimeout(() => notifications.shift(), 4000);
     ">

    {{-- Toast stack --}}
    <div class="pointer-events-none fixed right-4 top-4 z-50 flex flex-col gap-2">
        <template x-for="(n, i) in notifications" :key="i">
            <div x-transition.opacity.duration.200ms
                 class="pointer-events-auto w-80 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg"
                 :class="{
                     'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-300': n.type === 'success',
                     'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300': n.type === 'error'
                 }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    @if (! $this->employee)
        <div class="rounded-2xl border border-dashed border-red-300 dark:border-red-500/40 bg-red-50 dark:bg-red-500/5 p-10 text-center">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                Your account isn't linked to any shop yet.
            </p>
        </div>
    @else
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                    {{ $this->employee->tenant?->name ?? 'Employee portal' }}
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Update Profile
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Keep your contact details up to date.
                </p>
            </div>

            <a href="{{ route('employee.profile') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        </div>

        <form wire:submit="save" class="space-y-6">

            {{-- Avatar --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Photo
                </h3>

                <div class="flex items-center gap-5">
                    <div class="shrink-0">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}"
                                 class="h-20 w-20 rounded-2xl object-cover border border-slate-200 dark:border-gray-700" />
                        @elseif ($this->user->avatar)
                            <img src="{{ asset('storage/' . $this->user->avatar) }}"
                                 class="h-20 w-20 rounded-2xl object-cover border border-slate-200 dark:border-gray-700" />
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-white dark:bg-gray-700">
                                {{ strtoupper(substr($this->user->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Choose photo
                            <input type="file" wire:model="avatar" accept="image/*" class="hidden" />
                        </label>
                        <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">
                            JPG or PNG. Max 2 MB.
                        </p>
                        @error('avatar') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="avatar" class="mt-2 text-xs text-slate-500">
                            Uploading…
                        </div>
                    </div>
                </div>
            </div>

            {{-- Personal --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Personal info
                </h3>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Full name <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text" required
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" type="email" required
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Phone
                        </label>
                        <input
                            wire:model.blur="phone"
                            type="tel"
                            inputmode="numeric"
                            autocomplete="tel-national"
                            placeholder="+63 912 345 6789"
                            x-on:input="
                                const raw = $event.target.value;
                                const digits = raw.replace(/\D/g, '');
                                let local = digits.replace(/^63/, '').replace(/^0/, '').slice(0, 10);

                                if (! local) {
                                    $event.target.value = '';
                                    return;
                                }

                                let out = '+63';
                                if (local.length > 0) out += ' ' + local.slice(0, 3);
                                if (local.length > 3) out += ' ' + local.slice(3, 6);
                                if (local.length > 6) out += ' ' + local.slice(6, 10);

                                $event.target.value = out;
                            "
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100"
                        />
                        @error('phone') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1.5 text-xs text-slate-500 dark:text-gray-400">
                            Digits only — the +63 prefix is added automatically.
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Birth date
                        </label>
                        <input wire:model="birth_date" type="date"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('birth_date') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Gender
                        </label>
                        <select wire:model="gender"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100">
                            <option value="">Prefer not to say</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        @error('gender') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Address with map picker --}}
                    <div class="sm:col-span-2">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Address
                            </label>
                            <button type="button" wire:click="openMapPicker"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 bg-orange-50 px-2.5 py-1 text-xs font-semibold text-[#FF5E14] transition hover:bg-orange-100 dark:border-orange-500/30 dark:bg-orange-500/10 dark:hover:bg-orange-500/20">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Pick on map
                            </button>
                        </div>
                        <textarea wire:model="address" rows="2"
                                  placeholder="Type your address or pick it on the map"
                                  class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100"></textarea>
                        @error('address') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Read-only employment note --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-gray-800 dark:bg-gray-900/60">
                <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Locked fields
                </h3>
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Position, hired date, and commission rate are managed by your shop owner.
                    Contact them if any of these need updating.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('employee.profile') }}"
                   class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </form>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Map picker modal — teleported to <body> --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($showMapPicker)
        @teleport('body')
        <div
            x-data="{
                map: null,
                marker: null,
                pickedAddress: '',
                loading: false,
                ready: false,

                async init() {
                    await this.loadLeaflet();

                    await new Promise((r) => setTimeout(r, 50));

                    const el = this.$refs.mapEl;
                    if (! el) return;

                    const initialLat = 14.5995;
                    const initialLng = 120.9842;

                    this.map = L.map(el, {
                        center: [initialLat, initialLng],
                        zoom: 12,
                        zoomControl: true,
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap',
                    }).addTo(this.map);

                    this.map.on('click', (e) => this.pick(e.latlng.lat, e.latlng.lng));

                    this.ready = true;

                    setTimeout(() => this.map && this.map.invalidateSize(), 150);

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                const { latitude, longitude } = pos.coords;
                                this.map.setView([latitude, longitude], 16);
                                this.pick(latitude, longitude);
                            },
                            () => {},
                            { enableHighAccuracy: true, timeout: 8000 }
                        );
                    }
                },

                async loadLeaflet() {
                    if (typeof L !== 'undefined') return;

                    if (! document.querySelector('link[data-leaflet]')) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        link.setAttribute('data-leaflet', '1');
                        document.head.appendChild(link);
                    }

                    await new Promise((resolve) => {
                        const s = document.createElement('script');
                        s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        s.onload = resolve;
                        document.head.appendChild(s);
                    });
                },

                async pick(lat, lng) {
                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    } else {
                        this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                        this.marker.on('dragend', (e) => {
                            const p = e.target.getLatLng();
                            this.reverse(p.lat, p.lng);
                        });
                    }

                    await this.reverse(lat, lng);
                },

                async reverse(lat, lng) {
                    this.loading = true;
                    try {
                        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
                        const res = await fetch(url, { headers: { 'Accept-Language': 'en' } });
                        const data = await res.json();
                        this.pickedAddress = data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    } catch (e) {
                        this.pickedAddress = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    } finally {
                        this.loading = false;
                    }
                },

                locate() {
                    if (! navigator.geolocation) return alert('Geolocation not supported by this browser.');
                    this.loading = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const { latitude, longitude } = pos.coords;
                            this.map.setView([latitude, longitude], 17);
                            this.pick(latitude, longitude);
                        },
                        () => { this.loading = false; alert('Could not get your location.'); },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                },

                confirm() {
                    if (! this.pickedAddress) return;
                    $wire.set('address', this.pickedAddress);
                    $wire.closeMapPicker();
                }
            }"
            x-init="init()"
            @keydown.escape.window="$wire.closeMapPicker()"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/70 p-3 sm:p-6 backdrop-blur-sm">

            <div class="flex max-h-[94vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

                {{-- Header --}}
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-5 py-4">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Pick your location
                        </p>
                        <h3 class="mt-0.5 truncate text-lg font-bold text-slate-900 dark:text-white">
                            Tap anywhere on the map
                        </h3>
                    </div>
                    <button type="button" wire:click="closeMapPicker"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="flex-1 min-h-0 overflow-hidden">
                    <div x-ref="mapEl" class="h-[52vh] min-h-[320px] w-full bg-slate-100 dark:bg-gray-800"></div>
                </div>

                {{-- Picked address strip --}}
                <div class="border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-5 py-3">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-500/20">
                            <svg class="h-4 w-4 text-[#FF5E14]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                                Selected address
                            </p>
                            <p class="mt-0.5 text-sm text-slate-800 dark:text-gray-200"
                               x-text="loading ? 'Looking up address…' : (pickedAddress || 'Tap the map to set your location')"></p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-2 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" @click="locate()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="3" />
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
                        </svg>
                        Use my location
                    </button>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <button type="button" wire:click="closeMapPicker"
                                class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="button" @click="confirm()"
                                :disabled="! pickedAddress || loading"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Use this address
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>