@php
    $tenant = auth()->user()?->tenant;
    $verification = $tenant->verification_status ?? 'pending';
    $verificationBadge = match ($verification) {
        'approved'  => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'text' => 'text-emerald-700 dark:text-emerald-300', 'dot' => 'bg-emerald-500', 'label' => 'Verified'],
        'rejected'  => ['bg' => 'bg-red-50 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-300', 'dot' => 'bg-red-500', 'label' => 'Rejected'],
        'suspended' => ['bg' => 'bg-red-50 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-300', 'dot' => 'bg-red-500', 'label' => 'Suspended'],
        default     => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-700 dark:text-amber-300', 'dot' => 'bg-amber-500', 'label' => 'Pending review'],
    };
@endphp

<header class="sticky top-0 z-20 border-b border-slate-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

        <div class="flex items-center gap-3">
            <button
                id="ownerSidebarToggle"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 lg:hidden"
                aria-label="Toggle sidebar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

        </div>

        <div class="flex items-center gap-2">

            {{-- Dark mode toggle --}}
            <button
                id="darkModeToggle"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                aria-label="Toggle dark mode"
            >
                <svg id="darkModeIconSun" xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.219 4.219l1.061 1.061M18.72 18.72l1.06 1.06M3 12h1.5M19.5 12H21M4.219 19.781l1.061-1.061M18.72 5.28l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
                <svg id="darkModeIconMoon" xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
            </button>

            {{-- Account menu --}}
            <details class="group relative">
                <summary class="flex cursor-pointer list-none items-center gap-2.5 rounded-lg border border-slate-200 bg-white px-2 py-1.5 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 dark:bg-gray-600 text-xs font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'SO', 0, 2)) }}
                    </div>
                    <div class="hidden text-left sm:block">
                        <p class="text-sm font-semibold leading-tight text-slate-900 dark:text-white">{{ auth()->user()->name ?? 'Shop owner' }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-gray-400">Owner account</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4 text-slate-400 transition group-open:rotate-180 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </summary>

                <div class="absolute right-0 z-30 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <a href="{{ route('owner.profile') }}" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-gray-200 dark:hover:bg-gray-700">Profile</a>
                    <a href="{{ route('owner.update_profile') }}" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-gray-200 dark:hover:bg-gray-700">Update profile</a>
                    <div class="my-1 border-t border-slate-100 dark:border-gray-700"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Log out</button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>

<script>
    (function () {
        const toggle = document.getElementById('darkModeToggle');
        if (!toggle) return;

        toggle.addEventListener('click', function () {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    })();
</script>