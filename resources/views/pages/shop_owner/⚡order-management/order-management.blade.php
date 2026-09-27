<div class="space-y-6" wire:poll.20s="$refresh">
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Orders</h1>
            </div>

            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Search order # or customer..."
                   class="w-full sm:w-72 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
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
            <p class="text-sm font-medium text-slate-500 dark:text-gray-400">No orders in this status.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                @php $badge = $order->status->badge(); @endphp
                <article wire:key="order-{{ $order->id }}"
                         class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">

                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-gray-800 px-5 py-4">
                        <div>
                            <p class="font-mono text-xs text-slate-500 dark:text-gray-400">{{ $order->order_number }}</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                {{ $order->customer?->name ?? 'Unknown customer' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                {{ $order->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                {{ $order->status->label() }}
                            </span>
                            <span class="text-lg font-bold text-[#FF5E14]">₱{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>

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

                    @if ($order->notes)
                        <div class="border-t border-slate-100 dark:border-gray-800 px-5 py-3 bg-slate-50 dark:bg-gray-800/40">
                            <p class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-1">Notes</p>
                            <p class="text-sm text-slate-700 dark:text-gray-300">{{ $order->notes }}</p>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-5 py-3">
                        @if ($order->status->canBeProcessed())
                            <button wire:click="accept({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    class="rounded-xl bg-[#FF5E14] px-4 py-2 text-xs font-bold text-white shadow-sm shadow-orange-500/30 hover:bg-[#D94E10] transition disabled:opacity-60">
                                Accept order
                            </button>
                            <button wire:click="cancel({{ $order->id }})"
                                    wire:confirm="Cancel this order?"
                                    wire:loading.attr="disabled"
                                    class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-100 transition disabled:opacity-60">
                                Reject
                            </button>
                        @endif

                        @if ($order->status->canBeMarkedReady())
                            <button wire:click="markReady({{ $order->id }})"
                                    wire:loading.attr="disabled"
                                    class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition disabled:opacity-60">
                                Mark as ready
                            </button>
                        @endif

                        @if ($order->status->canBeCompleted())
                            <button wire:click="complete({{ $order->id }})"
                                    wire:confirm="Complete this order? Stock will be deducted and a sale will be recorded."
                                    wire:loading.attr="disabled"
                                    class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition disabled:opacity-60">
                                <span wire:loading.remove wire:target="complete">Complete &amp; record sale</span>
                                <span wire:loading wire:target="complete">Processing...</span>
                            </button>
                        @endif

                        @if ($order->status->isFinal())
                            <span class="text-xs text-slate-500 dark:text-gray-400">
                                @if ($order->status->value === 'completed')
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