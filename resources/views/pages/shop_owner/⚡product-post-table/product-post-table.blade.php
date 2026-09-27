
<div
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    class="space-y-6 text-slate-900 dark:text-slate-100"
>
    {{-- Flash messages --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700
                    dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700
                    dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Products</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Manage your catalog, stock, and public listings.</p>
        </div>

        <a href="{{ route('owner.product_create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] focus:outline-none focus:ring-2 focus:ring-orange-300 dark:focus:ring-orange-500/40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New product
        </a>
    </div>

    {{-- Table card --}}
    <div class="card overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between dark:border-gray-800">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="relative w-full sm:w-72">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search name, SKU, or barcode..."
                        class="inp pl-9"
                    />
                </div>

                <select wire:model.live="categoryFilter" class="inp sm:w-44">
                    <option value="">All categories</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                    @endforeach
                </select>

                <select wire:model.live="stockFilter" class="inp sm:w-40">
                    <option value="">All stock</option>
                    <option value="in">In stock</option>
                    <option value="low">Low stock</option>
                    <option value="out">Out of stock</option>
                </select>

                <select wire:model.live="statusFilter" class="inp sm:w-40">
                    <option value="">All statuses</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>

                @if ($search || $categoryFilter || $stockFilter || $statusFilter)
                    <button wire:click="clearFilters" type="button" class="btn-secondary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2 text-sm">
                <label class="text-slate-500 dark:text-gray-400">Show</label>
                <select wire:model.live="perPage" class="inp w-20 py-2">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span class="text-slate-500 dark:text-gray-400">per page</span>
            </div>
        </div>

        {{-- Table --}}
        @php $products = $this->products; @endphp

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800">
                <thead class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur dark:bg-gray-800/60">
                    <tr>
                        @php
                            $headers = [
                                ['key' => 'name',          'label' => 'Product',  'align' => 'left',   'sortable' => true],
                                ['key' => 'sku',           'label' => 'SKU',      'align' => 'left',   'sortable' => true],
                                ['key' => null,            'label' => 'Category', 'align' => 'left',   'sortable' => false],
                                ['key' => 'selling_price', 'label' => 'Price',    'align' => 'right',  'sortable' => true],
                                ['key' => 'stock',         'label' => 'Stock',    'align' => 'center', 'sortable' => true],
                                ['key' => null,            'label' => 'Variants', 'align' => 'center', 'sortable' => false],
                                ['key' => null,            'label' => 'Status',   'align' => 'left',   'sortable' => false],
                                ['key' => 'created_at',    'label' => 'Added',    'align' => 'left',   'sortable' => true],
                                ['key' => null,            'label' => 'Actions',  'align' => 'right',  'sortable' => false],
                            ];
                        @endphp

                        @foreach ($headers as $h)
                            @php
                                $align = match($h['align']) {
                                    'right'  => 'text-right',
                                    'center' => 'text-center',
                                    default  => 'text-left',
                                };
                            @endphp
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-gray-300 {{ $align }}">
                                @if ($h['sortable'])
                                    <button wire:click="sortBy('{{ $h['key'] }}')" type="button"
                                            class="inline-flex items-center gap-1 hover:text-slate-900 dark:hover:text-white
                                                   {{ $h['align'] === 'right' ? 'ml-auto' : '' }}
                                                   {{ $h['align'] === 'center' ? 'mx-auto' : '' }}">
                                        {{ $h['label'] }}
                                        @if ($sortField === $h['key'])
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if ($sortDirection === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                                @endif
                                            </svg>
                                        @endif
                                    </button>
                                @else
                                    {{ $h['label'] }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                    @forelse ($products as $product)
                        @php
                            $isLow = $product->stock > 0 && $product->stock <= $product->low_stock_alert;
                            $isOut = $product->stock <= 0;
                            $variantCount = $product->variants->count();
                            $post = $product->post;
                            $postStatus = $post?->status;
                        @endphp
                        <tr wire:key="product-row-{{ $product->id }}"
                            class="transition hover:bg-slate-50 dark:hover:bg-gray-800/50">

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="{{ $product->ft_img ? asset('storage/' . $product->ft_img) : asset('images/default-product.png') }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        class="h-12 w-12 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-gray-700"
                                    />
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $product->name }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-slate-600 dark:text-gray-300">{{ $product->sku }}</span>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $product->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">₱{{ number_format($product->selling_price, 2) }}</p>
                                <p class="text-xs text-slate-500 line-through dark:text-gray-400">₱{{ number_format($product->cost_price, 2) }}</p>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-sm font-semibold {{ $isOut ? 'text-red-600 dark:text-red-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }}">
                                        {{ $product->stock }}
                                    </span>
                                    @if ($isOut)
                                        <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-red-500">Out</span>
                                    @elseif ($isLow)
                                        <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-500">Low</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($variantCount > 0)
                                    <span class="inline-flex items-center justify-center rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                                        {{ $variantCount }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- Status — driven by post.status --}}
                            <td class="px-4 py-3">
                                @if (! $post)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:bg-gray-800 dark:text-gray-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        No post
                                    </span>
                                @elseif ($postStatus === \App\Enums\PostStatus::Published)
                                    <button wire:click="togglePublish({{ $product->id }})"
                                            title="Click to unpublish"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-700 transition hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Published
                                    </button>
                                @elseif ($postStatus === \App\Enums\PostStatus::Draft)
                                    <button wire:click="togglePublish({{ $product->id }})"
                                            title="Click to publish"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-700 transition hover:bg-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                        Draft
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-red-700 dark:bg-red-500/10 dark:text-red-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        {{ $postStatus->label() }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <p class="text-sm text-slate-700 dark:text-gray-300">{{ $product->created_at?->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-400 dark:text-gray-500">{{ $product->created_at?->format('g:i A') }}</p>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="viewProduct({{ $product->id }})" type="button" title="View"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    <a href="{{ route('owner.product_edit', $product->id) }}" title="Edit"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-orange-600 transition hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <button wire:click="confirmDelete({{ $product->id }})" type="button" title="Delete"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="mt-4 text-sm font-medium text-slate-500 dark:text-gray-400">
                                    {{ ($search || $categoryFilter || $stockFilter || $statusFilter) ? 'No products match your filters.' : 'No products yet.' }}
                                </p>
                                @if (! ($search || $categoryFilter || $stockFilter || $statusFilter))
                                    <a href="{{ route('owner.product_create') }}"
                                       class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                                        Create your first product
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        @if ($products->total() > 0)
            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Showing <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $products->firstItem() ?? 0 }}</span>
                    to <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $products->lastItem() ?? 0 }}</span>
                    of <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $products->total() }}</span> products
                </p>
                <div>{{ $products->links() }}</div>
            </div>
        @endif
    </div>

    {{-- VIEW MODAL --}}
    @if ($showViewModal && $viewingProduct)
        <div
            x-data
            x-init="document.body.classList.add('overflow-hidden')"
            x-effect="() => { return () => document.body.classList.remove('overflow-hidden') }"
            @keydown.escape.window="$wire.closeViewModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm"
            wire:key="view-modal-{{ $viewingProduct['id'] }}"
        >
            <div class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">

                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-gray-800">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Product detail</p>
                        <h2 class="mt-1 truncate text-2xl font-bold text-slate-900 dark:text-white">{{ $viewingProduct['name'] }}</h2>
                        <div class="mt-1 flex items-center gap-3">
                            <p class="font-mono text-xs text-slate-500 dark:text-gray-400">{{ $viewingProduct['sku'] }}</p>
                            @if ($viewingProduct['post_status'] === 'published')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Published
                                </span>
                            @elseif ($viewingProduct['post_status'] === 'draft')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:bg-gray-800 dark:text-gray-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span> Draft
                                </span>
                            @endif
                        </div>
                    </div>
                    <button wire:click="closeViewModal" type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto p-6">
                    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.9fr]">
                        <div class="space-y-6">
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-gray-800 dark:bg-gray-800">
                                <img src="{{ $viewingProduct['image'] }}" alt="{{ $viewingProduct['name'] }}" class="h-72 w-full object-cover sm:h-80" />
                            </div>

                            @if (count($viewingProduct['attachments']) > 0)
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                                    <h3 class="mb-4 text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Additional images</h3>
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                                        @foreach ($viewingProduct['attachments'] as $image)
                                            <a href="{{ $image }}" target="_blank" class="group overflow-hidden rounded-xl border border-slate-200 dark:border-gray-700">
                                                <img src="{{ $image }}" alt="Attachment" loading="lazy" class="h-24 w-full object-cover transition group-hover:scale-105" />
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                                <h3 class="mb-3 text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Description</h3>
                                <p class="whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-gray-400">
                                    {{ $viewingProduct['description'] ?: 'No description added for this product yet.' }}
                                </p>
                            </div>

                            @if (count($viewingVariants) > 0)
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h3 class="text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Variants</h3>
                                        <span class="rounded-full bg-orange-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">{{ count($viewingVariants) }} total</span>
                                    </div>
                                    <div class="max-h-72 space-y-2 overflow-y-auto">
                                        @foreach ($viewingVariants as $variant)
                                            <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-gray-800 dark:bg-gray-800">
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $variant['sku'] }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-gray-400">Stock: {{ $variant['stock_quantity'] }}</p>
                                                </div>
                                                <p class="text-sm font-bold text-slate-900 dark:text-white">₱{{ number_format($variant['price'], 2) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-6">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                                <h3 class="mb-4 text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Pricing &amp; stock</h3>
                                <div class="space-y-4">
                                    <div class="flex items-baseline justify-between border-b border-slate-200 pb-4 dark:border-gray-800">
                                        <span class="text-sm text-slate-500 dark:text-gray-400">Selling price</span>
                                        <span class="text-2xl font-bold text-slate-900 dark:text-white">₱{{ number_format($viewingProduct['selling_price'], 2) }}</span>
                                    </div>
                                    <div class="flex items-baseline justify-between border-b border-slate-200 pb-4 dark:border-gray-800">
                                        <span class="text-sm text-slate-500 dark:text-gray-400">Cost price</span>
                                        <span class="text-base font-semibold text-slate-700 dark:text-gray-300">₱{{ number_format($viewingProduct['cost_price'], 2) }}</span>
                                    </div>
                                    <div class="flex items-baseline justify-between border-b border-slate-200 pb-4 dark:border-gray-800">
                                        <span class="text-sm text-slate-500 dark:text-gray-400">Current stock</span>
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $viewingProduct['stock'] }} units</span>
                                    </div>
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-sm text-slate-500 dark:text-gray-400">Low stock alert</span>
                                        <span class="text-sm font-semibold text-slate-700 dark:text-gray-300">{{ $viewingProduct['low_stock_alert'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                                <h3 class="mb-4 text-sm font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Product info</h3>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500 dark:text-gray-400">SKU</dt>
                                        <dd class="truncate font-mono font-semibold text-slate-900 dark:text-white">{{ $viewingProduct['sku'] }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500 dark:text-gray-400">Barcode</dt>
                                        <dd class="truncate font-medium text-slate-900 dark:text-white">{{ $viewingProduct['barcode'] ?: '—' }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500 dark:text-gray-400">Category</dt>
                                        <dd class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-gray-800 dark:text-gray-300">{{ $viewingProduct['category'] }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500 dark:text-gray-400">Status</dt>
                                        <dd class="font-semibold text-slate-900 dark:text-white">{{ $viewingProduct['post_status_label'] }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500 dark:text-gray-400">Created</dt>
                                        <dd class="text-right font-medium text-slate-900 dark:text-white">{{ $viewingProduct['created_at'] }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500 dark:text-gray-400">Updated</dt>
                                        <dd class="text-right font-medium text-slate-900 dark:text-white">{{ $viewingProduct['updated_at'] }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/50">
                    <button wire:click="closeViewModal" type="button"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Close
                    </button>
                    <a href="{{ route('owner.product_edit', $viewingProduct['id']) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit product
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- DELETE MODAL --}}
    @if ($deletingId)
        <div
            x-data
            @keydown.escape.window="$wire.cancelDelete()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm"
            wire:key="delete-modal-{{ $deletingId }}"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Delete product?</h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                            You're about to delete <span class="font-semibold text-slate-900 dark:text-white">{{ $deletingName }}</span>. This will permanently remove the product, its variants, and all uploaded images.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cancelDelete" type="button"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="delete">Delete product</span>
                        <span wire:loading wire:target="delete" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif


    <style>
        .inp {
            @apply w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition
                   placeholder:text-slate-400
                   focus:border-orange-400 focus:ring-2 focus:ring-orange-100
                   dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500
                   dark:focus:border-orange-500/60 dark:focus:ring-orange-500/20;
        }
        .btn-secondary {
            @apply inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition
                   hover:bg-slate-50
                   dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700;
        }
        .card {
            @apply rounded-2xl border border-slate-200 bg-white shadow-sm
                   dark:border-gray-800 dark:bg-gray-900;
        }
    </style>
</div>

