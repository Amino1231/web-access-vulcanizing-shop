<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-6">Checkout</h1>

        @php $cart = $this->cart; @endphp

        @if ($cart->isEmpty())
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-slate-500 dark:text-gray-400">Your cart is empty.</p>
                <a href="{{ route('customer.dashboard') }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#D94E10] transition">
                    Continue shopping
                </a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">

                {{-- LEFT --}}
                <div class="space-y-4">

                    {{-- Select all header --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox"
                                   wire:click="toggleSelectAll"
                                   @checked($this->isAllSelected)
                                   class="h-4 w-4 rounded border-slate-300 dark:border-gray-600 text-[#FF5E14] focus:ring-[#FF5E14] cursor-pointer" />
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                Select all ({{ $cart->count() }} items)
                            </span>
                        </label>
                    </div>

                    {{-- Items --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 divide-y divide-slate-200 dark:divide-gray-800">
                        @foreach ($cart as $row)
                            @php $isSelected = in_array($row['key'], $selectedKeys, true); @endphp
                            <div wire:key="cart-{{ $row['key'] }}"
                                 class="flex gap-4 p-4 transition {{ $isSelected ? '' : 'opacity-60' }}">

                                {{-- Checkbox --}}
                                <div class="flex items-start pt-1">
                                    <input type="checkbox"
                                           wire:click="toggleSelect('{{ $row['key'] }}')"
                                           @checked($isSelected)
                                           class="h-4 w-4 rounded border-slate-300 dark:border-gray-600 text-[#FF5E14] focus:ring-[#FF5E14] cursor-pointer" />
                                </div>

                                {{-- Image --}}
                                <img src="{{ asset('storage/' . $row['product']->ft_img) }}"
                                     alt="{{ $row['product']->name }}"
                                     class="h-20 w-20 rounded-xl object-cover border border-slate-200 dark:border-gray-700" />

                                {{-- Details --}}
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
                                        <input type="number"
                                               min="1"
                                               max="{{ $row['available_stock'] }}"
                                               value="{{ $row['quantity'] }}"
                                               wire:change="updateQuantity('{{ $row['key'] }}', $event.target.value)"
                                               class="w-16 rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-2 py-1 text-sm text-slate-900 dark:text-white" />
                                        <span class="text-xs text-slate-400 dark:text-gray-500">
                                            / {{ $row['available_stock'] }} available
                                        </span>
                                        <button wire:click="removeItem('{{ $row['key'] }}')"
                                                class="ml-2 text-xs text-red-600 dark:text-red-400 hover:underline">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                {{-- Line total --}}
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-[#FF5E14]">
                                        ₱{{ number_format($row['line_total'], 2) }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">
                                        {{ $row['quantity'] }} × ₱{{ number_format($row['unit_price'], 2) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Payment + Notes --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">
                                Payment method
                            </label>
                            <select wire:model.live="payment_method"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                                <optgroup label="Pay later">
                                    <option value="cash_on_pickup">Cash on pickup</option>
                                    <option value="bank_transfer">Bank transfer</option>
                                </optgroup>
                                <optgroup label="Pay now">
                                    <option value="paymongo_gcash">GCash (pay now)</option>
                                    <option value="paymongo_card">Card (pay now)</option>
                                    <option value="paymongo_maya">Maya (pay now)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">
                                Notes <span class="text-slate-400 font-normal">(optional)</span>
                            </label>
                            <textarea wire:model="notes" rows="3"
                                      placeholder="Any special instructions for the shop..."
                                      class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"></textarea>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Summary --}}
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-4 sticky top-24">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Order summary</h2>
                            <span class="text-xs font-medium text-slate-500 dark:text-gray-400">
                                {{ $this->totals['count'] }} of {{ $cart->count() }} selected
                            </span>
                        </div>

                        @php $totals = $this->totals; @endphp

                        @if ($totals['count'] === 0)
                            <div class="rounded-xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-4 text-center">
                                <p class="text-sm text-slate-500 dark:text-gray-400">
                                    Select items to see your total.
                                </p>
                            </div>
                        @else
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500 dark:text-gray-400">Subtotal</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">
                                        ₱{{ number_format($totals['subtotal'], 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500 dark:text-gray-400">Tax (12%)</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">
                                        ₱{{ number_format($totals['tax'], 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between border-t border-slate-200 dark:border-gray-800 pt-3">
                                    <span class="text-base font-bold text-slate-900 dark:text-white">Total</span>
                                    <span class="text-xl font-bold text-[#FF5E14]">
                                        ₱{{ number_format($totals['total'], 2) }}
                                    </span>
                                </div>
                            </div>

                            @if ($cart->count() - $totals['count'] > 0)
                                <div class="rounded-lg bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20 px-3 py-2 text-xs text-orange-700 dark:text-orange-300">
                                    {{ $cart->count() - $totals['count'] }}
                                    {{ \Illuminate\Support\Str::plural('item', $cart->count() - $totals['count']) }}
                                    will stay in your cart.
                                </div>
                            @endif
                        @endif

                        <button wire:click="placeOrder"
                                wire:loading.attr="disabled"
                                wire:target="placeOrder"
                                @disabled($totals['count'] === 0)
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="placeOrder">
                                {{ str_starts_with($payment_method, 'paymongo_') ? 'Pay now' : 'Place order' }}
                                {{ $totals['count'] > 0 ? '(' . $totals['count'] . ')' : '' }}
                            </span>
                            <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ str_starts_with($payment_method, 'paymongo_') ? 'Redirecting…' : 'Placing order…' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>