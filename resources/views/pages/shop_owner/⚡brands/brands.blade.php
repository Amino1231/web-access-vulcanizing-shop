<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Catalog</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Brands</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Organize your products by brand. Optional — you can list products without a brand.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
            </svg>
            Add brand
        </button>
    </div>

    @if (session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search by brand name or slug..."
                    class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
            </div>

            @if ($search)
            <button wire:click="clearFilters" type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                Clear
            </button>
            @endif

            <div class="shrink-0 rounded-lg bg-slate-100 dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300">
                {{ $this->counts['all'] }} {{ \Illuminate\Support\Str::plural('brand', $this->counts['all']) }}
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Brand</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Slug</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Products</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($this->brands as $brand)
                    <tr wire:key="brand-{{ $brand->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-gray-700">
                                    {{ strtoupper(substr($brand->brand_name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $brand->brand_name }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <code class="rounded bg-slate-100 dark:bg-gray-800 px-2 py-0.5 text-xs text-slate-700 dark:text-gray-300">
                                {{ $brand->brand_slug }}
                            </code>
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                            @if ($brand->products_count > 0)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                    {{ $brand->products_count }} {{ \Illuminate\Support\Str::plural('product', $brand->products_count) }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400 dark:text-gray-500">No products</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">

                                {{-- Edit --}}
                                <button wire:click="openEdit({{ $brand->id }})" type="button" title="Edit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <button wire:click="confirmDelete({{ $brand->id }})" type="button" title="Delete"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 dark:hover:bg-red-500/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">No brands yet</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                                Brands help customers filter your products. Add your first one.
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->brands->total() > 0)
        <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500 dark:text-gray-400">
                Showing {{ $this->brands->firstItem() }}–{{ $this->brands->lastItem() }} of {{ $this->brands->total() }}
            </p>
            <div>{{ $this->brands->links() }}</div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Add / Edit modal --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($showForm)
    <div x-data @keydown.escape.window="$wire.closeForm()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">

        <div class="flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        {{ $editingId ? 'Edit brand' : 'New brand' }}
                    </p>
                    <h3 class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">
                        {{ $editingId ? 'Update brand' : 'Add a brand' }}
                    </h3>
                </div>
                <button wire:click="closeForm" type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <form wire:submit="save" class="overflow-y-auto p-6 space-y-4">

                @error('general')
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                @enderror

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                        Brand name <span class="text-red-500">*</span>
                    </label>
                    <input wire:model.live.debounce.400ms="brandName" type="text" required
                           placeholder="e.g. Michelin"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                    @error('brandName') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="brandSlug" type="text" required
                           placeholder="e.g. michelin"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 font-mono text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                    <p class="mt-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                        Auto-fills from the name. Used in URLs — lowercase, hyphens only.
                    </p>
                    @error('brandSlug') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </form>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                <button wire:click="closeForm" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Add brand' }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Delete confirmation modal --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($confirmingDeleteId)
    <div x-data @keydown.escape.window="$wire.cancelDelete()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">

        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Remove {{ $confirmingDeleteName }}?</h3>

                    @if ($confirmingDeleteProductCount > 0)
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                            This brand is assigned to
                            <span class="font-semibold text-slate-900 dark:text-white">
                                {{ $confirmingDeleteProductCount }}
                                {{ \Illuminate\Support\Str::plural('product', $confirmingDeleteProductCount) }}
                            </span>.
                            Those products will remain — they'll just lose their brand label.
                        </p>
                    @else
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                            No products are using this brand. Safe to delete.
                        </p>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelDelete" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="deleteBrand" wire:loading.attr="disabled" wire:target="deleteBrand" type="button"
                        class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="deleteBrand">Remove</span>
                    <span wire:loading wire:target="deleteBrand">Removing…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>