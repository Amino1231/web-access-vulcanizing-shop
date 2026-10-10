<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">Platform management</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Orders</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Every order across every shop — look up by #, customer, or shop for support.
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
            ['key' => '',             'label' => 'All',        'count' => $counts['all']],
            ['key' => 'pending',      'label' => 'Pending',    'count' => $counts['pending']],
            ['key' => 'processing',   'label' => 'Processing', 'count' => $counts['processing']],
            ['key' => 'ready',        'label' => 'Ready',      'count' => $counts['ready']],
            ['key' => 'completed',    'label' => 'Completed',  'count' => $counts['completed']],
            ['key' => 'cancelled',    'label' => 'Cancelled',  'count' => $counts['cancelled']],
        ];
        @endphp
        @foreach ($tabs as $tab)
        <button wire:click="$set('statusFilter', '{{ $tab['key'] }}')" type="button"
            class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                {{ $statusFilter === $tab['key']
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
        <div class="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Order #, customer, or shop..."
                       class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
            </div>

            <select wire:model.live="tenantFilter"
                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                <option value="">All shops</option>
                @foreach ($this->tenants as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>

            <input wire:model.live="from" type="date"
                   class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />

            <input wire:model.live="to" type="date"
                   class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />

            @if ($search || $statusFilter || $tenantFilter || $from || $to)
            <button wire:click="clearFilters" type="button"
                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                Clear
            </button>
            @endif
        </div>
    </div>

    @php $orders = $this->orders; @endphp

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($orders as $order)
            @php $badge = $order->status->badge(); @endphp
            <div wire:key="m-order-{{ $order->id }}"
                 class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-mono text-xs text-slate-500 dark:text-gray-400">{{ $order->order_number }}</p>
                        <p class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white">
                            {{ $order->customer?->name ?? 'Deleted user' }}
                        </p>
                        <p class="truncate text-xs text-slate-500 dark:text-gray-400">
                            {{ $order->tenant?->name ?? 'Unknown shop' }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                        {{ $order->status->label() }}
                    </span>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 dark:border-gray-800 pt-3">
                    <p class="text-xs text-slate-500 dark:text-gray-400">
                        {{ $order->created_at->format('M d, Y g:i A') }}
                    </p>
                    <p class="text-base font-bold text-[#FF5E14]">₱{{ number_format($order->total, 2) }}</p>
                </div>

                <button wire:click="viewOrder({{ $order->id }})" type="button"
                        class="mt-3 w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    View details
                </button>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-sm text-slate-500 dark:text-gray-400">No orders found.</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Order</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Shop</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Items</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Total</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Date</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($orders as $order)
                        @php $badge = $order->status->badge(); @endphp
                        <tr wire:key="order-{{ $order->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">

                            <td class="whitespace-nowrap px-4 py-3">
                                <p class="font-mono text-xs text-slate-600 dark:text-gray-300">{{ $order->order_number }}</p>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($order->tenant?->logo)
                                        <img src="{{ asset('storage/'.$order->tenant->logo) }}" alt=""
                                             class="h-7 w-7 shrink-0 rounded-md object-cover border border-slate-200 dark:border-gray-700" />
                                    @endif
                                    <span class="truncate text-sm text-slate-900 dark:text-white">
                                        {{ $order->tenant?->name ?? '—' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                                    {{ $order->customer?->name ?? '—' }}
                                </p>
                                <p class="truncate text-xs text-slate-500 dark:text-gray-400">
                                    {{ $order->customer?->email ?? '' }}
                                </p>
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                                {{ $order->items->count() }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $order->status->label() }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                                ₱{{ number_format($order->total, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs text-slate-500 dark:text-gray-400">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="viewOrder({{ $order->id }})" type="button" title="View"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    @if ($order->status->canBeCancelled())
                                        <button wire:click="confirmCancel({{ $order->id }})" type="button" title="Force cancel"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 dark:hover:bg-red-500/10">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">No orders found</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">Try adjusting your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->total() > 0)
            <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }}
                </p>
                <div>{{ $orders->links() }}</div>
            </div>
        @endif
    </div>

    {{-- Mobile pagination --}}
    <div class="md:hidden">
        {{ $orders->links() }}
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Cancel confirmation modal --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if ($confirmingOrderId && $confirmingAction === 'cancel')
    <div x-data @keydown.escape.window="$wire.cancelAction()"
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Force cancel this order?</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $confirmingNumber }}</span> — if the shop already accepted it, stock will be restored to inventory.
                    </p>

                    <div class="mt-4">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Reason (recorded in the log)
                        </label>
                        <textarea wire:model="cancelReason" rows="3"
                                  class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-red-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-red-100"></textarea>
                        @error('cancelReason')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelAction" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="performCancel" wire:loading.attr="disabled" wire:target="performCancel" type="button"
                        class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="performCancel">Force cancel order</span>
                    <span wire:loading wire:target="performCancel">Working…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Detail modal --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if ($viewingOrderId && $viewingOrder)
    @php $o = $viewingOrder; @endphp
    <div x-data @keydown.escape.window="$wire.closeOrderModal()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
        <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Order detail</p>
                    <h2 class="mt-1 truncate text-xl font-bold text-slate-900 dark:text-white">{{ $o['order_number'] }}</h2>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $o['badge']['bg'] }} {{ $o['badge']['text'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $o['badge']['dot'] }}"></span>
                            {{ $o['status_label'] }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-gray-400">
                            Placed {{ $o['created_at'] }}
                        </span>
                    </div>
                </div>
                <button wire:click="closeOrderModal" type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto p-6 space-y-5">

                {{-- Shop + Customer --}}
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Shop</p>
                        <div class="mt-2 flex items-center gap-2">
                            @if ($o['tenant']['logo'])
                                <img src="{{ $o['tenant']['logo'] }}" alt=""
                                     class="h-8 w-8 rounded-lg object-cover border border-slate-200" />
                            @endif
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $o['tenant']['name'] ?? '—' }}</p>
                        </div>
                        @if ($o['tenant']['phone'])
                            <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">📞 {{ $o['tenant']['phone'] }}</p>
                        @endif
                        @if ($o['tenant']['email'])
                            <p class="text-xs text-slate-500 dark:text-gray-400">✉ {{ $o['tenant']['email'] }}</p>
                        @endif
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</p>
                        <p class="mt-2 font-semibold text-slate-900 dark:text-white">{{ $o['customer']['name'] }}</p>
                        @if ($o['customer']['email'])
                            <p class="text-xs text-slate-500 dark:text-gray-400">✉ {{ $o['customer']['email'] }}</p>
                        @endif
                        @if ($o['customer']['phone'])
                            <p class="text-xs text-slate-500 dark:text-gray-400">📞 {{ $o['customer']['phone'] }}</p>
                        @endif
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                        Items ({{ count($o['items']) }})
                    </h3>
                    <div class="space-y-2">
                        @foreach ($o['items'] as $item)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3">
                                @if ($item['image'])
                                    <img src="{{ $item['image'] }}" alt=""
                                         class="h-14 w-14 shrink-0 rounded-lg object-cover border border-slate-200" />
                                @else
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-gray-800">
                                        <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-8-6h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $item['product_name'] }}</p>
                                    @if ($item['variant_label'])
                                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ $item['variant_label'] }}</p>
                                    @endif
                                    <p class="text-xs text-slate-500 dark:text-gray-400">
                                        {{ $item['quantity'] }} × ₱{{ number_format($item['unit_price'], 2) }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-slate-900 dark:text-white">₱{{ number_format($item['line_total'], 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Totals --}}
                <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-gray-400">Subtotal</span>
                        <span class="font-semibold text-slate-900 dark:text-white">₱{{ number_format($o['subtotal'], 2) }}</span>
                    </div>
                    @if ($o['discount'] > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-gray-400">Discount</span>
                            <span class="font-semibold text-emerald-600">-₱{{ number_format($o['discount'], 2) }}</span>
                        </div>
                    @endif
                    @if ($o['tax'] > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-gray-400">Tax</span>
                            <span class="font-semibold text-slate-900 dark:text-white">₱{{ number_format($o['tax'], 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-slate-200 dark:border-gray-800 pt-2">
                        <span class="text-base font-bold text-slate-900 dark:text-white">Total</span>
                        <span class="text-lg font-bold text-[#FF5E14]">₱{{ number_format($o['total'], 2) }}</span>
                    </div>
                </div>

                {{-- Payment + notes --}}
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Payment</p>
                        <p class="mt-1 text-sm font-medium text-slate-900 dark:text-white capitalize">
                            {{ str_replace('_', ' ', $o['payment_method']) }}
                        </p>
                        @if ($o['sale'])
                            <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                                Invoice: <span class="font-mono">{{ $o['sale']['invoice_number'] }}</span>
                            </p>
                        @endif
                    </div>

                    @if ($o['notes'])
                        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer notes</p>
                            <p class="mt-1 text-sm text-slate-700 dark:text-gray-300">{{ $o['notes'] }}</p>
                        </div>
                    @endif
                </div>

                {{-- Timeline --}}
                <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Timeline</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-gray-400">Placed</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ $o['created_at'] }}</span>
                        </div>
                        @if ($o['processed_at'])
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Accepted</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $o['processed_at'] }}</span>
                            </div>
                        @endif
                        @if ($o['completed_at'])
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Completed</span>
                                <span class="font-medium text-emerald-600">{{ $o['completed_at'] }}</span>
                            </div>
                        @endif
                        @if ($o['cancelled_at'])
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Cancelled</span>
                                <span class="font-medium text-red-600">{{ $o['cancelled_at'] }}</span>
                            </div>
                            @if ($o['cancellation_reason'])
                                <p class="mt-1 text-xs italic text-slate-500 dark:text-gray-400">
                                    Reason: {{ $o['cancellation_reason'] }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                <button wire:click="closeOrderModal" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Close
                </button>

                @if (in_array($o['status'], ['pending', 'processing']))
                    <button wire:click="confirmCancel({{ $o['id'] }})" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Force cancel
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>