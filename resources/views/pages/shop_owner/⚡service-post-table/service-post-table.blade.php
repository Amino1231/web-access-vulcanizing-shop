<div class="space-y-6">

    {{-- Flash messages --}}
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

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Services</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Manage your service catalog and public listings.</p>
        </div>

        <a href="{{ route('owner.service_create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] focus:outline-none focus:ring-2 focus:ring-orange-300 dark:focus:ring-orange-500/40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New service
        </a>
    </div>

    {{-- Table card --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between dark:border-gray-800">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="relative w-full sm:w-72">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Search services..."
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 pl-9 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-orange-500/60 dark:focus:ring-orange-500/20" />
                </div>

                <select wire:model.live="categoryFilter"
                        class="w-full sm:w-44 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-orange-500/60 dark:focus:ring-orange-500/20">
                    <option value="">All categories</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                    @endforeach
                </select>

                <select wire:model.live="activeFilter"
                        class="w-full sm:w-40 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-orange-500/60 dark:focus:ring-orange-500/20">
                    <option value="">All status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <select wire:model.live="statusFilter"
                        class="w-full sm:w-40 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-orange-500/60 dark:focus:ring-orange-500/20">
                    <option value="">All post statuses</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>

                @if ($search || $categoryFilter || $activeFilter || $statusFilter)
                    <button wire:click="clearFilters" type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2 text-sm">
                <label class="text-slate-500 dark:text-gray-400">Show</label>
                <select wire:model.live="perPage"
                        class="w-20 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-orange-500/60 dark:focus:ring-orange-500/20">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span class="text-slate-500 dark:text-gray-400">per page</span>
            </div>
        </div>

        {{-- Table --}}
        @php $services = $this->services; @endphp

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800">
                <thead class="sticky top-0 z-10 bg-slate-50/95 backdrop-blur dark:bg-gray-800/60">
                    <tr>
                        @php
                            $headers = [
                                ['key' => 'name',             'label' => 'Service',  'align' => 'left',   'sortable' => true],
                                ['key' => null,               'label' => 'Category', 'align' => 'left',   'sortable' => false],
                                ['key' => 'price',            'label' => 'Price',    'align' => 'right',  'sortable' => true],
                                ['key' => 'duration_minutes', 'label' => 'Duration', 'align' => 'center', 'sortable' => true],
                                ['key' => null,               'label' => 'Type',     'align' => 'center', 'sortable' => false],
                                ['key' => null,               'label' => 'Active',   'align' => 'center', 'sortable' => false],
                                ['key' => null,               'label' => 'Status',   'align' => 'left',   'sortable' => false],
                                ['key' => 'created_at',       'label' => 'Added',    'align' => 'left',   'sortable' => true],
                                ['key' => null,               'label' => 'Actions',  'align' => 'right',  'sortable' => false],
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
                    @forelse ($services as $service)
                        @php
                            $post = $service->post;
                            $postStatus = $post?->status;
                        @endphp
                        <tr wire:key="service-row-{{ $service->id }}"
                            class="transition hover:bg-slate-50 dark:hover:bg-gray-800/50">

                            <td class="px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $service->name }}</p>
                                    @if ($service->description)
                                        <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($service->description, 60) }}</p>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $service->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    ₱{{ number_format($service->price, 2) }}
                                </p>
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($service->duration_minutes)
                                    <span class="text-sm text-slate-700 dark:text-gray-300">{{ $service->duration_minutes }} min</span>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-gray-500">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if ($service->is_vehicle_service)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                        Vehicle
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:bg-purple-500/10 dark:text-purple-300">
                                        General
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleActive({{ $service->id }})"
                                        title="{{ $service->is_active ? 'Click to deactivate' : 'Click to activate' }}"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider transition
                                            {{ $service->is_active
                                                ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20'
                                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $service->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>

                            <td class="px-4 py-3">
                                @if (! $post)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:bg-gray-800 dark:text-gray-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        No post
                                    </span>
                                @elseif ($postStatus === \App\Enums\PostStatus::Published)
                                    <button wire:click="togglePublish({{ $service->id }})"
                                            title="Click to unpublish"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-700 transition hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Published
                                    </button>
                                @elseif ($postStatus === \App\Enums\PostStatus::Draft)
                                    <button wire:click="togglePublish({{ $service->id }})"
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
                                <p class="text-sm text-slate-700 dark:text-gray-300">{{ $service->created_at?->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-400 dark:text-gray-500">{{ $service->created_at?->format('g:i A') }}</p>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('owner.service_edit', $service->id) }}" title="Edit"
                                       class="flex h-8 w-8 items-center justify-center rounded-lg text-orange-600 transition hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <button wire:click="confirmDelete({{ $service->id }})" type="button" title="Delete"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-4 text-sm font-medium text-slate-500 dark:text-gray-400">
                                    {{ ($search || $categoryFilter || $activeFilter || $statusFilter) ? 'No services match your filters.' : 'No services yet.' }}
                                </p>
                                @if (! ($search || $categoryFilter || $activeFilter || $statusFilter))
                                    <a href="{{ route('owner.service_create') }}"
                                       class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                                        Create your first service
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        @if ($services->total() > 0)
            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Showing <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $services->firstItem() ?? 0 }}</span>
                    to <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $services->lastItem() ?? 0 }}</span>
                    of <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $services->total() }}</span> services
                </p>
                <div>{{ $services->links() }}</div>
            </div>
        @endif
    </div>

    {{-- DELETE MODAL --}}
    @if ($deletingId)
        <div x-data @keydown.escape.window="$wire.cancelDelete()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm"
             wire:key="delete-modal-{{ $deletingId }}">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Delete service?</h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                            You're about to delete <span class="font-semibold text-slate-900 dark:text-white">{{ $deletingName }}</span>. This will permanently remove the service and its public listing.
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
                        <span wire:loading.remove wire:target="delete">Delete service</span>
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
</div>