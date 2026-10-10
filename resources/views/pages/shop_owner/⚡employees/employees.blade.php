<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Team</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Employees</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Manage your staff — assign them to roadside jobs and appointments.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
            class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
            </svg>
            Add employee
        </button>
    </div>

    @if (session('success'))
    <div
        class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div
        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- Tabs --}}
    @php $counts = $this->counts; @endphp
    <div class="flex flex-wrap gap-2">
        @php
        $tabs = [
        ['key' => 'active', 'label' => 'Active', 'count' => $counts['active']],
        ['key' => 'inactive', 'label' => 'Inactive', 'count' => $counts['inactive']],
        ['key' => 'all', 'label' => 'All', 'count' => $counts['all']],
        ];
        @endphp
        @foreach ($tabs as $tab)
        <button wire:click="$set('statusFilter', '{{ $tab['key'] }}')" type="button"
            class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                {{ $statusFilter === $tab['key']
                    ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10 dark:border-orange-500/40'
                    : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">
            {{ $tab['label'] }}
            <span
                class="rounded-full bg-slate-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-gray-300">
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
                    placeholder="Search by name, position, or phone..."
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

    {{-- Table --}}
    <div
        class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Employee</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Position</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Phone</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Commission</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Status</th>
                        <th
                            class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($this->employees as $emp)
                    <tr wire:key="emp-{{ $emp->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-gray-700">
                                    {{ strtoupper(substr($emp->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{
                                        $emp->name }}</p>
                                    @if ($emp->hired_at)
                                    <p class="truncate text-xs text-slate-500 dark:text-gray-400">
                                        Since {{ $emp->hired_at->format('M Y') }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                            {{ $emp->position }}
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                            @if ($emp->phone)
                            <a href="tel:{{ $emp->phone }}" class="text-[#FF5E14] hover:underline">
                                {{ $emp->phone }}
                            </a>
                            @else
                            —
                            @endif
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                            {{ number_format((float) $emp->commission_rate, 2) }}%
                        </td>

                        <td class="px-4 py-3">
                            @if ($emp->is_active)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Active
                            </span>
                            @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-gray-800 dark:text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                Inactive
                            </span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">

                                {{-- Schedule --}}
                                <button wire:click="openSchedule({{ $emp->id }})" type="button" title="Edit schedule"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-500/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                {{-- Edit --}}
                                <button wire:click="openEdit({{ $emp->id }})" type="button" title="Edit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                {{-- Toggle active --}}
                                <button wire:click="toggleActive({{ $emp->id }})" type="button"
                                    title="{{ $emp->is_active ? 'Deactivate' : 'Reactivate' }}" class="flex h-8 w-8 items-center justify-center rounded-lg transition
                                        {{ $emp->is_active
                                            ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10'
                                            : 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10' }}">
                                    @if ($emp->is_active)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @else
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @endif
                                </button>

                                {{-- Delete --}}
                                <button wire:click="confirmDelete({{ $emp->id }})" type="button" title="Delete"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 dark:hover:bg-red-500/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none"
                                stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a2 2 0 002-2v-1a6 6 0 00-9.33-5A6 6 0 004 17v1a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">No employees yet</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">Add your first staff member to
                                start assigning them to jobs.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->employees->total() > 0)
        <div
            class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500 dark:text-gray-400">
                Showing {{ $this->employees->firstItem() }}–{{ $this->employees->lastItem() }} of {{
                $this->employees->total() }}
            </p>
            <div>{{ $this->employees->links() }}</div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Add / Edit modal --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($showForm)
    <div x-data @keydown.escape.window="$wire.closeForm()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">

        <div
            class="flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

            {{-- Header --}}
            <div
                class="flex items-center justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        {{ $editingId ? 'Edit employee' : 'New employee' }}
                    </p>
                    <h3 class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">
                        {{ $editingId ? 'Update details' : 'Add to your team' }}
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
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}
                </div>
                @enderror

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                        Full name <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="name" type="text" required placeholder="e.g. Maria Santos"
                        class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                    @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                        Position <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="position" type="text" required placeholder="e.g. Senior Technician"
                        class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                    @error('position') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                        Phone <span class="text-slate-400">(optional)</span>
                    </label>
                    <input wire:model="phone" type="text" placeholder="+63 912 345 6789"
                        class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                    @error('phone') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Commission %
                        </label>
                        <input wire:model="commissionRate" type="number" step="0.01" min="0" max="100"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('commissionRate') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Hired on
                        </label>
                        <input wire:model="hiredAt" type="date"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('hiredAt') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="inline-flex cursor-pointer items-center gap-2.5">
                        <input wire:model="isActive" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" />
                        <span class="text-sm font-medium text-slate-700 dark:text-gray-300">
                            Active — available for assignment
                        </span>
                    </label>
                </div>
            </form>

            {{-- Footer --}}
            <div
                class="flex justify-end gap-3 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                <button wire:click="closeForm" type="button"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save" type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Add employee'
                        }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Schedule modal --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($showSchedule)
    @php
    $dayLabels = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday',
    ];
    @endphp
    <div x-data @keydown.escape.window="$wire.closeSchedule()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">

        <div
            class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl">

            {{-- Header --}}
            <div
                class="flex items-center justify-between gap-4 border-b border-slate-200 dark:border-gray-800 px-6 py-5">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Weekly
                        schedule</p>
                    <h3 class="mt-0.5 truncate text-lg font-bold text-slate-900 dark:text-white">
                        {{ $scheduleEmployeeName }}
                    </h3>
                </div>
                <button wire:click="closeSchedule" type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto p-6">

                @if ($errors->any())
                <div
                    class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-500/30 dark:bg-red-500/10">
                    <p class="text-xs font-bold uppercase tracking-wider text-red-700 dark:text-red-300">Fix these first
                    </p>
                    <ul class="mt-1.5 list-disc space-y-0.5 pl-4 text-sm text-red-700 dark:text-red-300">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <p class="mb-4 text-xs text-slate-500 dark:text-gray-400">
                    Toggle days off. Start &amp; end times use 24-hour format.
                </p>

                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-gray-800/50">
                            <tr>
                                <th
                                    class="w-12 px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                                    On
                                </th>
                                <th
                                    class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                                    Day
                                </th>
                                <th
                                    class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                                    Opens
                                </th>
                                <th
                                    class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                                    Closes
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                            @foreach ($dayLabels as $day => $label)
                            @php $isOn = $schedules[$day]['enabled']; @endphp
                            <tr wire:key="sched-{{ $day }}"
                                class="transition {{ $isOn ? '' : 'bg-slate-50/60 dark:bg-gray-900/40' }}">

                                <td class="px-3 py-3">
                                    <input type="checkbox" wire:model.live="schedules.{{ $day }}.enabled"
                                        class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" />
                                </td>

                                <td class="px-3 py-3">
                                    <span
                                        class="text-sm font-semibold {{ $isOn ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-gray-500' }}">
                                        {{ $label }}
                                    </span>
                                </td>

                                <td class="px-3 py-3">
                                    @if ($isOn)
                                    <input type="time" wire:model="schedules.{{ $day }}.start"
                                        class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-sm text-slate-900 dark:text-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" />
                                    @else
                                    <span
                                        class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                        Day off
                                    </span>
                                    @endif
                                </td>

                                <td class="px-3 py-3">
                                    @if ($isOn)
                                    <input type="time" wire:model="schedules.{{ $day }}.end"
                                        class="w-full rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-1.5 text-sm text-slate-900 dark:text-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" />
                                    @else
                                    <span
                                        class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                        —
                                    </span>
                                    @endif
                                </td>
                            </tr>

                            @error("schedules.{$day}.end")
                            <tr>
                                <td colspan="4" class="bg-red-50 dark:bg-red-500/10 px-3 py-2">
                                    <p class="text-xs text-red-600 dark:text-red-400">
                                        {{ $label }}: {{ $message }}
                                    </p>
                                </td>
                            </tr>
                            @enderror
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div
                class="flex justify-end gap-3 border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900/50 px-6 py-4">
                <button wire:click="closeSchedule" type="button"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="saveSchedule" wire:loading.attr="disabled" wire:target="saveSchedule" type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveSchedule">Save schedule</span>
                    <span wire:loading wire:target="saveSchedule">Saving…</span>
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

        <div
            class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Remove {{ $confirmingDeleteName }}?
                    </h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                        This employee will be permanently removed. Existing assignments and reviews will still reference
                        their name.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelDelete" type="button"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button wire:click="deleteEmployee" wire:loading.attr="disabled" wire:target="deleteEmployee"
                    type="button"
                    class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="deleteEmployee">Remove</span>
                    <span wire:loading wire:target="deleteEmployee">Removing…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>