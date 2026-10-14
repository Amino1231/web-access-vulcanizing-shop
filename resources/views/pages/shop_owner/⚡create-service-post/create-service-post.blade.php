<div class="space-y-6">
    <div class="mx-auto mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Create service post</h1>
        </div>

        <a href="{{ route('owner.services') }}" wire:navigate
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700">
            View services
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
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Service details</h2>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Service name</label>
                    <input wire:model="name" type="text"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                           placeholder="e.g. Tire Mounting" />
                    @error('name') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Description</label>
                    <textarea wire:model="description" rows="5"
                              class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20"
                              placeholder="Describe the service, what's included, and any important notes."></textarea>
                    @error('description') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>

                {{-- Category --}}
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

                {{-- Duration | Price --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Duration (minutes)</label>
                        <input wire:model="duration_minutes" type="number" min="0" max="1440"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('duration_minutes') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Price</label>
                        <input wire:model="price" type="number" min="0" step="0.01"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none transition focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('price') <span class="mt-2 block text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Type + Active --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 transition hover:bg-slate-100 dark:hover:bg-gray-700">
                        <input type="checkbox" wire:model="is_vehicle_service" class="h-4 w-4 rounded border-slate-300 dark:border-gray-600 text-[#FF5E14] focus:ring-[#FF5E14]" />
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Vehicle service</p>
                            <p class="text-xs text-slate-500 dark:text-gray-400">Requires the customer's vehicle</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 transition hover:bg-slate-100 dark:hover:bg-gray-700">
                        <input type="checkbox" wire:model="is_active" class="h-4 w-4 rounded border-slate-300 dark:border-gray-600 text-emerald-500 focus:ring-emerald-500" />
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Active</p>
                            <p class="text-xs text-slate-500 dark:text-gray-400">Available for booking</p>
                        </div>
                    </label>
                </div>

                {{-- Publish status --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Publish status</label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 transition hover:bg-slate-100 dark:hover:bg-gray-700 has-[:checked]:border-[#FF5E14] has-[:checked]:bg-orange-50 dark:has-[:checked]:bg-orange-500/10">
                            <input type="radio" wire:model="status" value="published" class="h-4 w-4 text-[#FF5E14] focus:ring-[#FF5E14]" />
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Publish now</p>
                                <p class="text-xs text-slate-500 dark:text-gray-400">Visible to customers immediately</p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-3 transition hover:bg-slate-100 dark:hover:bg-gray-700 has-[:checked]:border-[#FF5E14] has-[:checked]:bg-orange-50 dark:has-[:checked]:bg-orange-500/10">
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
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Create service post</span>
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