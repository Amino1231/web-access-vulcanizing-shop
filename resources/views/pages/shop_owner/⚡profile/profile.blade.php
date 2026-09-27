

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Profile</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Your account and shop details.</p>
        </div>

        <a href="{{ route('owner.update_profile') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
            Edit profile
        </a>
    </div>

    @php $user = Auth::user(); $tenant = $this->tenant; @endphp

    {{-- Account card --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-gray-400">Account</h2>

        <div class="flex items-center gap-4">
            @if ($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                     class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-gray-700" />
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-900 dark:bg-gray-700 text-lg font-bold text-white">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                </div>
            @endif
            <div>
                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $user->name }}</p>
                <p class="text-sm text-slate-500 dark:text-gray-400">{{ $user->email }}</p>
            </div>
        </div>

        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Phone</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $user->phone ?: 'Not set' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Address</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $user->address ?: 'Not set' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Gender</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $user->gender ?? 'Not set')) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Birth date</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $user->birth_date?->format('M d, Y') ?? 'Not set' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Shop card --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none">
        <h2 class="mb-4 text-sm font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-gray-400">Shop</h2>

        @if (! $tenant)
            <p class="text-sm text-slate-500 dark:text-gray-400">No business profile has been set up yet.</p>
        @else
            <div class="flex items-center gap-4">
                @if ($tenant->logo)
                    <img src="{{ asset('storage/' . $tenant->logo) }}" alt="{{ $tenant->name }}"
                         class="h-16 w-16 rounded-xl object-cover border border-slate-200 dark:border-gray-700" />
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-900 dark:bg-gray-700 text-lg font-bold text-white">
                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $tenant->name }}</p>
                    <p class="text-sm text-slate-500 dark:text-gray-400">{{ $tenant->email ?: 'No email set' }}</p>
                </div>
            </div>

            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Phone</dt>
                    <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $tenant->phone ?: 'Not set' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Address</dt>
                    <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $tenant->address ?: 'Not set' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Verification status</dt>
                    <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ ucfirst($tenant->verification_status) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Store status</dt>
                    <dd class="mt-1 text-sm text-slate-900 dark:text-white">{{ $tenant->is_active ? 'Active' : 'Inactive' }}</dd>
                </div>
            </dl>
        @endif
    </div>
</div>