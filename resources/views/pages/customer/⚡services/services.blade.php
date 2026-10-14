<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                    Book a service
                </p>

                @if ($tenantFilter !== '')
                    @php $shop = collect($this->tenants)->firstWhere('id', (int) $tenantFilter); @endphp
                    <div class="mt-1 flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ $shop['name'] ?? 'Shop' }} services
                        </h1>
                        <a href="{{ route('customer.services.index') }}"
                           class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 dark:bg-orange-500/10 px-3 py-1 text-xs font-semibold text-[#FF5E14] transition hover:bg-orange-100 dark:hover:bg-orange-500/20">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear shop filter
                        </a>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                        Showing only services offered by this shop.
                    </p>
                @else
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Available services
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                        Pick a service and schedule an appointment at your nearest shop.
                    </p>
                @endif
            </div>

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
                 class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-2.5 shadow-sm shrink-0">
                <div class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#FF5E14] opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#FF5E14]"></span>
                </div>
                <div class="text-right">
                    <p class="font-mono text-lg font-bold leading-tight tracking-tight text-slate-900 dark:text-white" x-text="time"></p>
                    <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-500 dark:text-gray-400" x-text="date + ' · PHT'"></p>
                </div>
                <svg class="h-5 w-5 text-[#FF5E14]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" d="M12 7v5l3 2" />
                </svg>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative w-full sm:w-80">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search services..."
                           class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                </div>

                <select wire:model.live="tenantFilter"
                        class="w-full sm:w-56 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                    <option value="">All shops</option>
                    @foreach ($this->tenants as $t)
                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                    @endforeach
                </select>

                @if ($search || $tenantFilter)
                    <button wire:click="clearFilters" type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                        Clear
                    </button>
                @endif
            </div>
        </div>

        {{-- Grid --}}
        @php $services = $this->services; @endphp

        @if ($services->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-16 text-center">
                <p class="text-sm text-slate-500 dark:text-gray-400">No services available yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($services as $service)
                    <article wire:key="svc-{{ $service->id }}"
                             class="group flex flex-col rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-lg p-5">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                @php
                                    $tenant = $service->tenant;
                                    $status = $tenant?->availabilityStatus() ?? 'inactive';
                                    $statusClasses = match ($status) {
                                        'open'         => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                        'closing_soon' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        'closed'       => 'bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-gray-400',
                                        'closed_today' => 'bg-slate-100 text-slate-500 dark:bg-gray-800 dark:text-gray-500',
                                        default        => 'bg-slate-100 text-slate-500 dark:bg-gray-800 dark:text-gray-500',
                                    };
                                    $dotColor = match ($status) {
                                        'open'         => 'bg-emerald-500',
                                        'closing_soon' => 'bg-amber-500',
                                        default        => 'bg-slate-400',
                                    };
                                @endphp

                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-[11px] font-medium text-slate-500 dark:text-gray-400">
                                        {{ $tenant?->name }}
                                    </span>
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $statusClasses }}">
                                        @if (in_array($status, ['open', 'closing_soon']))
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $dotColor }} opacity-75"></span>
                                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                            </span>
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                        @endif
                                        {{ $tenant?->availabilityLabel() }}
                                    </span>
                                </div>

                                <h3 class="mt-1 truncate text-base font-semibold text-slate-900 dark:text-white">
                                    {{ $service->name }}
                                </h3>
                                <p class="mt-1 text-[11px] uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                    {{ $service->category?->name ?? 'Uncategorized' }}
                                </p>
                            </div>

                            @php
                                $rating = $service->averageRating();
                                $count  = $service->reviewsCount();
                            @endphp

                            @if ($rating)
                                <div class="mt-1 flex items-center gap-1.5">
                                    <div class="flex items-center gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="h-3 w-3 {{ $i <= round($rating) ? 'text-yellow-400' : 'text-slate-300 dark:text-gray-600' }}"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-700 dark:text-gray-300">{{ number_format($rating, 1) }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-gray-500">({{ $count }})</span>
                                </div>
                            @endif
                            @if ($service->is_vehicle_service)
                                <span class="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                    Vehicle
                                </span>
                            @endif
                        </div>

                        @if ($service->description)
                            <p class="mt-3 line-clamp-2 text-sm text-slate-600 dark:text-gray-400">
                                {{ $service->description }}
                            </p>
                        @endif

                        <div class="mt-auto flex items-end justify-between gap-3 pt-4 border-t border-slate-100 dark:border-gray-800">
                            <div>
                                <p class="text-lg font-bold text-[#FF5E14]">₱{{ number_format($service->price, 2) }}</p>
                                @if ($service->duration_minutes)
                                    <p class="text-[11px] text-slate-500 dark:text-gray-400">
                                        ~{{ $service->duration_minutes }} min
                                    </p>
                                @endif
                            </div>

                            @php
                                $post = \App\Models\Post::where('service_id', $service->id)
                                    ->where('status', 'published')
                                    ->whereNull('archived_at')
                                    ->first();
                            @endphp

                            @if ($post)
                                <a href="{{ route('customer.services.book', $post->id) }}" 
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#FF5E14] px-3.5 py-2 text-xs font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                                    Book
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @else
                                <span class="rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-500 dark:bg-gray-800 dark:text-gray-400">
                                    Not bookable
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $services->links() }}</div>
        @endif
    </div>
</div>