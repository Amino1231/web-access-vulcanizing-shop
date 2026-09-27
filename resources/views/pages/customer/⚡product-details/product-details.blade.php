
<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">

        @php $product = $this->product; @endphp

        @if (! $product)
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-slate-500 dark:text-gray-400">This product is no longer available.</p>
                <a href="{{ route('customer.dashboard') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#D94E10] transition">
                    Back to marketplace
                </a>
            </div>
        @else
            <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-gray-300 hover:text-[#FF5E14] transition mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to marketplace
            </a>

            <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr]">
                <div class="space-y-4">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <img src="{{ asset('storage/' . $product->ft_img) }}" alt="{{ $product->name }}" class="h-96 w-full object-cover" />
                    </div>

                    @if (is_array($product->attachments) && count($product->attachments) > 0)
                        <div class="grid grid-cols-4 gap-3">
                            @foreach ($product->attachments as $path)
                                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-gray-700">
                                    <img src="{{ asset('storage/' . $path) }}" class="h-24 w-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#FF5E14]">
                            {{ $product->category?->name ?? 'Uncategorized' }}
                        </p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $product->name }}</h1>
                        <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                            Sold by <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $product->tenant?->name }}</span>
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-4">
                        <p class="text-3xl font-extrabold text-[#FF5E14]">₱{{ number_format($product->selling_price, 2) }}</p>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-gray-400">Stock</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $product->stock }} available</span>
                        </div>

                        @if ($product->variants->count() > 0)
                            <div class="pt-2">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400 mb-2">Choose variant</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($product->variants as $variant)
                                        <button type="button"
                                                wire:click="selectVariant({{ $variant->id }})"
                                                class="rounded-lg border px-3 py-2 text-xs font-semibold transition
                                                    {{ $selectedVariantId === $variant->id
                                                        ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10'
                                                        : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200' }}">
                                            {{ $variant->sku }}
                                            @if ($variant->stock_quantity <= 0)
                                                <span class="ml-1 text-red-500">(out)</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 pt-2">
                            <label class="text-sm font-medium text-slate-700 dark:text-gray-300">Qty</label>
                            <input type="number" wire:model.live="quantity" min="1"
                                   class="w-20 rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400" />
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="button" wire:click="addToCart"
                                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Add to cart
                            </button>
                            <button type="button" wire:click="buyNow"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                                Buy now
                            </button>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
                        <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400 mb-3">Description</h2>
                        <p class="text-sm leading-7 text-slate-600 dark:text-gray-400 whitespace-pre-line">
                            {{ $product->description ?: 'No description provided.' }}
                        </p>
                    </div>

                    @if ($product->variants->count() > 0)
                        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
                            <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400 mb-3">All variants</h2>
                            <div class="space-y-2">
                                @foreach ($product->variants as $variant)
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800 p-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $variant->sku }}</p>
                                            <p class="text-xs text-slate-500 dark:text-gray-400">Stock: {{ $variant->stock_quantity }}</p>
                                        </div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">₱{{ number_format($variant->price, 2) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($this->related->count() > 0)
                <div class="mt-12">
                    <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">More from {{ $product->tenant?->name }}</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($this->related as $rel)
                            <a href="{{ route('customer.product_detail', $rel->id) }}" class="group overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-lg">
                                <img src="{{ asset('storage/' . $rel->ft_img) }}" class="h-40 w-full object-cover transition group-hover:scale-105" loading="lazy" />
                                <div class="p-3">
                                    <p class="line-clamp-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $rel->name }}</p>
                                    <p class="mt-1 text-sm font-bold text-[#FF5E14]">₱{{ number_format($rel->selling_price, 2) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>