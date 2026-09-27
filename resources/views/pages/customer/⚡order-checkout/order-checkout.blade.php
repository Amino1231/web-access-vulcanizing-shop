<div class="bg-gray-50 dark:bg-gray-950 min-h-screen" wire:poll.15s>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-6">Checkout</h1>

        @php $cart = $this->cart; @endphp

        @if ($cart->isEmpty())
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-slate-500 dark:text-gray-400">Your cart is empty.</p>
                <a href="{{ route('customer.dashboard') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#D94E10] transition">
                    Continue shopping
                </a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 divide-y divide-slate-200 dark:divide-gray-800">
                        @foreach ($cart as $row)
                            <div wire:key="cart-{{ $row['key'] }}" class="flex gap-4 p-4">
                                <img src="{{ asset('storage/' . $row['product']->ft_img) }}"
                                     alt="{{ $row['product']->name }}"
                                     class="h-20 w-20 rounded-xl object-cover border border-slate-200 dark:border-gray-700" />

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                        {{ $row['product']->name }}
                                    </p>
                                    @if ($row['variant'])
                                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ $row['variant']->sku }}</p>
                                    @endif
                                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">
                                        Sold by {{ $row['product']->tenant?->name }}
                                    </p>

                                    <div class="mt-2 flex items-center gap-2">
                                        <input type="number" min="1" max="{{ $row['available_stock'] }}"
                                               value="{{ $row['quantity'] }}"
                                               wire:change="updateQuantity('{{ $row['key'] }}', $event.target.value)"
                                               class="w-16 rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-2 py-1 text-sm text-slate-900 dark:text-white" />
                                        <button wire:click="removeItem('{{ $row['key'] }}')"
                                                class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-bold text-[#FF5E14]">₱{{ number_format($row['line_total'], 2) }}</p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">
                                        {{ $row['quantity'] }} × ₱{{ number_format($row['unit_price'], 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Payment method</label>
                            <select wire:model="payment_method"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                                <option value="cash_on_pickup">Cash on pickup</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank transfer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">Notes (optional)</label>
                            <textarea wire:model="notes" rows="3"
                                      placeholder="Any special instructions for the shop..."
                                      class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"></textarea>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-4 sticky top-24">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Order summary</h2>

                        @php $totals = $this->totals; @endphp

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Subtotal</span>
                                <span class="font-semibold text-slate-900 dark:text-white">₱{{ number_format($totals['subtotal'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Tax (12%)</span>
                                <span class="font-semibold text-slate-900 dark:text-white">₱{{ number_format($totals['tax'], 2) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-slate-200 dark:border-gray-800 pt-3">
                                <span class="text-base font-bold text-slate-900 dark:text-white">Total</span>
                                <span class="text-xl font-bold text-[#FF5E14]">₱{{ number_format($totals['total'], 2) }}</span>
                            </div>
                        </div>

                        <button wire:click="placeOrder" wire:loading.attr="disabled"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                            <span wire:loading.remove wire:target="placeOrder">Place order</span>
                            <span wire:loading wire:target="placeOrder">Placing order…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>