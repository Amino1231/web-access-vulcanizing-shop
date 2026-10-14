<div class="space-y-6">

    @use('App\Enums\AppointmentStatus')

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Operations</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Appointments</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Confirm, start, complete, or cancel customer bookings.
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
                ['key' => 'upcoming',  'label' => 'Upcoming',  'count' => $counts['upcoming']],
                ['key' => 'today',     'label' => 'Today',     'count' => $counts['today']],
                ['key' => 'completed', 'label' => 'Completed', 'count' => $counts['completed']],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'count' => $counts['cancelled']],
                ['key' => 'all',       'label' => 'All',       'count' => $counts['all']],
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
                    placeholder="Search by customer, plate, brand, or booking #..."
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
    <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Booking</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Vehicle</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Assigned to</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">When</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @forelse ($this->appointments as $appt)
                        @php
                            $status = $appt->status;
                            $badge  = $status->badge();
                        @endphp
                        <tr wire:key="appt-{{ $appt->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">

                            <td class="whitespace-nowrap px-4 py-3">
                                <p class="font-mono text-xs text-slate-600 dark:text-gray-300">{{ $appt->appointment_number }}</p>
                            </td>

                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $appt->customer?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-500 dark:text-gray-400">{{ $appt->customer?->phone ?? '' }}</p>
                            </td>

                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                                @if ($appt->vehicle)
                                    <p class="font-medium">{{ $appt->vehicle->plate_number ?? '—' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400">
                                        {{ trim(($appt->vehicle->brand ?? '') . ' ' . ($appt->vehicle->model ?? '')) ?: '—' }}
                                    </p>
                                @else
                                    —
                                @endif
                            </td>

                            {{-- Assigned employee --}}
                            <td class="px-4 py-3 text-sm">
                                @if ($appt->employee)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $appt->employee->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700 dark:text-gray-300">
                                <p>{{ $appt->start_time?->format('M d, Y') }}</p>
                                <p class="text-xs text-slate-500 dark:text-gray-400">
                                    {{ $appt->start_time?->format('g:i A') }} – {{ $appt->end_time?->format('g:i A') }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $status->label() }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- Confirm — for Scheduled or Assigned --}}
                                    @if (in_array($status, [AppointmentStatus::Scheduled, AppointmentStatus::Assigned], true))
                                        <button wire:click="confirmAction({{ $appt->id }}, 'confirm')" type="button"
                                            title="Confirm & assign employee"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-500/10">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Start --}}
                                    @if (in_array($status, [AppointmentStatus::Confirmed, AppointmentStatus::Accepted], true))
                                        <button wire:click="confirmAction({{ $appt->id }}, 'start')" type="button"
                                            title="Start service"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 transition hover:bg-amber-50 dark:hover:bg-amber-500/10">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Complete --}}
                                    @if ($status === AppointmentStatus::InProgress)
                                        <button wire:click="confirmAction({{ $appt->id }}, 'complete')" type="button"
                                            title="Mark completed"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-emerald-600 transition hover:bg-emerald-50 dark:hover:bg-emerald-500/10">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- No-show --}}
                                    @if (in_array($status, [AppointmentStatus::Confirmed, AppointmentStatus::Accepted], true))
                                        <button wire:click="confirmAction({{ $appt->id }}, 'no_show')" type="button"
                                            title="Mark as no-show"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Cancel --}}
                                    @if ($status->canBeCancelled())
                                        <button wire:click="confirmAction({{ $appt->id }}, 'cancel')" type="button"
                                            title="Cancel"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 dark:hover:bg-red-500/10">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-gray-400">
                                No appointments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->appointments->total() > 0)
            <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Showing {{ $this->appointments->firstItem() }}–{{ $this->appointments->lastItem() }} of {{ $this->appointments->total() }}
                </p>
                <div>{{ $this->appointments->links() }}</div>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- Confirmation modal --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if ($confirmingId && $confirmingAction)
        @php
            $isDanger  = in_array($confirmingAction, ['cancel', 'no_show'], true);
            $isConfirm = $confirmingAction === 'confirm';

            $title = match ($confirmingAction) {
                'confirm'  => 'Confirm & assign this booking?',
                'start'    => 'Start the service?',
                'complete' => 'Mark this appointment as completed?',
                'cancel'   => 'Cancel this appointment?',
                'no_show'  => 'Mark customer as no-show?',
                default    => 'Confirm action',
            };
            $message = match ($confirmingAction) {
                'confirm'  => 'Pick the employee who will handle this job. They will be notified instantly.',
                'start'    => 'Move this appointment into in-progress state.',
                'complete' => 'The appointment will be closed and marked as completed.',
                'cancel'   => 'The customer will be notified. This cannot be undone.',
                'no_show'  => 'Record that the customer did not show up.',
                default    => '',
            };
        @endphp
        <div x-data @keydown.escape.window="$wire.cancelConfirmation()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">

                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $isDanger ? 'bg-red-100 dark:bg-red-500/20' : ($isConfirm ? 'bg-blue-100 dark:bg-blue-500/20' : 'bg-emerald-100 dark:bg-emerald-500/20') }}">
                        <svg class="h-6 w-6 {{ $isDanger ? 'text-red-600 dark:text-red-400' : ($isConfirm ? 'text-blue-600 dark:text-blue-400' : 'text-emerald-600 dark:text-emerald-400') }}"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            @if ($isConfirm)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            @endif
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-gray-400">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $confirmingLabel }}</span> — {{ $message }}
                        </p>

                        {{-- Employee picker — only when confirming --}}
                        @if ($isConfirm)
                            <div class="mt-4">
                                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                    Assign to <span class="text-red-500">*</span>
                                </label>

                                @if ($this->employees->isEmpty())
                                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                        No active employees. Add one in the Employees page first.
                                    </div>
                                @else
                                    <select wire:model="assigningEmployeeId"
                                        class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                        <option value="">— Select employee —</option>
                                        @foreach ($this->employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position }}</option>
                                        @endforeach
                                    </select>
                                    @error('assigningEmployeeId')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cancelConfirmation" type="button"
                        class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="performAction" wire:loading.attr="disabled" wire:target="performAction" type="button"
                        @disabled($isConfirm && $this->employees->isEmpty())
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition disabled:opacity-60
                            {{ $isDanger ? 'bg-red-600 hover:bg-red-700' : ($isConfirm ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-600 hover:bg-emerald-700') }}">
                        <span wire:loading.remove wire:target="performAction">
                            {{ $isConfirm ? 'Confirm & notify employee' : 'Confirm' }}
                        </span>
                        <span wire:loading wire:target="performAction">Working…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>