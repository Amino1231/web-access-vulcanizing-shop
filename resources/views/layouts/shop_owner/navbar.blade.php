@php
    $user     = auth()->user();
    $tenant   = $user?->currentTenant();
    $allShops = $user?->allTenants() ?? collect();

    $navGroups = [
        [
            'label' => 'Overview',
            'items' => [
                ['label' => 'Dashboard',       'route' => 'owner.dashboard',      'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5L12 4l9 7.5V20a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1v-8.5z" /></svg>'],
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                ['label' => 'Products',   'route' => 'owner.products',   'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5L12 3l8 4.5v9L12 21l-8-4.5v-9zm8 4.5l8-4.5M12 12v9" /></svg>'],
                ['label' => 'Categories', 'route' => 'owner.categories', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 016.5 5h4.9a2.5 2.5 0 011.77.73L14.5 7H17.5A2.5 2.5 0 0120 9.5v7A2.5 2.5 0 0117.5 19h-11A2.5 2.5 0 014 16.5v-9z" /></svg>'],
                ['label' => 'Inventory',  'route' => 'owner.inventory',  'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L12 3l9 4.5-9 4.5-9-4.5zm9 4.5v9m-9-4.5l9 4.5 9-4.5" /></svg>'],
                ['label' => 'Brands', 'route' => 'owner.brands', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z" /></svg>'],
            ],
        ],
        [
            'label' => 'Operations',
            'items' => [
                ['label' => 'Orders',       'route' => 'owner.order_management', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M7 12h10M8 17h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg>'],
                ['label' => 'Appointments', 'route' => 'owner.appointments',     'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3M16 2v3M3.5 8.5h17M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 13l2 2 4-4" /></svg>'],
                ['label' => 'Roadside requests', 'route' => 'owner.roadside', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>'],
                ['label' => 'Employees', 'route' => 'owner.employees', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
                ['label' => 'Reports', 'route' => 'owner.reports', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>'],    
                ['label' => 'Shop settings', 'route' => 'owner.shop.settings', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'],    
            ],
        ],

        
    ];
@endphp

<aside id="ownerSidebar"
       class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full border-r border-slate-200 bg-white transition-transform duration-200 ease-out dark:border-gray-800 dark:bg-gray-900
              lg:static lg:z-30 lg:translate-x-0">
    <div class="flex h-full flex-col">

        {{-- Shop switcher --}}
        <div class="relative border-b border-slate-200 px-3 py-3 dark:border-gray-800">

            {{-- Mobile close button --}}
            <button
                id="ownerSidebarClose"
                type="button"
                class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 lg:hidden"
                aria-label="Close sidebar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Shop dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" type="button"
                        class="flex w-full items-center justify-between gap-3 rounded-xl bg-slate-50 dark:bg-gray-800 px-3 py-2.5 pr-12 text-left transition hover:bg-slate-100 dark:hover:bg-gray-700 lg:pr-3">
                    <div class="flex min-w-0 items-center gap-3">
                        @if ($tenant?->logo)
                            <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}"
                                 class="h-10 w-10 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-gray-700" />
                        @else
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white dark:bg-gray-700">
                                {{ strtoupper(substr($tenant->name ?? 'SH', 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $tenant->name ?? 'No shop yet' }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-gray-400">Owner portal</p>
                        </div>
                    </div>
                    <svg class="hidden h-4 w-4 shrink-0 text-slate-500 transition lg:block" :class="open && 'rotate-180'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                     class="absolute left-0 right-0 z-50 mt-2 max-h-72 overflow-y-auto rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl dark:border-gray-700 dark:bg-gray-900">

                    <p class="px-3 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-gray-400">
                        Your shops ({{ $allShops->count() }})
                    </p>

                    @forelse ($allShops as $shop)
                        @php
                            $isCurrent = $tenant?->id === $shop->id;
                            $status = $shop->verification_status instanceof \App\Enums\VerificationStatus
                                ? $shop->verification_status->value
                                : $shop->verification_status;
                            $statusColor = match ($status) {
                                'approved'        => 'text-emerald-600 dark:text-emerald-400',
                                'rejected'        => 'text-red-600 dark:text-red-400',
                                'under_review'    => 'text-blue-600 dark:text-blue-400',
                                'needs_more_info' => 'text-purple-600 dark:text-purple-400',
                                default           => 'text-amber-600 dark:text-amber-400',
                            };
                        @endphp
                        <a href="{{ route('owner.switch_shop', $shop->id) }}"
                           class="flex items-center gap-2 px-3 py-2 text-sm transition hover:bg-slate-50 dark:hover:bg-gray-800 {{ $isCurrent ? 'bg-orange-50 dark:bg-orange-500/10' : '' }}">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-[10px] font-bold {{ $isCurrent ? 'bg-[#FF5E14] text-white' : 'bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                {{ strtoupper(substr($shop->name, 0, 2)) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-medium text-slate-900 dark:text-white">{{ $shop->name }}</span>
                                <span class="block truncate text-[10px] font-semibold uppercase tracking-wide {{ $statusColor }}">
                                    {{ str_replace('_', ' ', $status) }}
                                </span>
                            </span>
                            @if ($isCurrent)
                                <svg class="h-4 w-4 shrink-0 text-[#FF5E14]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </a>
                    @empty
                        <p class="px-3 py-3 text-xs text-slate-500 dark:text-gray-400">
                            No shops yet. Create one below.
                        </p>
                    @endforelse

                    <div class="mt-1.5 border-t border-slate-100 pt-1.5 dark:border-gray-800">
                        <a href="{{ route('owner.business_create') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-2 text-sm font-semibold text-[#FF5E14] transition hover:bg-orange-50 dark:hover:bg-orange-500/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                            Create new shop
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
            @foreach ($navGroups as $group)
                <div>
                    <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-gray-500">
                        {{ $group['label'] }}
                    </p>

                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php $isActive = request()->routeIs($item['route']); @endphp

                            <a href="{{ route($item['route']) }}" wire:navigate
                               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                                   {{ $isActive
                                       ? 'bg-slate-100 text-slate-900 dark:bg-gray-800 dark:text-white'
                                       : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                                <span class="flex h-7 w-7 items-center justify-center rounded-md
                                    {{ $isActive
                                        ? 'bg-[#FF5E14] text-white'
                                        : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200 dark:bg-gray-800 dark:text-gray-400 dark:group-hover:bg-gray-700' }}">
                                    {!! $item['icon'] !!}
                                </span>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        {{-- Store status --}}
        <div class="border-t border-slate-200 p-4 dark:border-gray-800">
            @php $isLive = (bool) ($tenant->is_active ?? false); @endphp

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 dark:border-gray-800 dark:bg-gray-800/50">
                <div class="mb-1.5 flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-gray-400">
                    <span>Store status</span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isLive ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $isLive ? 'Live' : 'Offline' }}
                    </span>
                </div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ $isLive ? 'Business is active' : 'Business is inactive' }}
                </p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400">
                    {{ $tenant?->address ?? 'No address on file' }}
                </p>
            </div>
        </div>
    </div>
</aside>

{{-- Overlay for mobile --}}
<div id="ownerSidebarOverlay"
     class="fixed inset-0 z-40 hidden bg-slate-950/50 backdrop-blur-sm lg:hidden"></div>

<style>
    [x-cloak] { display: none !important; }
</style>