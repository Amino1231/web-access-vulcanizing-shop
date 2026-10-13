<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Platform
                management</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Approved shops</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Approved shops and their verification documents. Use the approval queue to review new submissions.
            </p>
        </div>

        <a href="{{ route('admin.shop_approval') }}" wire:navigate
            class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Approval queue
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
    <div
        class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div
        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- Summary strip --}}
    @php $counts = $this->counts; @endphp
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Approved shops on platform</p>
                    <p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">{{ number_format($counts['all']) }}</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-gray-400">
                Every shop listed here has passed verification and can accept customers.
            </p>
        </div>
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
                    placeholder="Search approved shop name, email, owner..."
                    class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
            </div>

            <select wire:model.live="activeFilter"
                class="w-full sm:w-40 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                <option value="">Any status</option>
                <option value="active">Active only</option>
                <option value="inactive">Inactive only</option>
            </select>

            @if ($search || $activeFilter)
            <button wire:click="clearFilters" type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                Clear
            </button>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div
        class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Shop</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Owner</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Contact</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Documents</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Active</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($this->shops as $shop)
                    @php
                    // Count attachments for the documents column
                    $attachmentCount = is_array($shop->attachment) ? count($shop->attachment) : 0;
                    @endphp
                    <tr wire:key="shop-row-{{ $shop->id }}"
                        class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($shop->logo)
                                <img src="{{ asset('storage/' . $shop->logo) }}" alt="{{ $shop->name }}"
                                    class="h-9 w-9 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-gray-700" />
                                @else
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-gray-700">
                                    {{ strtoupper(substr($shop->name, 0, 2)) }}
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{
                                        $shop->name }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $shop->address ?:
                                        'No address' }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $shop->user?->name
                                ?? '—' }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $shop->user?->email ?? ''
                                }}</p>
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-gray-400">
                            {{ $shop->phone ?: '—' }}
                        </td>

                        {{-- Documents column --}}
                        <td class="px-4 py-3">
                            @if ($attachmentCount > 0)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ $attachmentCount }} {{ \Illuminate\Support\Str::plural('doc', $attachmentCount) }}
                            </span>
                            @else
                            <span class="text-xs text-slate-400 dark:text-gray-500">No documents</span>
                            @endif
                        </td>

                        {{-- Active status --}}
                        <td class="px-4 py-3">
                            @if ($shop->is_active)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Active
                            </span>
                            @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-gray-800 dark:text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Inactive
                            </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">

                                {{-- Toggle active --}}
                                <button wire:click="confirmToggleActive({{ $shop->id }})" type="button"
                                    title="{{ $shop->is_active ? 'Deactivate' : 'Activate' }}"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg transition
                                                {{ $shop->is_active
                                                    ? 'text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700'
                                                    : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' }}">
                                    @if ($shop->is_active)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @else
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @endif
                                </button>

                                {{-- Suspend --}}
                                <button wire:click="confirmSuspend({{ $shop->id }})" type="button" title="Suspend shop"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 transition hover:bg-amber-50 dark:hover:bg-amber-500/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                </button>

                                {{-- View --}}
                                <button wire:click="viewTenant({{ $shop->id }})" type="button" title="View details"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01" />
                            </svg>
                            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">No approved shops</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                                Shops approved through the queue will appear here.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->shops->total() > 0)
        <div
            class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500 dark:text-gray-400">
                Showing {{ $this->shops->firstItem() }}–{{ $this->shops->lastItem() }} of {{ $this->shops->total() }}
                approved shops
            </p>
            <div>{{ $this->shops->links() }}</div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- CONFIRMATION MODAL --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($confirmingActionId && $confirmingAction)
    @php
    $isDanger = in_array($confirmingAction, ['suspend', 'deactivate']);
    $title = match ($confirmingAction) {
    'activate' => 'Activate this shop?',
    'deactivate' => 'Deactivate this shop?',
    'suspend' => 'Suspend this shop?',
    default => 'Confirm action',
    };
    $message = match ($confirmingAction) {
    'activate' => 'The shop will appear in customer searches and can accept orders again.',
    'deactivate' => 'The shop will be hidden from customers and cannot accept new orders.',
    'suspend' => 'The shop will be marked as suspended. The owner will need to contact support to appeal.',
    default => '',
    };
    @endphp
    <div x-data @keydown.escape.window="$wire.cancelAction()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
        <div
            class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $isDanger ? 'bg-red-100 dark:bg-red-500/20' : 'bg-emerald-100 dark:bg-emerald-500/20' }}">
                    <svg class="h-6 w-6 {{ $isDanger ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $confirmingName }}</span> {{
                        $message }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelAction" type="button"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="performAction" wire:loading.attr="disabled" wire:target="performAction"
                    type="button"
                    class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition disabled:opacity-60
                                {{ $isDanger ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                    <span wire:loading.remove wire:target="performAction">Confirm</span>
                    <span wire:loading wire:target="performAction">Working…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TENANT DETAIL MODAL --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($viewingTenantId && $viewingTenant)
    @php
    $t = $viewingTenant;

    $modalStatus = is_object($t['verification_status'])
    ? $t['verification_status']->value
    : $t['verification_status'];

    $modalStatusBadge = match ($modalStatus) {
    'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
    'rejected' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
    'suspended' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
    'under_review' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
    'needs_more_info' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300',
    default => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
    };
    @endphp

    <div x-data @keydown.escape.window="$wire.closeTenantModal()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm"
        wire:key="tenant-modal-{{ $t['id'] }}">

        <div
            class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                <div class="flex min-w-0 items-start gap-4">
                    @if ($t['logo'])
                    <img src="{{ $t['logo'] }}" alt="{{ $t['name'] }}"
                        class="h-14 w-14 shrink-0 rounded-xl border border-slate-200 object-cover dark:border-gray-700" />
                    @else
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-lg font-bold text-white dark:bg-gray-700">
                        {{ strtoupper(substr($t['name'], 0, 2)) }}
                    </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                            Approved shop</p>
                        <h2 class="mt-1 truncate text-2xl font-bold text-slate-900 dark:text-white">{{ $t['name'] }}
                        </h2>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $modalStatusBadge }}">
                                {{ str_replace('_', ' ', $modalStatus) }}
                            </span>
                            @if ($t['is_active'])
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Live
                            </span>
                            @else
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-gray-800 dark:text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Offline
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <button wire:click="closeTenantModal" type="button"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 dark:text-gray-400 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto p-6">

                {{-- Revenue Flow KPIs --}}
                <div class="mb-6">
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                        Revenue flow · Customer → Shop → Platform
                    </p>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Gross Sales</p>
                            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                ₱{{ number_format($t['stats']['gross_revenue'] ?? 0, 2) }}
                            </p>
                            <p class="mt-0.5 text-[9px] uppercase tracking-wider text-slate-400">Customer paid</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Rate</p>
                            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                                {{ number_format($t['stats']['commission_rate'] ?? 0, 2) }}%
                            </p>
                            <p class="mt-0.5 text-[9px] uppercase tracking-wider text-slate-400">Platform cut</p>
                        </div>

                        <div class="rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">→ To Platform</p>
                            <p class="mt-1 text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                ₱{{ number_format($t['stats']['platform_earnings'] ?? 0, 2) }}
                            </p>
                            <p class="mt-0.5 text-[9px] uppercase tracking-wider text-emerald-600/70 dark:text-emerald-400/70">Admin earns</p>
                        </div>

                        <div class="rounded-xl border border-blue-200 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">→ To Shop</p>
                            <p class="mt-1 text-lg font-bold text-blue-700 dark:text-blue-300">
                                ₱{{ number_format($t['stats']['shop_net_revenue'] ?? 0, 2) }}
                            </p>
                            <p class="mt-0.5 text-[9px] uppercase tracking-wider text-blue-600/70 dark:text-blue-400/70">Owner receives</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Orders</p>
                            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $t['stats']['orders'] }}</p>
                            @if (($t['stats']['orders_pending'] ?? 0) > 0)
                                <p class="mt-0.5 text-[9px] font-semibold text-amber-600">{{ $t['stats']['orders_pending'] }} pending</p>
                            @else
                                <p class="mt-0.5 text-[9px] uppercase tracking-wider text-slate-400">No pending</p>
                            @endif
                        </div>

                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Products</p>
                            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $t['stats']['products'] }}</p>
                            <p class="mt-0.5 text-[9px] uppercase tracking-wider text-slate-400">{{ $t['stats']['services'] }} services</p>
                        </div>
                    </div>
                </div>

                {{-- Documents -- highlighted because that's a focus of this page --}}
                @if (! empty($t['attachments']))
                <div class="mb-6 rounded-xl border-2 border-blue-200 dark:border-blue-500/40 bg-blue-50/50 dark:bg-blue-500/5 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20">
                                <svg class="h-4.5 w-4.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">
                                    Verification documents
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-300">
                                    {{ count($t['attachments']) }} {{ \Illuminate\Support\Str::plural('file', count($t['attachments'])) }} on record
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach ($t['attachments'] as $file)
                        <a href="{{ $file['url'] }}" target="_blank"
                            class="flex items-center gap-3 rounded-lg border border-blue-200 dark:border-blue-500/30 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm text-slate-700 dark:text-gray-300 transition hover:bg-blue-100 dark:hover:bg-blue-500/10">
                            <svg class="h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="truncate font-medium">{{ basename($file['path']) }}</span>
                            <svg class="ml-auto h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="mb-6 rounded-xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-4">
                    <div class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 16v-4M12 8h.01" />
                        </svg>
                        <p class="text-xs text-slate-500 dark:text-gray-400">
                            No verification documents on record for this shop.
                        </p>
                    </div>
                </div>
                @endif

                {{-- Two columns --}}
                <div class="grid gap-6 lg:grid-cols-2">

                    {{-- Left --}}
                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                                Owner</h3>
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#FF5E14] text-sm font-bold text-white">
                                    {{ strtoupper(substr($t['owner']['name'], 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{
                                        $t['owner']['name'] }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{
                                        $t['owner']['email'] ?? 'No email' }}</p>
                                </div>
                            </div>
                            @if ($t['owner']['phone'])
                            <p class="mt-3 text-sm text-slate-600 dark:text-gray-400">{{ $t['owner']['phone'] }}</p>
                            @endif
                        </div>

                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                                Contact</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500 dark:text-gray-400">Email</dt>
                                    <dd class="truncate font-medium text-slate-900 dark:text-white">{{ $t['email'] ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500 dark:text-gray-400">Phone</dt>
                                    <dd class="font-medium text-slate-900 dark:text-white">{{ $t['phone'] ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500 dark:text-gray-400">Address</dt>
                                    <dd class="text-right font-medium text-slate-900 dark:text-white">{{ $t['address'] ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500 dark:text-gray-400">Coordinates</dt>
                                    <dd class="font-mono text-xs text-slate-700 dark:text-gray-300">
                                        {{ $t['latitude'] && $t['longitude'] ? "{$t['latitude']}, {$t['longitude']}" : '—' }}
                                    </dd>
                                </div>
                                @if ($t['latitude'] && $t['longitude'])
                                <div class="pt-1">
                                    <a href="https://www.google.com/maps?q={{ $t['latitude'] }},{{ $t['longitude'] }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#FF5E14] hover:underline">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Open in Google Maps
                                    </a>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Right --}}
                    <div class="space-y-4">
                        @if (! empty($t['business_hours']))
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                                Business hours</h3>
                            <div class="space-y-1.5 text-sm">
                                @foreach ($t['business_hours'] as $day => $hours)
                                <div class="flex justify-between">
                                    <span class="text-slate-500 dark:text-gray-400">{{ $day }}</span>
                                    <span class="font-medium text-slate-900 dark:text-white">
                                        {{ $hours['open'] ?? '—' }} – {{ $hours['close'] ?? '—' }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                            <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                                Meta</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-gray-400">Slug</dt>
                                    <dd class="font-mono text-xs text-slate-700 dark:text-gray-300">{{ $t['slug'] }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-gray-400">Commission rate</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">
                                        {{ number_format($t['commission_rate'] ?? 0, 2) }}%
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-gray-400">Submitted</dt>
                                    <dd class="text-slate-700 dark:text-gray-300">{{ $t['submitted_at'] ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-gray-400">Created</dt>
                                    <dd class="text-slate-700 dark:text-gray-300">{{ $t['created_at'] }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500 dark:text-gray-400">Setup complete</dt>
                                    <dd class="font-medium {{ $t['business_setup_completed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-gray-400' }}">
                                        {{ $t['business_setup_completed'] ? 'Yes' : 'No' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Shop ID <span class="font-mono font-semibold text-slate-700 dark:text-gray-300">#{{ $t['id'] }}</span>
                </p>

                <div class="flex flex-wrap items-center gap-2">

                    <button wire:click="closeTenantModal" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-100 dark:hover:bg-gray-700">
                        Close
                    </button>

                    {{-- Suspend --}}
                    <button wire:click="suspendFromModal({{ $t['id'] }})"
                        wire:confirm="Suspend {{ $t['name'] }}? The owner will need to contact support."
                        wire:loading.attr="disabled" wire:target="suspendFromModal" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Suspend shop
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>