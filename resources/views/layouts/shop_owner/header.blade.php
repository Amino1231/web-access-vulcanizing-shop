@php
    $user      = auth()->user();
    $tenant    = $user?->currentTenant();
    $verification = $tenant?->verification_status;

    if ($verification instanceof \App\Enums\VerificationStatus) {
        $verification = $verification->value;
    }

    $verificationBadge = match ($verification) {
        'approved'  => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'text' => 'text-emerald-700 dark:text-emerald-300', 'dot' => 'bg-emerald-500', 'label' => 'Verified'],
        'rejected'  => ['bg' => 'bg-red-50 dark:bg-red-500/10',         'text' => 'text-red-700 dark:text-red-300',         'dot' => 'bg-red-500',     'label' => 'Rejected'],
        'suspended' => ['bg' => 'bg-red-50 dark:bg-red-500/10',         'text' => 'text-red-700 dark:text-red-300',         'dot' => 'bg-red-500',     'label' => 'Suspended'],
        default     => ['bg' => 'bg-amber-50 dark:bg-amber-500/10',     'text' => 'text-amber-700 dark:text-amber-300',     'dot' => 'bg-amber-500',   'label' => 'Pending review'],
    };
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900/95">
    <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">

        {{-- LEFT --}}
        <div class="flex min-w-0 items-center gap-3">

            {{-- Hamburger (mobile/tablet only) --}}
            <button
                id="ownerSidebarToggle"
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 lg:hidden"
                aria-label="Open sidebar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            {{-- Shop name + badge (desktop/tablet) --}}
            <div class="hidden min-w-0 items-center gap-2.5 sm:flex">
                @if ($tenant?->logo)
                    <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}"
                         class="h-8 w-8 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-gray-700" />
                @else
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-gray-700">
                        {{ strtoupper(substr($tenant->name ?? 'SH', 0, 2)) }}
                    </div>
                @endif

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold leading-tight text-slate-900 dark:text-white">
                        {{ $tenant->name ?? 'My shop' }}
                    </p>
                    <p class="text-[11px] leading-tight text-slate-500 dark:text-gray-400">Owner portal</p>
                </div>

                <span class="hidden shrink-0 items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider md:inline-flex {{ $verificationBadge['bg'] }} {{ $verificationBadge['text'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $verificationBadge['dot'] }}"></span>
                    {{ $verificationBadge['label'] }}
                </span>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="flex items-center gap-2">

            {{-- Dark mode toggle --}}
            <button
                id="darkModeToggle"
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                aria-label="Toggle dark mode"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.219 4.219l1.061 1.061M18.72 18.72l1.06 1.06M3 12h1.5M19.5 12H21M4.219 19.781l1.061-1.061M18.72 5.28l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
            </button>

            {{-- Notifications --}}
            <livewire:shared.notification-bell />

            {{-- Account menu --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" type="button"
                        class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white py-1.5 pl-1.5 pr-2.5 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-gray-600">
                        {{ strtoupper(substr($user->name ?? 'SO', 0, 2)) }}
                    </div>
                    <div class="hidden text-left sm:block">
                        <p class="text-xs font-semibold leading-tight text-slate-900 dark:text-white">{{ $user->name ?? 'Shop owner' }}</p>
                        <p class="text-[10px] leading-tight text-slate-500 dark:text-gray-400">Owner account</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </button>

                <div x-show="open" x-cloak x-transition.origin.top.right
                     class="absolute right-0 z-50 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <a href="{{ route('owner.profile') }}"
                       class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-gray-200 dark:hover:bg-gray-700">
                        Profile
                    </a>
                    <a href="{{ route('owner.update_profile') }}"
                       class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-gray-200 dark:hover:bg-gray-700">
                        Update profile
                    </a>
                    <div class="my-1 border-t border-slate-100 dark:border-gray-700"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
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