<div class="space-y-6 w-full min-w-0 max-w-full">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">Catalog</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Service category management</h1>
        </div>

        <a href="{{ route('owner.service_create') }}" wire:navigate
           class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:w-auto">
            Create service
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 min-w-0 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.4fr)]">
        {{-- Add category --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm min-w-0 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Add category</h2>

            <form wire:submit="save" class="space-y-4">
                <div class="min-w-0">
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Category name</label>
                    <input wire:model="name" id="name" type="text"
                           class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100"
                           placeholder="e.g. Tire Services" />
                    @error('name') <span class="mt-2 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="min-w-0">
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                    <textarea wire:model="description" id="description" rows="4"
                              class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100"
                              placeholder="Category description"></textarea>
                    @error('description') <span class="mt-2 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-400 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 transition hover:from-orange-600 hover:to-orange-500 disabled:opacity-60 sm:w-auto">
                    <span wire:loading.remove wire:target="save">Save category</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Saving…
                    </span>
                </button>
            </form>
        </div>

        {{-- Categories list --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm min-w-0 sm:p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Categories</h2>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-600">
                    {{ count($this->categories) }}
                </span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3">Name</th>
                                <th class="whitespace-nowrap px-4 py-3">Slug</th>
                                <th class="whitespace-nowrap px-4 py-3">Description</th>
                                <th class="whitespace-nowrap px-4 py-3">Status</th>
                                <th class="whitespace-nowrap px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($this->categories as $category)
                                <tr wire:key="cat-{{ $category['id'] }}">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $category['name'] }}</td>
                                    <td class="px-4 py-3 text-slate-600">/{{ $category['slug'] }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $category['description'] ?: 'No description' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block whitespace-nowrap rounded-full bg-orange-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-orange-700">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="openEdit({{ $category['id'] }})" type="button" title="Edit"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button wire:click="confirmDelete({{ $category['id'] }})" type="button" title="Delete"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                        No categories created yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit modal --}}
    @if ($showEditModal)
        <div x-data @keydown.escape.window="$wire.closeEdit()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
            <div class="flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Edit category</p>
                        <h3 class="mt-0.5 text-lg font-bold text-slate-900">Update details</h3>
                    </div>
                    <button wire:click="closeEdit" type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveEdit" class="min-h-0 flex-1 overflow-y-auto p-6 space-y-4">
                    <div class="min-w-0">
                        <label for="edit-name" class="mb-2 block text-sm font-medium text-slate-700">Category name</label>
                        <input wire:model="editingName" id="edit-name" type="text"
                               class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100" />
                        @error('editingName') <span class="mt-2 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="min-w-0">
                        <label for="edit-description" class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                        <textarea wire:model="editingDescription" id="edit-description" rows="4"
                                  class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100"></textarea>
                        @error('editingDescription') <span class="mt-2 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                    </div>
                </form>

                <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button wire:click="closeEdit" type="button"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveEdit">Save changes</span>
                        <span wire:loading wire:target="saveEdit">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($confirmingDeleteId)
        <div x-data @keydown.escape.window="$wire.cancelDelete()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-slate-900">Remove {{ $confirmingDeleteName }}?</h3>
                        <p class="mt-1.5 text-sm text-slate-600">
                            This category will be permanently deleted. Categories that still have services assigned cannot be removed.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cancelDelete" type="button"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete" type="button"
                            class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="delete">Remove</span>
                        <span wire:loading wire:target="delete">Removing…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>