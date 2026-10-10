<header x-data="{ mobile: false }"
        class="sticky top-0 z-[1000] border-b border-slate-200 dark:border-gray-800 bg-white/95 dark:bg-gray-950/95 backdrop-blur-sm">

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">

            {{-- Logo --}}
            <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2 shrink-0">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF5E14] shadow-lg shadow-orange-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-width="2.5" />
                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                        <path stroke-linecap="round" d="M12 3v3m0 12v3M3 12h3m12 0h3" />
                    </svg>
                </div>
                <div class="hidden sm:block">
                    <span class="text-lg font-bold text-slate-900 dark:text-white">Vulcan</span>
                    <span class="text-lg font-light text-[#FF5E14]">Pro</span>
                </div>
            </a>

            {{-- Search (desktop only) --}}
            <div class="hidden flex-1 lg:block max-w-xl mx-4">
                <form action="{{ route('customer.new_arrivals') }}" method="GET" class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search products, shops, or SKUs..."
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-gray-500 outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                </form>
            </div>

            {{-- Desktop nav (lg+) --}}
            <nav class="hidden lg:flex items-center gap-1">
                <a href="{{ route('customer.dashboard') }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 dark:text-gray-300 transition hover:bg-slate-100 dark:hover:bg-gray-800 hover:text-slate-900 dark:hover:text-white">
                    Marketplace
                </a>
                <a href="{{ route('customer.new_arrivals', ['sort' => 'latest']) }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 dark:text-gray-300 transition hover:bg-slate-100 dark:hover:bg-gray-800 hover:text-slate-900 dark:hover:text-white">
                    New arrivals
                </a>
                <a href="{{ route('customer.services.index') }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 dark:text-gray-300 transition hover:bg-slate-100 dark:hover:bg-gray-800">
                    Services
                </a>
                <a href="{{ route('customer.map') }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 dark:text-gray-300 transition hover:bg-slate-100 dark:hover:bg-gray-800">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Map
                    </span>
                </a>
            </nav>

            {{-- Right actions --}}
            <div class="flex items-center gap-2">

                {{-- Theme toggle — always visible --}}
                <button type="button" onclick="toggleTheme()" title="Toggle theme"
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 transition hover:bg-slate-200 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                    <svg class="hidden h-5 w-5 dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    </svg>
                </button>

                {{-- Everything below is desktop-only (lg+) --}}
                <div class="hidden lg:flex items-center gap-2">
                    @auth
                        {{-- Notifications --}}
                        <livewire:shared.notification-bell />

                        {{-- Cart --}}
                        <a href="{{ route('customer.checkout') }}" title="Cart"
                           class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 transition hover:bg-slate-200 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            @php $cartCount = count(session('cart', [])); @endphp
                            @if ($cartCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#FF5E14] px-1 text-[10px] font-bold text-white">
                                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                                </span>
                            @endif
                        </a>

                        {{-- User dropdown --}}
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" type="button"
                                    class="flex items-center gap-2 rounded-xl bg-slate-100 dark:bg-gray-800 pl-1.5 pr-3 py-1.5 text-sm font-medium text-slate-700 dark:text-gray-200 transition hover:bg-slate-200 dark:hover:bg-gray-700">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#FF5E14] text-xs font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </span>
                                <span class="max-w-[100px] truncate">{{ auth()->user()->name }}</span>
                                <svg class="h-3 w-3 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak x-transition.origin.top.right
                                 class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl py-1.5 z-50">

                                <div class="px-4 py-3 border-b border-slate-100 dark:border-gray-800">
                                    <p class="text-xs font-medium text-slate-500 dark:text-gray-400">Signed in as</p>
                                    <p class="mt-0.5 truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->email }}</p>
                                </div>

                                <a href="{{ route('customer.profile') }}"
                                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    My profile
                                </a>

                                <a href="{{ route('customer.orders') }}"
                                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    My orders
                                </a>

                                <a href="{{ route('customer.appointments') }}"
                                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    My appointments
                                </a>

                                @php
                                    $user = auth()->user();
                                    $dashboardRoute = $user->hasRole('admin')
                                        ? 'admin.dashboard'
                                        : ($user->hasRole('owner')
                                            ? 'owner.dashboard'
                                            : 'customer.dashboard');
                                @endphp
                                @if (\Illuminate\Support\Facades\Route::has($dashboardRoute))
                                    <a href="{{ route($dashboardRoute) }}"
                                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-800">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Dashboard
                                    </a>
                                @endif

                                <div class="border-t border-slate-100 dark:border-gray-800 mt-1.5 pt-1.5">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="flex w-full items-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-100 dark:hover:bg-gray-800">
                            Sign in
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                            Sign up
                        </a>
                    @endauth
                </div>

                {{-- Mobile/tablet hamburger --}}
                <button type="button" @click="mobile = !mobile"
                        class="lg:hidden flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300">
                    <svg x-show="!mobile" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobile" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile/tablet menu — everything in here --}}
        <div x-show="mobile" x-cloak x-transition
             class="lg:hidden border-t border-slate-200 dark:border-gray-800 py-3">
            <nav class="flex flex-col gap-1">

                {{-- Search (in menu) --}}
                <form action="{{ route('customer.new_arrivals') }}" method="GET" class="relative mb-2">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search products..."
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-gray-500 outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                </form>

                {{-- Main nav --}}
                <a href="{{ route('customer.dashboard') }}"
                   class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                    Marketplace
                </a>
                <a href="{{ route('customer.new_arrivals', ['sort' => 'latest']) }}"
                   class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                    New arrivals
                </a>
                <a href="{{ route('customer.services.index') }}"
                   class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                    Services
                </a>
                <a href="{{ route('customer.map') }}"
                   class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                    Map
                </a>

                @auth
                    {{-- Divider --}}
                    <div class="my-1.5 border-t border-slate-200 dark:border-gray-800"></div>

                    <a href="{{ route('customer.profile') }}"
                       class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                        My profile
                    </a>
                    <a href="{{ route('customer.orders') }}"
                       class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                        My orders
                    </a>
                    <a href="{{ route('customer.appointments') }}"
                       class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                        My appointments
                    </a>
                    <a href="{{ route('customer.checkout') }}"
                       class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                        <span>Cart</span>
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if ($cartCount > 0)
                            <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#FF5E14] px-1 text-[10px] font-bold text-white">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </a>

                    @php
                        $user = auth()->user();
                        $dashboardRoute = $user->hasRole('admin')
                            ? 'admin.dashboard'
                            : ($user->hasRole('owner')
                                ? 'owner.dashboard'
                                : 'customer.dashboard');
                    @endphp
                    @if (\Illuminate\Support\Facades\Route::has($dashboardRoute))
                        <a href="{{ route($dashboardRoute) }}"
                           class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800">
                            Dashboard
                        </a>
                    @endif

                    {{-- Sign out --}}
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2 rounded-lg bg-red-50 dark:bg-red-500/10 px-3 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Sign out
                        </button>
                    </form>
                @else
                    {{-- Divider --}}
                    <div class="my-1.5 border-t border-slate-200 dark:border-gray-800"></div>

                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}"
                           class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-center text-sm font-semibold text-slate-700 dark:text-gray-200">
                            Sign in
                        </a>
                        <a href="{{ route('register') }}"
                           class="rounded-xl bg-[#FF5E14] px-3 py-2.5 text-center text-sm font-semibold text-white shadow-sm shadow-orange-500/30">
                            Sign up
                        </a>
                    </div>
                @endauth
            </nav>
        </div>
    </div>
</header>

<style>
    [x-cloak] { display: none !important; }
</style>