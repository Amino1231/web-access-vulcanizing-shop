<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#FF5E14] to-orange-400 p-8 sm:p-12 text-white shadow-xl shadow-orange-500/20">
            <div class="absolute -top-16 -right-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 -left-16 h-72 w-72 rounded-full bg-yellow-300/20 blur-3xl"></div>

            <div class="relative max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-green-400 animate-pulse"></span>
                    {{ count($this->tenants) }} shops • {{ number_format($this->products->total()) }} products available
                </span>
                <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold leading-tight">
                    Discover tire &amp; vulcanizing products <br class="hidden sm:block" />
                    from trusted local shops.
                </h1>
                <p class="mt-3 text-white/90 max-w-lg">
                    Browse, compare, and order — all from verified vulcanizing shops in one place.
                </p>
            </div>
        </div>

        {{-- Fresh arrivals --}}
        @if ($this->featured->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Fresh arrivals</h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach ($this->featured as $item)
                        <a href="{{ route('customer.product_detail', $item->product_id) }}"
                           class="group overflow-hidden rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 shadow-sm hover:shadow-lg transition">
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" loading="lazy"
                                 class="h-28 w-full object-cover transition group-hover:scale-105" />
                            <div class="p-2.5">
                                <p class="truncate text-xs font-semibold text-slate-900 dark:text-white">{{ $item->name }}</p>
                                <p class="mt-0.5 text-[11px] text-[#FF5E14] font-bold">₱{{ number_format($item->price, 2) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Toolbar --}}
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-5 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                    <div class="relative w-full sm:w-80">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Search products..."
                               class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-gray-500 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    </div>

                    <select wire:model.live="categoryFilter"
                            class="w-full sm:w-48 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                        <option value="">All categories</option>
                        @foreach ($this->categories as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="tenantFilter"
                            class="w-full sm:w-48 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                        <option value="">All shops</option>
                        @foreach ($this->tenants as $t)
                            <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>

                    @if ($search || $categoryFilter || $tenantFilter || $sort !== 'latest')
                        <button wire:click="clearFilters" type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700">
                            Clear
                        </button>
                    @endif
                </div>

                <select wire:model.live="sort"
                        class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                    <option value="latest">Newest</option>
                    <option value="price_asc">Price: Low → High</option>
                    <option value="price_desc">Price: High → Low</option>
                    <option value="name">Name A → Z</option>
                </select>
            </div>
        </div>

        {{-- Grid --}}
        @php $products = $this->products; @endphp

        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-16 text-center">
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">No products match your filters.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($products as $post)
                    <article wire:key="post-{{ $post->id }}"
                             class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-xl">
                        <a href="{{ route('customer.product_detail', $post->product_id) }}" class="relative block overflow-hidden bg-slate-100 dark:bg-gray-800">
                            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->name }}"
                                 loading="lazy"
                                 class="h-48 w-full object-cover transition duration-500 group-hover:scale-105" />
                            @if ($post->product && $post->product->stock <= 5 && $post->product->stock > 0)
                                <span class="absolute left-3 top-3 rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                    Only {{ $post->product->stock }} left
                                </span>
                            @endif
                        </a>

                        <div class="flex flex-1 flex-col p-3 sm:p-4">
                            <span class="truncate text-[11px] font-medium text-slate-500 dark:text-gray-400">
                                {{ $post->tenant?->name }}
                            </span>

                            <a href="{{ route('customer.product_detail', $post->product_id) }}"
                               class="mt-1 line-clamp-2 text-sm font-semibold text-slate-900 dark:text-white hover:text-[#FF5E14] transition">
                                {{ $post->name }}
                            </a>

                            <p class="mt-1 text-[11px] uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                {{ $post->product?->category?->name ?? 'Uncategorized' }}
                            </p>

                            <div class="mt-auto pt-3">
                                <p class="text-base sm:text-lg font-bold text-[#FF5E14]">
                                    ₱{{ number_format($post->price, 2) }}
                                </p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $products->links() }}</div>
        @endif
    </div>
</div>