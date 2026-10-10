<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                Marketplace
            </p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                @if ($search !== '') Results for "{{ $search }}"
                @elseif ($sort === 'latest') New arrivals
                @else All products
                @endif
            </h1>
            @php $total = $this->products->total(); @endphp
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                {{ number_format($total) }} {{ \Illuminate\Support\Str::plural('product', $total) }}
                from {{ count($this->tenants) }} {{ \Illuminate\Support\Str::plural('shop', count($this->tenants)) }}
            </p>
        </div>

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
                               class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                    </div>

                    <select wire:model.live="categoryFilter"
                            class="w-full sm:w-48 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                        <option value="">All categories</option>
                        @foreach ($this->categories as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="tenantFilter"
                            class="w-full sm:w-48 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                        <option value="">All shops</option>
                        @foreach ($this->tenants as $t)
                            <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>

                    @if ($search || $categoryFilter || $tenantFilter || $sort !== 'latest')
                        <button wire:click="clearFilters" type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear
                        </button>
                    @endif
                </div>

                <select wire:model.live="sort"
                        class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
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
                <p class="text-sm font-medium text-slate-500 dark:text-gray-400">
                    {{ ($search || $categoryFilter || $tenantFilter) ? 'No products match your filters.' : 'No products available yet.' }}
                </p>
                @if ($search || $categoryFilter || $tenantFilter)
                    <button wire:click="clearFilters" type="button"
                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#D94E10]">
                        Clear filters
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($products as $post)
                    @php
                        $rating   = $post->product?->reviews_avg_rating;
                        $revCount = (int) ($post->product?->reviews_count ?? 0);
                    @endphp
                    <article wire:key="post-{{ $post->id }}"
                             class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-xl">

                        <a href="{{ route('customer.product_detail', $post->product_id) }}" wire:navigate
                           class="relative block overflow-hidden bg-slate-100 dark:bg-gray-800">
                            @if ($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->name }}" loading="lazy"
                                     class="h-48 w-full object-cover transition duration-500 group-hover:scale-105" />
                            @else
                                <div class="flex h-48 items-center justify-center bg-slate-100 dark:bg-gray-800">
                                    <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-8-6h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif

                            @php $stock = $post->product?->stock ?? 0; @endphp
                            @if ($stock <= 0)
                                <span class="absolute left-3 top-3 rounded-full bg-slate-900/80 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur">
                                    Out of stock
                                </span>
                            @elseif ($stock <= 5)
                                <span class="absolute left-3 top-3 rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                    Only {{ $stock }} left
                                </span>
                            @endif
                        </a>

                        <div class="flex flex-1 flex-col p-3 sm:p-4">
                            <span class="truncate text-[11px] font-medium text-slate-500 dark:text-gray-400">
                                {{ $post->tenant?->name }}
                            </span>

                            <a href="{{ route('customer.product_detail', $post->product_id) }}" wire:navigate
                               class="mt-1 line-clamp-2 text-sm font-semibold text-slate-900 dark:text-white transition hover:text-[#FF5E14]">
                                {{ $post->name }}
                            </a>

                            <p class="mt-1 text-[11px] uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                {{ $post->product?->category?->name ?? 'Uncategorized' }}
                            </p>

                            {{-- Rating row --}}
                            <div class="mt-2 flex items-center gap-1.5">
                                @if ($revCount > 0)
                                    <div class="flex items-center gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="h-3 w-3 {{ $i <= round((float) $rating) ? 'text-yellow-400' : 'text-slate-300 dark:text-gray-600' }}"
                                                 fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-700 dark:text-gray-300">
                                        {{ number_format((float) $rating, 1) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 dark:text-gray-500">
                                        ({{ $revCount }})
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-400 dark:text-gray-500">No reviews yet</span>
                                @endif
                            </div>

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