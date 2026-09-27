@php
    $tenant = auth()->user()?->tenant;

    $navGroups = [
        [
            'label' => 'Overview',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'owner.dashboard', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5L12 4l9 7.5V20a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1v-8.5z" /></svg>'],
               
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                ['label' => 'Products', 'route' => 'owner.products', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5L12 3l8 4.5v9L12 21l-8-4.5v-9zm8 4.5l8-4.5M12 12v9" /></svg>'],
                ['label' => 'Create product', 'route' => 'owner.product_create', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" /></svg>'],
                ['label' => 'Categories', 'route' => 'owner.categories', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 016.5 5h4.9a2.5 2.5 0 011.77.73L14.5 7H17.5A2.5 2.5 0 0120 9.5v7A2.5 2.5 0 0117.5 19h-11A2.5 2.5 0 014 16.5v-9z" /></svg>'],
                ['label' => 'Inventory', 'route' => 'owner.inventory', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L12 3l9 4.5-9 4.5-9-4.5zm9 4.5v9m-9-4.5l9 4.5 9-4.5" /></svg>'],
            ],
        ],
        [
            'label' => 'Operations',
            'items' => [
                ['label' => 'Orders', 'route' => 'owner.order_management', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M7 12h10M8 17h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" /></svg>'],
            ],
        ],
    ];
@endphp

<aside id="ownerSidebar" class="sticky inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-slate-200 bg-white transition-transform duration-200 dark:border-gray-800 dark:bg-gray-900 lg:static lg:translate-x-0">
    <div class="flex h-full flex-col">

        {{-- Dynamic tenant branding --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 dark:border-gray-800">
            <div class="flex min-w-0 items-center gap-3">
                @if ($tenant?->logo)
                    <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}"
                         class="h-10 w-10 shrink-0 rounded-lg object-cover border border-slate-200 dark:border-gray-700" />
                @else
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-900 dark:bg-gray-700 text-sm font-bold text-white">
                        {{ strtoupper(substr($tenant->name ?? 'SH', 0, 2)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $tenant->name ?? 'My shop' }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400">Owner portal</p>
                </div>
            </div>

            <button
                id="ownerSidebarClose"
                type="button"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 lg:hidden"
                aria-label="Close sidebar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
            @foreach ($navGroups as $group)
                <div>
                    <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-gray-500">{{ $group['label'] }}</p>

                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php $isActive = request()->routeIs($item['route']); @endphp

                            <a href="{{ route($item['route']) }}"
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

        {{-- Dynamic store status --}}
        <div class="border-t border-slate-200 p-4 dark:border-gray-800">
            @php
                $isLive = (bool) ($tenant->is_active ?? false);
            @endphp
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

<div id="ownerSidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden"></div>

<script>
    (function () {
        const sidebar = document.getElementById('ownerSidebar');
        const overlay = document.getElementById('ownerSidebarOverlay');
        const openBtn = document.getElementById('ownerSidebarToggle');
        const closeBtn = document.getElementById('ownerSidebarClose');

        function openSidebar() {
            sidebar?.classList.remove('-translate-x-full');
            overlay?.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar?.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
        }

        openBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);
    })();
</script>