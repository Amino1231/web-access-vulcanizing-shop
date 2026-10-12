<div id="adminSidebar" class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full border-r border-slate-200 bg-white shadow-xl transition-transform duration-200 dark:border-slate-700 dark:bg-slate-900 lg:static lg:z-30 lg:translate-x-0 lg:shadow-none">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white dark:bg-slate-100 dark:text-slate-900">VS</div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Admin</p>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Control panel</h2>
                </div>
            </div>

            <button
                id="adminSidebarClose"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700"
                aria-label="Close sidebar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-5">

            {{-- Overview --}}
            <div class="mb-2 px-2">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Overview</p>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.dashboard')
                       ? 'bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-slate-100'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition
                    {{ request()->routeIs('admin.dashboard')
                        ? 'bg-[#FF5E14] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600 dark:group-hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5L12 4l9 7.5V20a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1v-8.5z" />
                    </svg>
                </span>
                Dashboard
            </a>

            {{-- Management --}}
            <div class="my-4 border-t border-slate-200 dark:border-slate-700"></div>
            <div class="mb-2 px-2">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Management</p>
            </div>

            {{-- Users --}}
            <a href="{{ route('admin.users') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.users')
                       ? 'bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-slate-100'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition
                    {{ request()->routeIs('admin.users')
                        ? 'bg-[#FF5E14] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600 dark:group-hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </span>
                Users
            </a>

            {{-- Reports --}}
            <a href="{{ route('admin.reports') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.reports')
                       ? 'bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-slate-100'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition
                    {{ request()->routeIs('admin.reports')
                        ? 'bg-[#FF5E14] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600 dark:group-hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                Reports
            </a>

            {{-- Shop approvals --}}
            <a href="{{ route('admin.shop_approval') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.shop_approval')
                       ? 'bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-slate-100'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition
                    {{ request()->routeIs('admin.shop_approval')
                        ? 'bg-[#FF5E14] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600 dark:group-hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 2M5 22h14" />
                    </svg>
                </span>
                Shop approvals
            </a>

            {{-- All shops --}}
            <a href="{{ route('admin.shops.index') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.shops.*')
                       ? 'bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-slate-100'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition
                    {{ request()->routeIs('admin.shops.*')
                        ? 'bg-[#FF5E14] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600 dark:group-hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01" />
                    </svg>
                </span>
                All shops
            </a>

            {{-- Orders --}}
            <a href="{{ route('admin.orders') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.orders')
                       ? 'bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-slate-100'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition
                    {{ request()->routeIs('admin.orders')
                        ? 'bg-[#FF5E14] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600 dark:group-hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </span>
                Orders
            </a>

            {{-- Reviews --}}
            <a href="{{ route('admin.reviews') }}"
               class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.reviews')
                       ? 'bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-slate-100'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg transition
                    {{ request()->routeIs('admin.reviews')
                        ? 'bg-[#FF5E14] text-white'
                        : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200 group-hover:text-slate-900 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-slate-600 dark:group-hover:text-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </span>
                Reviews
            </a>
        </nav>

        <div class="border-t border-slate-200 p-4 dark:border-slate-700">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
                <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                    <span>Platform</span>
                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">Live</span>
                </div>
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">24 active tenants</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Updated just now</p>
            </div>
        </div>
    </div>
</div>

<div id="adminSidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden dark:bg-slate-950/60"></div>