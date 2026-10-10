<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">Platform management</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Users</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Every account on the platform — owners, customers, and their activity.
            </p>
        </div>
    </div>

    @if (session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- Tabs --}}
    @php $counts = $this->counts; @endphp
    <div class="flex flex-wrap gap-2">
        @php
        $tabs = [
            ['key' => '',         'label' => 'All',       'count' => $counts['all']],
            ['key' => 'owner',    'label' => 'Owners',    'count' => $counts['owners']],
            ['key' => 'customer', 'label' => 'Customers', 'count' => $counts['customers']],
            ['key' => 'inactive', 'label' => 'Inactive',  'count' => $counts['inactive']],
        ];
        @endphp
        @foreach ($tabs as $tab)
        <button wire:click="$set('roleFilter', '{{ $tab['key'] }}')" type="button"
            class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                {{ $roleFilter === $tab['key']
                    ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10 dark:border-orange-500/40'
                    : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">
            {{ $tab['label'] }}
            <span class="rounded-full bg-slate-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-gray-300">
                {{ $tab['count'] }}
            </span>
        </button>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Search by name, email, or phone..."
                       class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
            </div>

            @if ($search || $roleFilter)
            <button wire:click="clearFilters" type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                Clear
            </button>
            @endif
        </div>
    </div>

    @php $users = $this->users; @endphp

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($users as $user)
            @php
                $roles = $user->getRoleNames();
                $isOwner = $roles->contains('owner');
                $isCustomer = $roles->contains('customer');
            @endphp
            <div wire:key="m-user-{{ $user->id }}"
                 class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white dark:bg-gray-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $user->email }}</p>
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            @if ($isOwner)
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Owner</span>
                            @endif
                            @if ($isCustomer)
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">Customer</span>
                            @endif
                            @if (! $user->is_active)
                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700 dark:bg-red-500/10 dark:text-red-300">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 dark:border-gray-800 pt-3 text-xs">
                    <div>
                        <p class="text-slate-500 dark:text-gray-400">Shops owned</p>
                        <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ $user->owned_tenants_count }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-gray-400">Orders placed</p>
                        <p class="mt-0.5 font-bold text-slate-900 dark:text-white">{{ $user->orders_count }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button wire:click="viewUser({{ $user->id }})" type="button"
                            class="flex-1 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200">
                        View
                    </button>
                    <button wire:click="confirmToggleActive({{ $user->id }})" type="button"
                            class="flex-1 rounded-lg border px-3 py-2 text-xs font-semibold {{ $user->is_active
                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-sm text-slate-500 dark:text-gray-400">No users found.</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">User</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Phone</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Roles</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Shops</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Orders</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($users as $user)
                        @php
                            $roles = $user->getRoleNames();
                            $isOwner = $roles->contains('owner');
                            $isCustomer = $roles->contains('customer');
                        @endphp
                        <tr wire:key="user-{{ $user->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-gray-700">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                                {{ $user->phone ?: '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if ($isOwner)
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Owner</span>
                                    @endif
                                    @if ($isCustomer)
                                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">Customer</span>
                                    @endif
                                    @if (! $isOwner && ! $isCustomer)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-gray-800 dark:text-gray-300">User</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $user->owned_tenants_count }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $user->orders_count }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-red-700 dark:bg-red-500/10 dark:text-red-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- View --}}
                                    <button wire:click="viewUser({{ $user->id }})" type="button" title="View"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    {{-- Toggle --}}
                                    <button wire:click="confirmToggleActive({{ $user->id }})" type="button"
                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg transition
                                                {{ $user->is_active
                                                    ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10'
                                                    : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' }}">
                                        @if ($user->is_active)
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Reset password --}}
                                    <button wire:click="sendPasswordReset({{ $user->id }})"
                                            wire:confirm="Send a password reset link to {{ $user->email }}?"
                                            type="button" title="Send password reset"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-500/10">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </button>

                                    {{-- Delete --}}
                                    <button wire:click="confirmDelete({{ $user->id }})" type="button" title="Delete"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->total() > 0)
            <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
                </p>
                <div>{{ $users->links() }}</div>
            </div>
        @endif
    </div>

    {{-- Mobile pagination --}}
    <div class="md:hidden">
        {{ $users->links() }}
    </div>

    {{-- Confirmation modal --}}
    @if ($confirmingUserId && $confirmingAction)
    @php
        $isDanger = in_array($confirmingAction, ['deactivate', 'delete']);
        $title = match ($confirmingAction) {
            'activate'   => 'Activate this user?',
            'deactivate' => 'Deactivate this user?',
            'delete'     => 'Delete this user permanently?',
            default      => 'Confirm action',
        };
        $message = match ($confirmingAction) {
            'activate'   => 'They will be able to log in again.',
            'deactivate' => 'They will be blocked from logging in. Their data stays intact.',
            'delete'     => 'Their account will be permanently removed. Refused if they own shops or are an admin.',
            default      => '',
        };
    @endphp
    <div x-data @keydown.escape.window="$wire.cancelAction()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $isDanger ? 'bg-red-100 dark:bg-red-500/20' : 'bg-emerald-100 dark:bg-emerald-500/20' }}">
                    <svg class="h-6 w-6 {{ $isDanger ? 'text-red-600' : 'text-emerald-600' }}"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $confirmingName }}</span> — {{ $message }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelAction" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="performAction" wire:loading.attr="disabled" wire:target="performAction" type="button"
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition disabled:opacity-60
                            {{ $isDanger ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                    <span wire:loading.remove wire:target="performAction">
                        {{ $confirmingAction === 'delete' ? 'Delete' : 'Confirm' }}
                    </span>
                    <span wire:loading wire:target="performAction">Working…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Detail modal --}}
    @if ($viewingUserId && $viewingUser)
    @php $u = $viewingUser; @endphp
    <div x-data @keydown.escape.window="$wire.closeUserModal()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
        <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                <div class="flex min-w-0 items-start gap-4">
                    @if ($u['avatar'])
                        <img src="{{ $u['avatar'] }}" alt=""
                             class="h-14 w-14 shrink-0 rounded-full border border-slate-200 object-cover" />
                    @else
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-900 text-lg font-bold text-white dark:bg-gray-700">
                            {{ strtoupper(substr($u['name'], 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">User detail</p>
                        <h2 class="mt-1 truncate text-2xl font-bold text-slate-900 dark:text-white">{{ $u['name'] }}</h2>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            @foreach ($u['roles'] as $role)
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $role }}
                                </span>
                            @endforeach
                            @if ($u['is_active'])
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700 dark:bg-red-500/10 dark:text-red-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <button wire:click="closeUserModal" type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto p-6 space-y-5">

                {{-- Stats grid --}}
                @if ($u['is_owner'])
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Shops</p>
                            <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ count($u['shops']) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Revenue</p>
                            <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">₱{{ number_format($u['total_revenue'], 0) }}</p>
                        </div>
                    </div>
                @elseif ($u['is_customer'])
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Total spent</p>
                            <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">₱{{ number_format($u['total_spent'], 2) }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Orders</p>
                            <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ count($u['recent_orders']) }}</p>
                        </div>
                    </div>
                @endif

                {{-- Contact info --}}
                <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                    <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Contact</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Email</dt>
                            <dd class="truncate font-medium text-slate-900 dark:text-white">
                                {{ $u['email'] }}
                                @if ($u['email_verified'])
                                    <span class="ml-1 text-emerald-600" title="Verified">✓</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Phone</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ $u['phone'] ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Address</dt>
                            <dd class="text-right font-medium text-slate-900 dark:text-white">{{ $u['address'] ?? '—' }}</dd>
                        </div>
                        @if ($u['birth_date'])
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500">Birth date</dt>
                                <dd class="font-medium text-slate-900 dark:text-white">{{ $u['birth_date'] }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Joined</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ $u['created_at'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Last login</dt>
                            <dd class="font-medium text-slate-900 dark:text-white">{{ $u['last_login_at'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Shops (owners) --}}
                @if (! empty($u['shops']))
                    <div>
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
                            Shops owned ({{ count($u['shops']) }})
                        </h3>
                        <div class="space-y-2">
                            @foreach ($u['shops'] as $shop)
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                                    @if ($shop['logo'])
                                        <img src="{{ $shop['logo'] }}" alt=""
                                             class="h-9 w-9 shrink-0 rounded-lg border border-slate-200 object-cover" />
                                    @else
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-gray-700">
                                            {{ strtoupper(substr($shop['name'], 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $shop['name'] }}</p>
                                        <p class="text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $shop['verification_status']) }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $shop['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $shop['is_active'] ? 'Live' : 'Offline' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Recent orders (customers) --}}
                @if (! empty($u['recent_orders']))
                    <div>
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
                            Recent orders
                        </h3>
                        <div class="space-y-2">
                            @foreach ($u['recent_orders'] as $order)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-mono text-xs text-slate-600 dark:text-gray-300">{{ $order['order_number'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400">{{ $order['created_at'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $order['badge']['bg'] }} {{ $order['badge']['text'] }}">
                                            {{ $order['status_label'] }}
                                        </span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-white">₱{{ number_format($order['total'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer actions --}}
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                <button wire:click="closeUserModal" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-700">
                    Close
                </button>

                <button wire:click="sendPasswordReset({{ $u['id'] }})"
                        wire:confirm="Send password reset link to {{ $u['email'] }}?"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Reset password
                </button>

                @if ($u['is_active'])
                    <button wire:click="confirmToggleActive({{ $u['id'] }})" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700">
                        Deactivate
                    </button>
                @else
                    <button wire:click="confirmToggleActive({{ $u['id'] }})" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        Activate
                    </button>
                @endif

                <button wire:click="confirmDelete({{ $u['id'] }})" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>
    @endif
</div>