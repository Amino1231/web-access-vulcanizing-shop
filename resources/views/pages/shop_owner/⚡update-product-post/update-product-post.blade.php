<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner
                portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Edit product</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Update details, images, pricing, and publish
                status.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('owner.products') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 shadow-sm transition hover:bg-slate-50 dark:hover:bg-gray-700">
                Cancel
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10 px-4 py-3 text-sm font-medium text-red-700 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[1.5fr_0.95fr]">
            {{-- LEFT: details --}}
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-sm">
                <h2 class="mb-5 text-lg font-semibold text-slate-900 dark:text-white">Product details</h2>

                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Product name</label>
                        <input wire:model="name" type="text"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('name') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Description</label>
                        <textarea wire:model="description" rows="5"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"></textarea>
                        @error('description') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Category</label>
                            <select wire:model="category_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                                <option value="">Select category</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                Brand <span class="text-slate-400 font-normal">(optional)</span>
                            </label>
                            <select wire:model="brand_id"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                                <option value="">No brand</option>
                                @foreach ($brands as $brand)
                                <option value="{{ $brand['id'] }}">{{ $brand['name'] }}</option>
                                @endforeach
                            </select>
                            @error('brand_id') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Barcode</label>
                            <input wire:model="barcode" type="text"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            @error('barcode') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- SKU --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">SKU</label>
                        <div class="flex gap-2">
                            <input wire:model="sku" type="text"
                                class="flex-1 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 font-mono text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            <button type="button" wire:click="regenerateSku"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700"
                                title="Regenerate SKU from product name">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Regenerate
                            </button>
                        </div>
                        @error('sku') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Publish status --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Publish status</label>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 transition hover:bg-slate-100 dark:hover:bg-gray-700 has-[:checked]:border-[#FF5E14] has-[:checked]:bg-orange-50 dark:has-[:checked]:bg-orange-500/10">
                                <input type="radio" wire:model="status" value="published" class="h-4 w-4 text-[#FF5E14] focus:ring-[#FF5E14]" />
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Published</p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">Visible to customers</p>
                                </div>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 transition hover:bg-slate-100 dark:hover:bg-gray-700 has-[:checked]:border-[#FF5E14] has-[:checked]:bg-orange-50 dark:has-[:checked]:bg-orange-500/10">
                                <input type="radio" wire:model="status" value="draft" class="h-4 w-4 text-[#FF5E14] focus:ring-[#FF5E14]" />
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Draft</p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">Hidden from customers</p>
                                </div>
                            </label>
                        </div>
                        @error('status') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Stock</label>
                            <input wire:model="stock" type="number" min="0"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            @error('stock') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Cost price</label>
                            <input wire:model="cost_price" type="number" min="0" step="0.01"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            @error('cost_price') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Selling price</label>
                            <input wire:model="selling_price" type="number" min="0" step="0.01"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            @error('selling_price') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Low stock alert</label>
                        <input wire:model="low_stock_alert" type="number" min="0"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('low_stock_alert') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Inline category --}}
                    <div class="rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-300">Create category inline</label>
                            <button type="button" wire:click="createInlineCategory" wire:loading.attr="disabled" wire:target="createInlineCategory"
                                class="rounded-lg bg-slate-900 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-60">
                                <span wire:loading.remove wire:target="createInlineCategory">Save new category</span>
                                <span wire:loading wire:target="createInlineCategory">Saving…</span>
                            </button>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input wire:model="newCategoryName" type="text" placeholder="New category name"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            <input wire:model="newCategoryDescription" type="text" placeholder="Optional description"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        </div>
                        @error('newCategoryName') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Inline brand --}}
                    <div class="rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-300">Create brand inline</label>
                            <button type="button" wire:click="createInlineBrand" wire:loading.attr="disabled" wire:target="createInlineBrand"
                                class="rounded-lg bg-slate-900 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-60">
                                <span wire:loading.remove wire:target="createInlineBrand">Save new brand</span>
                                <span wire:loading wire:target="createInlineBrand">Saving…</span>
                            </button>
                        </div>
                        <input wire:model="newBrandName" type="text" placeholder="New brand name (e.g. Michelin)"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('newBrandName') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- RIGHT: images --}}
            <div class="space-y-6">
                {{-- Main image --}}
                <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Main image</h2>
                        @if ($existingMainImage && ! $mainImage)
                        <button type="button" wire:click="removeMainImage"
                            class="rounded-lg border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-2.5 py-1.5 text-[11px] font-semibold text-red-600 dark:text-red-400">
                            Remove
                        </button>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @if ($mainImage)
                        <img src="{{ $mainImage->temporaryUrl() }}" alt="Preview" class="h-44 w-full rounded-xl object-cover" />
                        <p class="text-xs font-medium text-orange-600 dark:text-orange-400">New image selected — will replace on save.</p>
                        @elseif ($existingMainImage)
                        <img src="{{ asset('storage/' . $existingMainImage) }}" alt="Current" class="h-44 w-full rounded-xl object-cover" />
                        @endif

                        <div wire:loading wire:target="mainImage" class="text-xs text-slate-400">Uploading…</div>

                        <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 p-5 text-center transition hover:border-slate-400 dark:hover:border-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-8-6h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium text-slate-700 dark:text-gray-300">
                                {{ $existingMainImage || $mainImage ? 'Replace main image' : 'Upload main image' }}
                            </span>
                            <input wire:model="mainImage" type="file" accept="image/*" class="hidden" />
                        </label>
                        @error('mainImage') <span class="block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Additional images</h2>
                        <span class="rounded-full bg-orange-50 dark:bg-orange-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-orange-700 dark:text-orange-300">Up to 5</span>
                    </div>

                    @if (count($existingAttachments) > 0)
                    <div class="mb-4 grid grid-cols-3 gap-3">
                        @foreach ($existingAttachments as $index => $path)
                        <div class="group relative" wire:key="att-{{ $index }}">
                            <img src="{{ asset('storage/' . $path) }}" alt="Attachment" class="h-20 w-full rounded-xl object-cover" />
                            <button type="button" wire:click="removeExistingAttachment({{ $index }})"
                                class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white shadow-md transition hover:bg-red-700"
                                title="Remove">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 p-5 text-center transition hover:border-slate-400 dark:hover:border-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8h-16" />
                        </svg>
                        <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Upload new images</span>
                        <input wire:model="attachmentImages" type="file" accept="image/*" multiple class="hidden" />
                    </label>

                    <div wire:loading wire:target="attachmentImages" class="mt-2 text-xs text-slate-400">Uploading…</div>

                    @if (count($attachmentImages) > 0)
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        @foreach ($attachmentImages as $attachment)
                        <img src="{{ $attachment->temporaryUrl() }}" alt="Preview" class="h-20 w-full rounded-xl object-cover" />
                        @endforeach
                    </div>
                    @endif
                    @error('attachmentImages') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    @error('attachmentImages.*') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- VARIANTS --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Variants</p>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Product variants</h2>
                </div>

                <button type="button" wire:click="addVariant"
                    class="inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:hover:bg-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                    </svg>
                    Add variant
                </button>
            </div>

            @if (empty($variants))
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-5 text-sm text-slate-500 dark:text-gray-400">
                No variants yet. Single product stock will be used. Click <strong class="text-slate-700 dark:text-gray-200">Add variant</strong> if this product has multiple SKUs.
            </div>
            @else
            <div class="space-y-3">
                @foreach ($variants as $index => $variant)
                <div wire:key="variant-row-{{ $index }}-{{ $variant['id'] ?? 'new' }}"
                    class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800 p-4">

                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FF5E14] text-xs font-bold text-white">
                                {{ $index + 1 }}
                            </div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $variant['id'] ? 'Existing variant' : 'New variant' }}
                            </p>
                        </div>

                        <button type="button" wire:click="removeVariant({{ $index }})"
                            wire:confirm="Remove this variant? Its stock will be permanently deleted."
                            class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition">
                            <svg class="h-3.5 w-3.5 inline" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Remove
                        </button>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-[1.5fr_1fr_1fr]">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">SKU</label>
                            <input wire:model="variants.{{ $index }}.sku" type="text" placeholder="e.g. TIRE-001-RED-S"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 font-mono text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            @error("variants.{$index}.sku") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Price</label>
                            <input wire:model="variants.{{ $index }}.price" type="number" min="0" step="0.01"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            @error("variants.{$index}.price") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Stock</label>
                            <input wire:model="variants.{{ $index }}.stock_quantity" type="number" min="0"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                            @error("variants.{$index}.stock_quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-4 rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-3 text-xs text-amber-800 dark:text-amber-300">
                <p class="font-semibold">Note:</p>
                <p class="mt-1">
                    When this product has variants, the product's stock is automatically the sum of all variant stocks.
                    Removing a variant deletes its stock permanently.
                </p>
            </div>
            @endif

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- GENERATE NEW VARIANTS FROM ATTRIBUTES --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div class="mt-6 border-t border-slate-200 dark:border-gray-800 pt-6">
                <div class="mb-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Bulk tool</p>
                    <h3 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">Generate new variants</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                        Add attributes below, then click "Add to variants" to append the generated rows to the list above.
                    </p>
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-gray-400">Up to 3 attribute groups (Color, Size, Material...).</p>
                    <button type="button" wire:click="addVariantGroup"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:hover:bg-gray-600">
                        Add attribute
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach ($variantGroups as $groupIndex => $group)
                    <div wire:key="vg-{{ $groupIndex }}" class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800 p-4">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div class="flex-1">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Attribute title</label>
                                <input wire:model.live.debounce.400ms="variantGroups.{{ $groupIndex }}.title" type="text"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                                    placeholder="Color" />
                            </div>
                            @if (count($variantGroups) > 1)
                            <button type="button" wire:click="removeVariantGroup({{ $groupIndex }})"
                                class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-600 dark:text-red-400">
                                Remove
                            </button>
                            @endif
                        </div>

                        <div class="space-y-3">
                            @foreach ($group['options'] as $optionIndex => $option)
                            <div class="flex items-center gap-2" wire:key="opt-{{ $groupIndex }}-{{ $optionIndex }}">
                                <input wire:model.live.debounce.400ms="variantGroups.{{ $groupIndex }}.options.{{ $optionIndex }}" type="text"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                                    placeholder="Enter option value" />
                                @if (count($group['options']) > 1)
                                <button type="button" wire:click="removeOption({{ $groupIndex }}, {{ $optionIndex }})"
                                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2 py-2 text-xs font-semibold text-slate-600 dark:text-gray-300">
                                    Delete
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-3 flex justify-end">
                            <button type="button" wire:click="addOption({{ $groupIndex }})"
                                class="rounded-lg bg-white dark:bg-gray-900 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-gray-300 border border-slate-200 dark:border-gray-700">
                                Add option
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if (! empty($variantCombos))
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <div class="flex flex-col gap-2 border-b border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ count($variantCombos) }} {{ \Illuminate\Support\Str::plural('variant', count($variantCombos)) }} ready
                                </p>
                                <p class="text-xs text-slate-500 dark:text-gray-400">Edit SKU, price, qty before adding.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-orange-50 dark:bg-orange-500/10 px-3 py-1.5 text-xs font-bold text-orange-700 dark:text-orange-300">
                                    Total qty: {{ $this->combosTotalQty }}
                                </div>
                                <button type="button" wire:click="regenerateAllVariantSkus"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Regenerate SKUs
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                                <thead class="bg-slate-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Variant</th>
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">SKU</th>
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Price</th>
                                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Qty</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                    @foreach ($variantCombos as $i => $combo)
                                    <tr wire:key="combo-{{ $i }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">
                                        <td class="px-4 py-3">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $combo['label'] }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input wire:model="variantCombos.{{ $i }}.sku" type="text"
                                                class="w-full min-w-[180px] rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 font-mono text-xs text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input wire:model="variantCombos.{{ $i }}.price" type="number" min="0" step="0.01"
                                                class="w-28 rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input wire:model.live="variantCombos.{{ $i }}.stock_quantity" type="number" min="0"
                                                class="w-24 rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/50 px-4 py-3">
                            <button type="button" wire:click="addCombosToVariants"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                </svg>
                                Add {{ count($variantCombos) }} {{ \Illuminate\Support\Str::plural('variant', count($variantCombos)) }} to list
                            </button>
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-4 text-xs text-slate-500 dark:text-gray-400">
                        Fill in attribute titles and at least one option per group to preview variants.
                    </div>
                @endif
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('owner.products') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-gray-200 shadow-sm transition hover:bg-slate-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save,mainImage,attachmentImages"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Saving…
                </span>
            </button>
        </div>
    </form>
</div>