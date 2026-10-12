<div class="space-y-6"
     x-data="{ notifications: [] }"
     @notify.window="
        notifications.push($event.detail);
        setTimeout(() => notifications.shift(), 4000);
     ">

    {{-- Notifications --}}
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        <template x-for="(n, i) in notifications" :key="i">
            <div class="pointer-events-auto w-80 rounded-xl border px-4 py-3 shadow-lg text-sm font-medium transition"
                 :class="{
                     'bg-emerald-50 border-emerald-200 text-emerald-700': n.type === 'success',
                     'bg-red-50 border-red-200 text-red-700': n.type === 'error',
                     'bg-slate-50 border-slate-200 text-slate-700': !n.type || (n.type !== 'success' && n.type !== 'error')
                 }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    {{-- Header --}}
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Orders</h1>
            </div>

            <div class="flex items-center gap-2">
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Search order # or customer..."
                       class="w-full sm:w-72 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />

                <button type="button" wire:click="$refresh"
                        title="Refresh"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 dark:text-gray-300 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        @php
            $counts = $this->counts;
            $tabs = [
                ['key' => 'pending',    'label' => 'Pending',    'count' => $counts['pending']    ?? 0],
                ['key' => 'processing', 'label' => 'Processing', 'count' => $counts['processing'] ?? 0],
                ['key' => 'ready',      'label' => 'Ready',      'count' => $counts['ready']      ?? 0],
                ['key' => 'completed',  'label' => 'Completed',  'count' => $counts['completed']  ?? 0],
                ['key' => 'cancelled',  'label' => 'Cancelled',  'count' => $counts['cancelled']  ?? 0],
                ['key' => '',           'label' => 'All',        'count' => array_sum($counts)],
            ];
        @endphp

        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $tab)
                <button wire:click="$set('statusFilter', '{{ $tab['key'] }}')"
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
    </div>

    @php $orders = $this->orders; @endphp

    @if ($orders->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
            <p class="text-sm font-medium text-slate-500 dark:text-gray-400">
                No orders in this status.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                @php
                    $badge = $order->status->badge();

                    // Payment method label
                    $paymentLabel = match ($order->payment_method) {
                        'cash_on_pickup' => 'Cash on pickup',
                        'bank_transfer'  => 'Bank transfer',
                        'paymongo_gcash' => 'GCash (online)',
                        'paymongo_card'  => 'Card (online)',
                        'paymongo_maya'  => 'Maya (online)',
                        default          => ucfirst(str_replace('_', ' ', (string) $order->payment_method)),
                    };

                    // Payment status pill
                    $payBadge = match ($order->payment_status) {
                        'paid'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                        'unpaid'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                        'failed'   => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
                        'refunded' => 'bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-300',
                        default    => 'bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-300',
                    };
                    $payDot = match ($order->payment_status) {
                        'paid'   => 'bg-emerald-500',
                        'unpaid' => 'bg-amber-500',
                        'failed' => 'bg-red-500',
                        default  => 'bg-slate-400',
                    };
                @endphp
                <article wire:key="order-{{ $order->id }}"
                         class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">

                    {{-- Header --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-gray-800 px-5 py-4">
                        <div>
                            <p class="font-mono text-xs text-slate-500 dark:text-gray-400">{{ $order->order_number }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                {{ $order->customer?->name ?? 'Unknown customer' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                {{ $order->created_at->format('M d, Y g:i A') }}
                            </p>

                            {{-- Payment info --}}
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-gray-800 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-gray-300">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    {{ $paymentLabel }}
                                </span>

                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $payBadge }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $payDot }}"></span>
                                    {{ $order->payment_status }}
                                </span>

                                @if ($order->paid_at)
                                    <span class="text-[10px] text-slate-400 dark:text-gray-500">
                                        Paid {{ $order->paid_at->format('M d, Y g:i A') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $order->status->label() }}
                            </span>
                            <span class="text-lg font-bold text-[#FF5E14]">₱{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="divide-y divide-slate-100 dark:divide-gray-800">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between px-5 py-3 text-sm">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-slate-900 dark:text-white">
                                        {{ $item->product_name }}
                                        @if ($item->variant_label)
                                            <span class="text-slate-500 dark:text-gray-400">· {{ $item->variant_label }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">
                                        {{ $item->quantity }} × ₱{{ number_format($item->unit_price, 2) }}
                                    </p>
                                </div>
                                <p class="font-semibold text-slate-900 dark:text-white">₱{{ number_format($item->line_total, 2) }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Notes --}}
                    @if ($order->notes)
                        <div class="border-t border-slate-100 dark:border-gray-800 px-5 py-3 bg-slate-50 dark:bg-gray-800/40">
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Notes</p>
                            <p class="text-sm text-slate-700 dark:text-gray-300">{{ $order->notes }}</p>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-5 py-3">
                        @if ($order->status->canBeProcessed())
                            <button wire:click="accept({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="accept({{ $order->id }})"
                                    class="rounded-xl bg-[#FF5E14] px-4 py-2 text-xs font-bold text-white shadow-sm shadow-orange-500/30 hover:bg-[#D94E10] transition disabled:opacity-60">
                                Accept order
                            </button>
                            <button wire:click="cancel({{ $order->id }})"
                                    wire:confirm="Cancel this order?"
                                    wire:loading.attr="disabled"
                                    wire:target="cancel({{ $order->id }})"
                                    class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-100 transition disabled:opacity-60">
                                Reject
                            </button>
                        @endif

                        @if ($order->status->canBeMarkedReady())
                            <button wire:click="markReady({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="markReady({{ $order->id }})"
                                    class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition disabled:opacity-60">
                                Mark as ready
                            </button>
                        @endif

                        @if ($order->status === \App\Enums\OrderStatus::Processing)
                            <button wire:click="cancel({{ $order->id }})"
                                    wire:confirm="Cancel this order? Stock will be restored to inventory."
                                    wire:loading.attr="disabled"
                                    wire:target="cancel({{ $order->id }})"
                                    class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-100 transition disabled:opacity-60">
                                Cancel &amp; restore stock
                            </button>
                        @endif

                        @if ($order->status->canBeCompleted())
                            <button wire:click="complete({{ $order->id }})"
                                    wire:confirm="Complete this order? A sale will be recorded for accounting."
                                    wire:loading.attr="disabled"
                                    wire:target="complete({{ $order->id }})"
                                    class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition disabled:opacity-60">
                                <span wire:loading.remove wire:target="complete({{ $order->id }})">Complete &amp; record sale</span>
                                <span wire:loading wire:target="complete({{ $order->id }})">Processing…</span>
                            </button>
                        @endif

                        @if ($order->status->isFinal())
                            <span class="text-xs text-slate-500 dark:text-gray-400">
                                @if ($order->status === \App\Enums\OrderStatus::Completed)
                                    Completed on {{ $order->completed_at?->format('M d, Y') }}
                                @else
                                    Cancelled on {{ $order->cancelled_at?->format('M d, Y') }}
                                @endif
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div>{{ $orders->links() }}</div>
    @endif
</div>