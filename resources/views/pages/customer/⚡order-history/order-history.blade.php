<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">

        @if (session('order_placed'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                Order <span class="font-bold">{{ session('order_placed') }}</span> placed successfully!
            </div>
        @endif

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">My orders</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Track and manage your purchases.</p>
            </div>

            <select wire:model.live="statusFilter"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                <option value="">All statuses</option>
                @foreach (\App\Enums\OrderStatus::options() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @php $orders = $this->orders; @endphp

        @if ($orders->isEmpty())
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-slate-500 dark:text-gray-400">No orders yet.</p>
                <a href="{{ route('customer.dashboard') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#D94E10] transition">
                    Start shopping
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    @php $badge = $order->status->badge(); @endphp
                    <div wire:key="order-{{ $order->id }}"
                         class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">

                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-gray-800 px-5 py-4">
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
                            @if ($order->status->canBeCancelled())
                                <button wire:click="cancelOrder({{ $order->id }})"
                                        wire:confirm="Cancel this order?"
                                        class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-100 transition">
                                    Cancel order
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
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
</div>