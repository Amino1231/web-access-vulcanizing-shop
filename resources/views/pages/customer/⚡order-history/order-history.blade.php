<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">

        @if (session('order_placed'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                Order <span class="font-bold">{{ session('order_placed') }}</span> placed successfully!
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">My orders</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Track and manage your purchases.</p>
        </div>

        {{-- Status tabs --}}
        @php
            $counts = $this->counts;
            $tabs = [
                ['key' => '',           'label' => 'All',       'count' => array_sum($counts)],
                ['key' => 'pending',    'label' => 'Pending',   'count' => $counts['pending']    ?? 0],
                ['key' => 'processing', 'label' => 'Processing','count' => $counts['processing'] ?? 0],
                ['key' => 'ready',      'label' => 'Ready',     'count' => $counts['ready']      ?? 0],
                ['key' => 'completed',  'label' => 'Completed', 'count' => $counts['completed']  ?? 0],
                ['key' => 'cancelled',  'label' => 'Cancelled', 'count' => $counts['cancelled']  ?? 0],
            ];
        @endphp

        <div class="mb-6 flex flex-wrap gap-2">
            @foreach ($tabs as $tab)
                <button wire:click="setFilter('{{ $tab['key'] }}')" type="button"
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

        @php $orders = $this->orders; @endphp

        @if ($orders->isEmpty())
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-slate-500 dark:text-gray-400">No orders in this status.</p>
                <a href="{{ route('customer.dashboard') }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#D94E10] transition">
                    Start shopping
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    @php $badge = $order->status->badge(); @endphp
                    <div wire:key="order-{{ $order->id }}"
                         class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden hover:border-orange-200 dark:hover:border-orange-500/30 transition">

                        {{-- Header — clickable --}}
                        <button type="button"
                                wire:click="viewOrder({{ $order->id }})"
                                class="w-full text-left flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-gray-800 px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-gray-800/40">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-gray-400">
                                    {{ $order->order_number }}
                                </p>
                                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $order->tenant?->name }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-gray-400">
                                    {{ $order->created_at->format('M d, Y g:i A') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $order->status->label() }}
                                </span>
                                <span class="text-lg font-bold text-[#FF5E14]">₱{{ number_format($order->total, 2) }}</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </button>

                        {{-- Items --}}
                        <div class="divide-y divide-slate-100 dark:divide-gray-800">
                            @foreach ($order->items->take(2) as $item)
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

                            @if ($order->items->count() > 2)
                                <div class="px-5 py-2 text-xs text-slate-500 dark:text-gray-400 bg-slate-50 dark:bg-gray-800/40">
                                    + {{ $order->items->count() - 2 }} more {{ \Illuminate\Support\Str::plural('item', $order->items->count() - 2) }}
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-5 py-3">
                            <button wire:click="viewOrder({{ $order->id }})" type="button"
                                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-xs font-bold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 transition">
                                View details
                            </button>

                            @if ($order->status === \App\Enums\OrderStatus::Pending)
                                <button wire:click="cancelOrder({{ $order->id }})"
                                        wire:confirm="Cancel this order?"
                                        wire:loading.attr="disabled"
                                        wire:target="cancelOrder({{ $order->id }})"
                                        class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-100 transition disabled:opacity-60">
                                    Cancel order
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- ORDER PREVIEW MODAL --}}
    {{-- ═══════════════════════════════════════════ --}}
    @if ($viewingOrderId && $viewingOrder)
        @php $o = $viewingOrder; @endphp
        <div x-data @keydown.escape.window="$wire.closeOrderModal()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm"
             wire:key="order-modal-{{ $o['id'] }}">
            <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Order details</p>
                        <h2 class="mt-1 truncate text-xl font-bold text-slate-900 dark:text-white">
                            {{ $o['order_number'] }}
                        </h2>
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
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 dark:text-gray-400 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="overflow-y-auto p-6 space-y-6">

                    {{-- Shop info --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                        <h3 class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Shop</h3>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $o['tenant']['name'] }}</p>
                        @if ($o['tenant']['phone'])
                            <p class="text-sm text-slate-500 dark:text-gray-400">📞 {{ $o['tenant']['phone'] }}</p>
                        @endif
                        @if ($o['tenant']['address'])
                            <p class="text-sm text-slate-500 dark:text-gray-400">📍 {{ $o['tenant']['address'] }}</p>
                        @endif
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
                                        <img src="{{ $item['image'] }}" class="h-14 w-14 shrink-0 rounded-lg object-cover border border-slate-200 dark:border-gray-700" />
                                    @else
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-gray-800">
                                            <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-8-6h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
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

                    {{-- Summary --}}
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
                            <h3 class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Payment</h3>
                            <p class="text-sm font-medium text-slate-900 dark:text-white capitalize">
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
                                <h3 class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Your notes</h3>
                                <p class="text-sm text-slate-700 dark:text-gray-300">{{ $o['notes'] }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Timeline --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Timeline</h3>
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
                            class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 transition">
                        Close
                    </button>

                    @if ($o['status'] === 'pending')
                        <button wire:click="cancelOrder({{ $o['id'] }})"
                                wire:confirm="Cancel this order?"
                                wire:loading.attr="disabled" wire:target="cancelOrder({{ $o['id'] }})"
                                class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2.5 text-sm font-bold text-red-600 dark:text-red-400 hover:bg-red-100 transition disabled:opacity-60">
                            Cancel order
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>