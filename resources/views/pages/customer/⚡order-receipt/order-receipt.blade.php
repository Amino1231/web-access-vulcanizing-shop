<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">

        {{-- Toolbar (hidden when printing) --}}
        <div class="print:hidden mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('customer.orders') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 dark:text-gray-300 hover:text-slate-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back to orders
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('customer.order_receipt.pdf', $order) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download
                </a>
            </div>
        </div>

        {{-- Receipt --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm print:shadow-none print:border-0 print:rounded-none">

            {{-- Header --}}
            <div class="border-b border-slate-200 dark:border-gray-800 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Receipt</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                            {{ $order->order_number }}
                        </p>
                        <p class="text-xs text-slate-400 dark:text-gray-500 mt-0.5">
                            Issued {{ $order->created_at->format('F d, Y g:i A') }}
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $order->tenant?->name }}
                        </p>
                        @if ($order->tenant?->address)
                            <p class="text-xs text-slate-500 dark:text-gray-400 max-w-xs">
                                {{ $order->tenant->address }}
                            </p>
                        @endif
                        @if ($order->tenant?->phone)
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                {{ $order->tenant->phone }}
                            </p>
                        @endif
                        @if ($order->tenant?->email)
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                {{ $order->tenant->email }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bill to + status --}}
            <div class="border-b border-slate-200 dark:border-gray-800 p-6 sm:p-8 grid gap-6 sm:grid-cols-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400 mb-2">Billed to</p>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $order->customer?->name ?? 'Guest' }}</p>
                    @if ($order->customer?->email)
                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ $order->customer->email }}</p>
                    @endif
                    @if ($order->customer?->phone)
                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ $order->customer->phone }}</p>
                    @endif
                </div>

                <div class="sm:text-right">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400 mb-2">Payment</p>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">
                        {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
                    </p>
                    <p class="text-xs mt-1">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider
                            {{ $order->payment_status === 'paid'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-amber-100 text-amber-700' }}">
                            {{ $order->payment_status }}
                        </span>
                    </p>
                    @if ($order->paid_at)
                        <p class="text-xs text-slate-400 dark:text-gray-500 mt-1">
                            Paid {{ $order->paid_at->format('M d, Y g:i A') }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="p-6 sm:p-8">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-gray-800 text-left">
                            <th class="py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Item</th>
                            <th class="py-2 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Qty</th>
                            <th class="py-2 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Unit price</th>
                            <th class="py-2 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-3 pr-3">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $item->product_name }}</p>
                                    @if ($item->variant_label)
                                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ $item->variant_label }}</p>
                                    @endif
                                </td>
                                <td class="py-3 text-right text-slate-700 dark:text-gray-300">{{ $item->quantity }}</td>
                                <td class="py-3 text-right text-slate-700 dark:text-gray-300">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right font-semibold text-slate-900 dark:text-white">₱{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Totals --}}
                <div class="mt-6 ml-auto max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-gray-400">Subtotal</span>
                        <span class="font-semibold text-slate-900 dark:text-white">₱{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if ($order->discount > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-gray-400">Discount</span>
                            <span class="font-semibold text-slate-900 dark:text-white">- ₱{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    @if ($order->tax > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-gray-400">Tax</span>
                            <span class="font-semibold text-slate-900 dark:text-white">₱{{ number_format($order->tax, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-slate-200 dark:border-gray-800 pt-3">
                        <span class="text-base font-bold text-slate-900 dark:text-white">Total</span>
                        <span class="text-lg font-bold text-[#FF5E14]">₱{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                {{-- Notes --}}
                @if ($order->notes)
                    <div class="mt-8 rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400 mb-1">Notes</p>
                        <p class="text-sm text-slate-700 dark:text-gray-300">{{ $order->notes }}</p>
                    </div>
                @endif

                {{-- Footer --}}
                <div class="mt-8 pt-6 border-t border-slate-200 dark:border-gray-800 text-center">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Thank you for your order!</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">
                        This receipt was generated by {{ config('app.name') }}.
                    </p>
                </div>
            </div>
        </div>

        {{-- Print hint --}}
        <p class="print:hidden mt-4 text-center text-xs text-slate-400 dark:text-gray-500">
            Tip: use your browser's "Save as PDF" option when printing to download a copy.
        </p>
    </div>
</div>

{{-- Hide the app chrome when printing --}}
<style>
    @media print {
        @page { margin: 1cm; }
        body { background: white !important; }
        nav, header, aside, .print\:hidden { display: none !important; }
    }
</style>