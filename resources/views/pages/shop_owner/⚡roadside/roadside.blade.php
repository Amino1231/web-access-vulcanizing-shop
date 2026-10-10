<div class="space-y-6">

    {{-- Header --}}
    <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Operations</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Roadside requests</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
            Customers requesting on-site assistance. Accept, assign an employee, and track progress.
        </p>
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

    {{-- Tabs --}}
    @php $counts = $this->counts; @endphp
    <div class="flex flex-wrap gap-2">
        @php
        $tabs = [
            ['key' => 'pending',   'label' => 'Incoming',    'count' => $counts['pending']],
            ['key' => 'active',    'label' => 'In progress', 'count' => $counts['active']],
            ['key' => 'completed', 'label' => 'Completed',   'count' => $counts['completed']],
            ['key' => 'all',       'label' => 'All',         'count' => $counts['all']],
        ];
        @endphp
        @foreach ($tabs as $tab)
        <button wire:click="$set('statusFilter', '{{ $tab['key'] }}')" type="button"
            class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                {{ $statusFilter === $tab['key']
                    ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10 dark:border-orange-500/40'
                    : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">
            {{ $tab['label'] }}
            <span class="rounded-full bg-slate-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-gray-300">
                {{ $tab['count'] }}
            </span>
        </button>
        @endforeach
    </div>

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
                    placeholder="Search by customer, plate, address, or request #..."
                    class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
            </div>

            @if ($search)
            <button wire:click="clearFilters" type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50">
                Clear
            </button>
            @endif
        </div>
    </div>

    {{-- Cards --}}
    @if ($this->requests->isEmpty())
    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
        <p class="text-sm text-slate-500 dark:text-gray-400">No requests in this view.</p>
    </div>
    @else
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($this->requests as $req)
            @php
                $assignment = $req->assignments->first();
                $statusKey  = $req->status; // pending | accepted | completed
                $statusBadge = match ($statusKey) {
                    'completed' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                    'accepted'  => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
                    default     => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                };
                $statusLabel = match ($statusKey) {
                    'completed' => 'Completed',
                    'accepted'  => 'In progress',
                    default     => 'New request',
                };
                $assignmentBadge = $assignment ? match ($assignment->status) {
                    'completed' => ['Assigned · Arrived · Completed', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'],
                    'arrived'   => ['Assigned · Arrived', 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300'],
                    default     => ['Assigned', 'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300'],
                } : null;
            @endphp

            <article wire:key="rsr-{{ $req->id }}"
                class="flex flex-col rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">

                {{-- Top row: number + status --}}
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-mono text-xs text-slate-500 dark:text-gray-400">{{ $req->request_number }}</p>
                        <p class="mt-0.5 text-[11px] text-slate-500 dark:text-gray-400">
                            {{ $req->requested_at?->diffForHumans() }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusBadge }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Problem --}}
                <div class="mb-3">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $req->problem_type ?? 'Roadside assistance' }}
                    </p>
                    @if ($req->description)
                    <p class="mt-1 line-clamp-2 text-xs text-slate-600 dark:text-gray-400">
                        {{ $req->description }}
                    </p>
                    @endif
                </div>

                {{-- Customer --}}
                <div class="mb-3 rounded-xl border border-slate-100 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $req->customer?->name ?? '—' }}
                    </p>
                    @if ($req->customer?->phone)
                    <a href="tel:{{ $req->customer->phone }}"
                        class="mt-0.5 inline-flex items-center gap-1 text-xs font-medium text-[#FF5E14] hover:underline">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $req->customer->phone }}
                    </a>
                    @endif
                </div>

                {{-- Location --}}
                <div class="mb-3 rounded-xl border border-slate-100 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Location</p>
                    <p class="mt-1 text-xs text-slate-700 dark:text-gray-300">
                        {{ $req->address ?: 'Pinned on map' }}
                    </p>
                    <a href="https://www.google.com/maps?q={{ $req->latitude }},{{ $req->longitude }}" target="_blank"
                        class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-[#FF5E14] hover:underline">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Open in Google Maps
                    </a>
                </div>

                {{-- Vehicle --}}
                @if ($req->vehicle)
                <div class="mb-3 text-xs text-slate-600 dark:text-gray-400">
                    <span class="font-semibold text-slate-800 dark:text-gray-200">
                        {{ $req->vehicle->plate_number ?? '—' }}
                    </span>
                    · {{ trim(($req->vehicle->brand ?? '') . ' ' . ($req->vehicle->model ?? '')) ?: '—' }}
                </div>
                @endif

                {{-- Employee / assignment status --}}
                @if ($assignment)
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 dark:bg-gray-800 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300">
                        {{ $assignment->employee?->name ?? 'Assigned' }}
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $assignmentBadge[1] }}">
                        {{ $assignmentBadge[0] }}
                    </span>
                </div>
                @endif

                {{-- Actions --}}
                <div class="mt-auto flex flex-wrap items-center gap-2 pt-2">
                    @if ($statusKey === 'pending')
                        <button wire:click="openAccept({{ $req->id }})" type="button"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Accept
                        </button>
                    @elseif ($statusKey === 'accepted')
                        @if ($assignment && $assignment->status === 'assigned')
                            <button wire:click="confirmAction({{ $req->id }}, 'arrived')" type="button"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Mark arrived
                            </button>
                        @elseif ($assignment && $assignment->status === 'arrived')
                            <button wire:click="confirmAction({{ $req->id }}, 'completed')" type="button"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Complete
                            </button>
                        @endif
                    @elseif ($statusKey === 'completed')
                        <div class="flex-1 text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            ✓ Completed {{ $req->completed_at?->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    @if ($this->requests->total() > 0)
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500 dark:text-gray-400">
            Showing {{ $this->requests->firstItem() }}–{{ $this->requests->lastItem() }} of {{ $this->requests->total() }}
        </p>
        <div>{{ $this->requests->links() }}</div>
    </div>
    @endif
    @endif

    {{-- Accept modal --}}
    @if ($acceptingId)
    <div x-data @keydown.escape.window="$wire.cancelAccept()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Accept this request?</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                        Assign an employee to <span class="font-semibold text-slate-900 dark:text-white">{{ $acceptingNumber }}</span>.
                    </p>

                    <div class="mt-4">
                        <label for="employee" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Assign to
                        </label>
                        @if ($this->employees->isEmpty())
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-700">
                                No active employees. Add one in the Employees page first.
                            </div>
                        @else
                            <select id="employee" wire:model="selectedEmployeeId"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                                @foreach ($this->employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position }}</option>
                                @endforeach
                            </select>
                            @error('selectedEmployeeId')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelAccept" type="button"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="confirmAccept" wire:loading.attr="disabled" wire:target="confirmAccept" type="button"
                    @disabled($this->employees->isEmpty())
                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="confirmAccept">Accept & assign</span>
                    <span wire:loading wire:target="confirmAccept">Working…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Confirm modal (arrived / completed) --}}
    @if ($confirmingId && $confirmingAction)
    @php
        $title = match ($confirmingAction) {
            'arrived'   => 'Mark employee as arrived?',
            'completed' => 'Mark this request as completed?',
            default     => 'Confirm action',
        };
        $message = match ($confirmingAction) {
            'arrived'   => 'The customer will see that help has arrived on site.',
            'completed' => 'The request will be closed out and moved to Completed.',
            default     => '',
        };
    @endphp
    <div x-data @keydown.escape.window="$wire.cancelConfirmation()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $confirmingLabel }}</span> — {{ $message }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelConfirmation" type="button"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="performAction" wire:loading.attr="disabled" wire:target="performAction" type="button"
                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="performAction">Confirm</span>
                    <span wire:loading wire:target="performAction">Working…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>