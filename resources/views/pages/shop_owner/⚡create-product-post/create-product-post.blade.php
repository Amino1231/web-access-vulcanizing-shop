<div class="space-y-6">
    <div class="mx-auto mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Create product post</h1>
        </div>

        <a href="{{ route('owner.products') }}" wire:navigate
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700">
            View products
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm font-medium text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[1.5fr_0.95fr]">
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Product details</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Product name</label>
                        <input wire:model="name" type="text"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                               placeholder="e.g. Heavy Duty Tire Kit" />
                        @error('name') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Description</label>
                        <textarea wire:model="description" rows="5"
                                  class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                                  placeholder="Describe the product, specifications, and usage."></textarea>
                        @error('description') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Category | Brand | Barcode --}}
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Category</label>
                            <select wire:model="category_id"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                                <option value="">Select category</option>
                                @foreach ($this->categories as $category)
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
                                @foreach ($this->brands as $brand)
                                    <option value="{{ $brand['id'] }}">{{ $brand['name'] }}</option>
                                @endforeach
                            </select>
                            @error('brand_id') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Barcode</label>
                            <input wire:model="barcode" type="text"
                                   class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                                   placeholder="Optional barcode" />
                            @error('barcode') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Publish status --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Publish status</label>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 cursor-pointer transition hover:bg-slate-100 dark:hover:bg-gray-700 has-[:checked]:border-[#FF5E14] has-[:checked]:bg-orange-50 dark:has-[:checked]:bg-orange-500/10">
                                <input type="radio" wire:model="status" value="published" class="h-4 w-4 text-[#FF5E14] focus:ring-[#FF5E14]" />
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Publish now</p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">Visible to customers immediately</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 cursor-pointer transition hover:bg-slate-100 dark:hover:bg-gray-700 has-[:checked]:border-[#FF5E14] has-[:checked]:bg-orange-50 dark:has-[:checked]:bg-orange-500/10">
                                <input type="radio" wire:model="status" value="draft" class="h-4 w-4 text-[#FF5E14] focus:ring-[#FF5E14]" />
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Save as draft</p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">Keep hidden until you publish</p>
                                </div>
                            </label>
                        </div>
                        @error('status') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Inline category --}}
                    <div class="rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-300">Create category inline</label>
                            <button type="button" wire:click="createInlineCategory"
                                    wire:loading.attr="disabled"
                                    wire:target="createInlineCategory"
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
                        @error('newCategoryDescription') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    {{-- Inline brand --}}
                    <div class="rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-300">Create brand inline</label>
                            <button type="button" wire:click="createInlineBrand"
                                    wire:loading.attr="disabled"
                                    wire:target="createInlineBrand"
                                    class="rounded-lg bg-slate-900 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-60">
                                <span wire:loading.remove wire:target="createInlineBrand">Save new brand</span>
                                <span wire:loading wire:target="createInlineBrand">Saving…</span>
                            </button>
                        </div>
                        <input wire:model="newBrandName" type="text" placeholder="New brand name (e.g. Michelin)"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('newBrandName') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Stock</label>
                            <input wire:model="stock" type="number" min="0" @if($hasVariants) disabled @endif
                                   class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20 disabled:cursor-not-allowed disabled:opacity-60" />
                            @if($hasVariants)
                                <span class="mt-2 block text-[11px] text-slate-500 dark:text-gray-400">Auto-summed from variants</span>
                            @endif
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
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Main image</h2>
                    </div>

                    <div data-image-preview class="space-y-3">
                        <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 p-5 text-center transition hover:border-slate-400 hover:bg-slate-100 dark:hover:bg-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-8-6h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Upload main image</span>
                            <input wire:model="mainImage" type="file" accept="image/*" class="hidden" />
                        </label>

                        <div wire:loading wire:target="mainImage" class="text-xs text-slate-400">Uploading…</div>

                        @if ($mainImage)
                            <img src="{{ $mainImage->temporaryUrl() }}" alt="Main preview" class="h-44 w-full rounded-xl object-cover" />
                        @endif
                        @error('mainImage') <span class="block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Additional attachments</h2>
                        <span class="rounded-full bg-orange-50 dark:bg-orange-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-orange-700 dark:text-orange-300">Up to 5</span>
                    </div>

                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 p-5 text-center transition hover:border-slate-400 hover:bg-slate-100 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8h-16" />
                        </svg>
                        <span class="text-sm font-medium text-slate-700 dark:text-gray-300">Upload product images</span>
                        <input wire:model="attachmentImages" type="file" accept="image/*" multiple class="hidden" />
                    </label>

                    <div wire:loading wire:target="attachmentImages" class="mt-2 text-xs text-slate-400">Uploading…</div>

                    @if ($attachmentImages)
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            @foreach ($attachmentImages as $attachment)
                                <img src="{{ $attachment->temporaryUrl() }}" alt="Attachment preview" class="h-20 w-full rounded-xl object-cover" />
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

                <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 text-sm text-slate-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="hasVariants" class="h-4 w-4 rounded border-slate-300 dark:border-gray-600 text-[#FF5E14] focus:ring-[#FF5E14]" />
                    Use variants
                </label>
            </div>

            @if ($hasVariants)
                {{-- Attribute builder --}}
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-gray-400">Add up to 3 attribute groups such as Color, Size, or Material.</p>
                    <button type="button" wire:click="addVariantGroup"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:hover:bg-gray-600">
                        Add attribute
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach ($variantGroups as $groupIndex => $group)
                        <div wire:key="vg-{{ $groupIndex }}"
                             class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800 p-4">
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

                {{-- Generated combinations --}}
                @if (! empty($variantCombos))
                    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                        <div class="flex flex-col gap-2 border-b border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ count($variantCombos) }} {{ \Illuminate\Support\Str::plural('variant', count($variantCombos)) }} generated
                                </p>
                                <p class="text-xs text-slate-500 dark:text-gray-400">
                                    Set SKU, price, and quantity for each.
                                </p>
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
                                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                                    {{ $combo['label'] }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input wire:model="variantCombos.{{ $i }}.sku" type="text"
                                                       class="w-full min-w-[180px] rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 font-mono text-xs text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                                                @error("variantCombos.{$i}.sku")
                                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-4 py-3">
                                                <input wire:model="variantCombos.{{ $i }}.price" type="number" min="0" step="0.01"
                                                       class="w-28 rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                                                @error("variantCombos.{$i}.price")
                                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-4 py-3">
                                                <input wire:model.live="variantCombos.{{ $i }}.stock_quantity" type="number" min="0"
                                                       class="w-24 rounded-lg border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                                                @error("variantCombos.{$i}.stock_quantity")
                                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @error('variantCombos')
                            <div class="border-t border-red-200 bg-red-50 px-4 py-2 text-xs text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-5 text-sm text-slate-500 dark:text-gray-400">
                        Fill in attribute titles and at least one option per group to generate variants.
                    </div>
                @endif
            @else
                <div class="rounded-2xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-5 text-sm text-slate-500 dark:text-gray-400">
                    Single product stock will be used without variant attributes.
                </div>
            @endif
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save,mainImage,attachmentImages"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Create product post</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Creating…
                </span>
            </button>
        </div>
    </form>
</div>