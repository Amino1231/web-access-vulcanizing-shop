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
                     'bg-red-50 border-red-200 text-red-700': n.type === 'error'
                 }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Finance</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Sales Ledger</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">View completed orders, services, and manual walk-in sales.</p>
        </div>

        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Search invoice, customer, or item..."
                   class="w-full sm:w-64 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
            
            <button wire:click="openManualSaleModal" 
                    wire:loading.attr="disabled" wire:target="openManualSaleModal"
                    type="button"
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#D94E10] transition disabled:opacity-60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">Record Sale</span>
            </button>
        </div>
    </div>

    {{-- Summary Stats --}}
    @php $stats = $this->summary; @endphp
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Total Sales</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['total_sales']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-gray-400">Gross Revenue</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">₱{{ number_format($stats['gross'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-500">Platform Commission</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">-₱{{ number_format($stats['commission'], 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 bg-gradient-to-br from-slate-50 to-white dark:from-gray-800 dark:to-gray-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-900 dark:text-white">Net Payout</p>
            <p class="mt-2 text-2xl font-bold text-[#FF5E14]">₱{{ number_format($stats['net'], 2) }}</p>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Item</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Invoice / Date</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Qty</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Total</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Commission</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Net</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                    @forelse ($this->saleItems as $item)
                        @php
                            $sale = $item->sale;
                            $itemCommission = 0;
                            $itemNet = $item->subtotal;

                            if ($sale && $sale->subtotal > 0) {
                                $ratio = $item->subtotal / $sale->subtotal;
                                $itemCommission = $sale->platform_commission * $ratio;
                                $itemNet = $item->subtotal - $itemCommission;
                            }
                        @endphp
                        <tr wire:key="sale-item-{{ $item->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">
                            <td class="px-5 py-3">
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ $item->product?->name ?? $item->service?->name }}
                                </p>
                                @if($item->item_type === 'service')
                                    <span class="text-[9px] font-bold uppercase text-blue-600 dark:text-blue-400">Service</span>
                                @else
                                    <span class="text-[9px] font-bold uppercase text-orange-600 dark:text-orange-400">Product</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <p class="font-mono text-xs text-slate-600 dark:text-gray-300">{{ $sale->invoice_number }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $sale->created_at->format('M d, Y g:i A') }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-900 dark:text-white">
                                    {{ $sale->customer?->name ?? $sale->customer_name ?? 'Walk-In' }}
                                </p>
                                <span class="text-[10px] uppercase text-slate-500">{{ str_replace('_', ' ', $sale->payment_method) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-slate-700 dark:text-gray-300">
                                x{{ $item->quantity }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-slate-900 dark:text-white">
                                ₱{{ number_format($item->subtotal, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                -₱{{ number_format($itemCommission, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-[#FF5E14]">
                                ₱{{ number_format($itemNet, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="viewSale({{ $sale->id }})" 
                                        wire:loading.attr="disabled" wire:target="viewSale({{ $sale->id }})"
                                        type="button"
                                        class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-gray-200 hover:bg-slate-50 transition disabled:opacity-60">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-500 dark:text-gray-400">
                                No sales recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($this->saleItems->hasPages())
            <div class="border-t border-slate-200 dark:border-gray-800 px-5 py-3">
                {{ $this->saleItems->links() }}
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SALE DETAILS MODAL --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($this->viewingSale)
        @php $s = $this->viewingSale; @endphp
        <div wire:key="sale-modal-{{ $s->id }}" 
             x-data x-init="document.body.style.overflow='hidden'" x-destroy="document.body.style.overflow=''"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
            <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">
                
                <div class="flex items-start justify-between border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Sale Details</h3>
                        <p class="text-xs font-mono text-slate-500 mt-1">{{ $s->invoice_number }}</p>
                    </div>
                    <a href="{{ route('owner.sales') }}" wire:click="closeSaleModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-slate-500">Customer</p>
                            <p class="font-semibold text-slate-900 dark:text-white">
                                {{ $s->customer?->name ?? $s->customer_name ?? 'Walk-In' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-wider text-slate-500">Date</p>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $s->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-gray-800 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-[10px] font-bold text-slate-500 uppercase">Item</th>
                                    <th class="px-3 py-2 text-center text-[10px] font-bold text-slate-500 uppercase">Qty</th>
                                    <th class="px-3 py-2 text-right text-[10px] font-bold text-slate-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                                @foreach($s->items as $item)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <p class="font-medium text-slate-900 dark:text-white">
                                                {{ $item->item_type === 'product' ? $item->product?->name : $item->service?->name }}
                                            </p>
                                        </td>
                                        <td class="px-3 py-2 text-center text-slate-600 dark:text-gray-400">x{{ $item->quantity }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">₱{{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-600 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span>₱{{ number_format($s->subtotal, 2) }}</span>
                        </div>
                        @if($s->tax > 0)
                            <div class="flex justify-between text-slate-600 dark:text-gray-400">
                                <span>Tax</span>
                                <span>₱{{ number_format($s->tax, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-bold text-base text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-gray-800">
                            <span>Total</span>
                            <span>₱{{ number_format($s->final_amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-500/10 p-4 space-y-2 border border-emerald-100 dark:border-emerald-500/20">
                        <div class="flex justify-between text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            <span>Platform Commission ({{ $s->commission_rate_applied }}%)</span>
                            <span>-₱{{ number_format($s->platform_commission, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-emerald-800 dark:text-emerald-300 border-t border-emerald-200/50 dark:border-emerald-500/30 pt-2">
                            <span>Your Net Payout</span>
                            <span>₱{{ number_format($s->shop_net_revenue, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 dark:border-gray-800 px-6 py-4 bg-slate-50 dark:bg-gray-900/50 text-right">
                    <a href="{{ route('owner.sales') }}" wire:click="closeSaleModal" type="button" class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-100">
                        Close
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MANUAL SALE MODAL --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($showManualSaleModal)
        <div wire:key="manual-sale-modal"
             x-data x-init="document.body.style.overflow='hidden'" x-destroy="document.body.style.overflow=''"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
            <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">
                
                <div class="flex items-start justify-between border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Record Manual Sale</h3>
                        <p class="text-xs text-slate-500 mt-1">For walk-ins or direct transactions.</p>
                    </div>
                    <a href="{{ route('owner.sales') }}" wire:click="closeManualSaleModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-2">Customer Name <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="text" wire:model="customer_name" placeholder="Juan Dela Cruz" class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-100" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-2">Item Type</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="item_type" value="service" class="text-[#FF5E14] focus:ring-[#FF5E14]">
                                <span class="text-sm text-slate-700 dark:text-gray-300">Service</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="item_type" value="product" class="text-[#FF5E14] focus:ring-[#FF5E14]">
                                <span class="text-sm text-slate-700 dark:text-gray-300">Product</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-2">
                            Select {{ ucfirst($item_type) }}
                        </label>
                        <select wire:model="item_id" class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-100">
                            <option value="">-- Choose --</option>
                            @if($item_type === 'service')
                                @foreach($this->availableServices as $svc)
                                    <option value="{{ $svc->id }}">{{ $svc->name }} (₱{{ number_format($svc->price, 2) }})</option>
                                @endforeach
                            @else
                                @foreach($this->availableProducts as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} (₱{{ number_format($prod->selling_price, 2) }})</option>
                                @endforeach
                            @endif
                        </select>
                        @error('item_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-2">Quantity</label>
                            <input type="number" wire:model="quantity" min="1" class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-100" />
                            @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-2">Payment</label>
                            <select wire:model="payment_method" class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-100">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 dark:border-gray-800 px-6 py-4 bg-slate-50 dark:bg-gray-900/50 flex justify-end gap-3">
                    <a href="{{ route('owner.sales') }}" wire:click="closeManualSaleModal" type="button" class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-100">
                        Cancel
                    </a>
                    <button wire:click="saveManualSale" wire:loading.attr="disabled" wire:target="saveManualSale" type="button" class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#D94E10] disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveManualSale">Save Sale</span>
                        <span wire:loading wire:target="saveManualSale">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>