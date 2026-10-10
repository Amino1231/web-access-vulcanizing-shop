<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">Trust &amp; safety</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Reviews</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Moderate every review on the platform — hide spam, flag abuse, keep quality up.
            </p>
        </div>
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

    {{-- Tabs --}}
    @php $counts = $this->counts; @endphp
    <div class="flex flex-wrap gap-2">
        @php
            $tabs = [
                ['key' => 'all',     'label' => 'All',         'count' => $counts['all']],
                ['key' => 'recent',  'label' => 'Recent (7d)', 'count' => $counts['recent']],
                ['key' => 'low',     'label' => 'Low rated',   'count' => $counts['low']],
                ['key' => 'flagged', 'label' => 'Flagged',     'count' => $counts['flagged']],
                ['key' => 'hidden',  'label' => 'Hidden',      'count' => $counts['hidden']],
            ];
        @endphp
        @foreach ($tabs as $t)
            <button wire:click="$set('tab', '{{ $t['key'] }}')" type="button"
                class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                    {{ $tab === $t['key']
                        ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10 dark:border-orange-500/40'
                        : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">
                {{ $t['label'] }}
                <span class="rounded-full bg-slate-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-gray-300">
                    {{ $t['count'] }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Review text, reviewer, or shop..."
                       class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
            </div>

            <select wire:model.live="tenantFilter"
                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                <option value="">All shops</option>
                @foreach ($this->tenants as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>

            <input wire:model.live="from" type="date"
                   class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />

            <input wire:model.live="to" type="date"
                   class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />

            @if ($search || $tab !== 'all' || $tenantFilter || $from || $to)
                <button wire:click="clearFilters" type="button"
                        class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                    Clear
                </button>
            @endif
        </div>
    </div>

    @php $reviews = $this->reviews; @endphp

    {{-- Mobile cards --}}
    <div class="space-y-3 md:hidden">
        @forelse ($reviews as $r)
            <div wire:key="m-review-{{ $r->id }}"
                 class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm {{ $r->is_hidden ? 'opacity-60' : '' }}">

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-3.5 w-3.5 {{ $this->starClass($i, (int) $r->rating) }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <p class="mt-1.5 truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $r->customer?->name ?? 'Anonymous' }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $r->tenant?->name ?? '—' }}</p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-1">
                        @if ($r->is_hidden)
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-gray-800 dark:text-gray-400">Hidden</span>
                        @endif
                        @if ($r->flagged_at)
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700">Flagged</span>
                        @endif
                    </div>
                </div>

                @if ($r->review)
                    <p class="mt-2 line-clamp-2 text-xs text-slate-600 dark:text-gray-400">{{ $r->review }}</p>
                @endif

                <div class="mt-3 flex gap-2">
                    <button wire:click="viewReview({{ $r->id }})" type="button"
                            class="flex-1 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200">
                        View
                    </button>
                    <button wire:click="toggleFlag({{ $r->id }})" type="button"
                            class="flex-1 rounded-lg border px-3 py-2 text-xs font-semibold {{ $r->flagged_at
                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                : 'border-slate-200 bg-white text-slate-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                        {{ $r->flagged_at ? 'Unflag' : 'Flag' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-sm text-slate-500 dark:text-gray-400">No reviews found.</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Rating</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Review</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Reviewer</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Shop</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Subject</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Flags</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Date</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($reviews as $r)
                        <tr wire:key="review-{{ $r->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40 {{ $r->is_hidden ? 'opacity-50' : '' }}">

                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="h-3.5 w-3.5 {{ $this->starClass($i, (int) $r->rating) }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                            </td>

                            <td class="px-4 py-3 max-w-xs">
                                <p class="line-clamp-2 text-sm text-slate-700 dark:text-gray-300">
                                    {{ $r->review ?: '—' }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $r->customer?->name ?? 'Anonymous' }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $r->customer?->email ?? '' }}</p>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($r->tenant?->logo)
                                        <img src="{{ asset('storage/'.$r->tenant->logo) }}" alt=""
                                             class="h-7 w-7 rounded-md object-cover border border-slate-200" />
                                    @endif
                                    <span class="truncate text-sm text-slate-900 dark:text-white">{{ $r->tenant?->name ?? '—' }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-xs text-slate-500 dark:text-gray-400">
                                {{ $r->subjectLabel() }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if ($r->is_hidden)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-gray-800 dark:text-gray-400">Hidden</span>
                                    @endif
                                    @if ($r->flagged_at)
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700">Flagged</span>
                                    @endif
                                    @if (! $r->is_hidden && ! $r->flagged_at)
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs text-slate-500 dark:text-gray-400">
                                {{ $r->created_at->format('M d, Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- View --}}
                                    <button wire:click="viewReview({{ $r->id }})" type="button" title="View"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    {{-- Flag --}}
                                    <button wire:click="toggleFlag({{ $r->id }})" type="button"
                                            title="{{ $r->flagged_at ? 'Remove flag' : 'Flag for follow-up' }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg transition
                                                {{ $r->flagged_at
                                                    ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10'
                                                    : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-700' }}">
                                        <svg class="h-4 w-4" fill="{{ $r->flagged_at ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                        </svg>
                                    </button>

                                    {{-- Hide / Unhide --}}
                                    <button wire:click="confirmHide({{ $r->id }})" type="button"
                                            title="{{ $r->is_hidden ? 'Unhide' : 'Hide from public' }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg transition
                                                {{ $r->is_hidden
                                                    ? 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10'
                                                    : 'text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                                        @if ($r->is_hidden)
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Delete --}}
                                    <button wire:click="confirmDelete({{ $r->id }})" type="button" title="Delete"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.575 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">No reviews found</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">Try a different filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reviews->total() > 0)
            <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Showing {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} of {{ $reviews->total() }}
                </p>
                <div>{{ $reviews->links() }}</div>
            </div>
        @endif
    </div>

    {{-- Mobile pagination --}}
    <div class="md:hidden">
        {{ $reviews->links() }}
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Confirmation modal --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if ($confirmingId && $confirmingAction)
        @php
            $isDanger = $confirmingAction === 'delete';
            $title = match ($confirmingAction) {
                'hide'   => 'Hide this review?',
                'unhide' => 'Make this review public again?',
                'delete' => 'Delete this review permanently?',
                default  => 'Confirm action',
            };
            $message = match ($confirmingAction) {
                'hide'   => 'Customers will no longer see it. The shop owner and reviewer can still be notified separately.',
                'unhide' => 'It will be visible to customers again.',
                'delete' => 'This cannot be undone. The review will be removed from the database.',
                default  => '',
            };
        @endphp
        <div x-data @keydown.escape.window="$wire.cancelAction()"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $isDanger ? 'bg-red-100 dark:bg-red-500/20' : 'bg-amber-100 dark:bg-amber-500/20' }}">
                        <svg class="h-6 w-6 {{ $isDanger ? 'text-red-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">{{ $message }}</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cancelAction" type="button"
                            class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="performAction" wire:loading.attr="disabled" wire:target="performAction" type="button"
                            class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm disabled:opacity-60
                                {{ $isDanger ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-600 hover:bg-amber-700' }}">
                        <span wire:loading.remove wire:target="performAction">Confirm</span>
                        <span wire:loading wire:target="performAction">Working…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- Detail modal --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if ($viewingId && $viewing)
        @php $v = $viewing; @endphp
        <div x-data @keydown.escape.window="$wire.closeModal()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
            <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Review detail</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ $v['customer']['name'] }}</h2>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $this->starClass($i, $v['rating']) }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            @if ($v['is_hidden'])
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-gray-800 dark:text-gray-400">Hidden</span>
                            @endif
                            @if ($v['flagged_at'])
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700">Flagged</span>
                            @endif
                            <span class="text-xs text-slate-500 dark:text-gray-400">{{ $v['created_at'] }}</span>
                        </div>
                    </div>
                    <button wire:click="closeModal" type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="overflow-y-auto p-6 space-y-4">

                    {{-- Review text --}}
                    <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                        <p class="text-sm leading-relaxed text-slate-700 dark:text-gray-300">
                            {{ $v['review'] ?: 'No text provided.' }}
                        </p>
                    </div>

                    {{-- Grid info --}}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Shop</p>
                            <div class="mt-1 flex items-center gap-2">
                                @if ($v['tenant']['logo'])
                                    <img src="{{ $v['tenant']['logo'] }}" alt="" class="h-7 w-7 rounded-md object-cover" />
                                @endif
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $v['tenant']['name'] ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Kind / Subject</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $v['kind'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-gray-400">{{ $v['subject'] }}</p>
                        </div>
                    </div>

                    {{-- Reviewer --}}
                    <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Reviewer</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $v['customer']['name'] }}</p>
                        @if ($v['customer']['email'])
                            <p class="text-xs text-slate-500 dark:text-gray-400">✉ {{ $v['customer']['email'] }}</p>
                        @endif
                        @if ($v['customer']['phone'])
                            <p class="text-xs text-slate-500 dark:text-gray-400">📞 {{ $v['customer']['phone'] }}</p>
                        @endif
                    </div>

                    {{-- Meta --}}
                    <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4 space-y-2 text-xs">
                        @if ($v['flagged_at'])
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Flagged at</span>
                                <span class="font-medium text-amber-700 dark:text-amber-400">{{ $v['flagged_at'] }}</span>
                            </div>
                        @endif
                        @if ($v['hidden_at'])
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Hidden at</span>
                                <span class="font-medium text-slate-700 dark:text-gray-300">{{ $v['hidden_at'] }}</span>
                            </div>
                        @endif
                        @if ($v['sale'])
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-gray-400">Sale invoice</span>
                                <span class="font-mono font-medium text-slate-700 dark:text-gray-300">{{ $v['sale']['invoice_number'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                    <button wire:click="closeModal" type="button"
                            class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-700">
                        Close
                    </button>

                    <button wire:click="toggleFlag({{ $v['id'] }})" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700">
                        {{ $v['flagged_at'] ? 'Unflag' : 'Flag' }}
                    </button>

                    <button wire:click="confirmHide({{ $v['id'] }})" type="button"
                            class="inline-flex items-center gap-2 rounded-xl {{ $v['is_hidden'] ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-700 hover:bg-slate-800' }} px-4 py-2.5 text-sm font-semibold text-white shadow-sm">
                        {{ $v['is_hidden'] ? 'Unhide' : 'Hide' }}
                    </button>

                    <button wire:click="confirmDelete({{ $v['id'] }})" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>