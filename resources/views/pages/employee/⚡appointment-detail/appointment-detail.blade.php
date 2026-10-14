<div class="space-y-6" x-data="{ notifications: [] }"
    @notify.window="
        notifications.push($event.detail);
        setTimeout(() => notifications.shift(), 4000);
     ">

    {{-- Toast stack --}}
    <div class="pointer-events-none fixed right-4 top-4 z-50 flex flex-col gap-2">
        <template x-for="(n, i) in notifications" :key="i">
            <div x-transition.opacity.duration.200ms
                class="pointer-events-auto w-80 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg"
                :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-300': n.type === 'success',
                    'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300': n.type === 'error'
                }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    @use('App\Enums\AppointmentStatus')

    {{-- Back link --}}
    <a href="{{ route('employee.appointments') }}"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-gray-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to appointments
    </a>

    @php $appt = $this->appointment; @endphp

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                Appointment
            </p>
            <h1 class="mt-1 truncate font-mono text-2xl font-bold text-slate-900 dark:text-white">
                {{ $appt->appointment_number }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                {{ $appt->tenant?->name }}
            </p>
        </div>

        <x-employee.status-badge :status="$appt->status" class="shrink-0" />
    </div>

    {{-- Detail grid --}}
    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Schedule --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Schedule
                </h2>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500 dark:text-gray-400">Start</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $appt->start_time?->format('l, M d, Y') }}
                        </dd>
                        <dd class="text-xs text-slate-500 dark:text-gray-400">
                            {{ $appt->start_time?->format('g:i A') }}
                        </dd>
                    </div>

                    @if ($appt->end_time)
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-gray-400">Ends</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                                {{ $appt->end_time->format('g:i A') }}
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-xs text-slate-500 dark:text-gray-400">Service</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $appt->service?->name ?? '—' }}
                        </dd>
                        @if ($appt->service?->duration_minutes)
                            <dd class="text-xs text-slate-500 dark:text-gray-400">
                                ~{{ $appt->service->duration_minutes }} min
                            </dd>
                        @endif
                    </div>

                    @if ($appt->vehicle)
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-gray-400">Vehicle</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                                {{ trim($appt->vehicle->brand . ' ' . $appt->vehicle->model) ?: '—' }}
                                @if ($appt->vehicle->year_model)
                                    <span class="text-slate-500 dark:text-gray-400">({{ $appt->vehicle->year_model }})</span>
                                @endif
                            </dd>
                            <dd class="font-mono text-xs text-slate-500 dark:text-gray-400">
                                {{ $appt->vehicle->plate_number ?? '—' }}
                                @if ($appt->vehicle->vehicle_type)
                                    · {{ ucfirst($appt->vehicle->vehicle_type) }}
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Customer --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Customer
                </h2>

                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white dark:bg-gray-700">
                        {{ strtoupper(substr($appt->customer?->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $appt->customer?->name ?? 'Unknown customer' }}
                        </p>
                        @if ($appt->customer?->phone)
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                {{ $appt->customer->phone }}
                            </p>
                        @endif
                    </div>

                    @if ($appt->customer?->phone)
                        <div class="flex items-center gap-2">
                            <a href="tel:{{ $appt->customer->phone }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                title="Call customer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </a>
                            <a href="sms:{{ $appt->customer->phone }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                title="Text customer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Notes --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Notes
                </h2>

                @if (in_array($appt->status, [
                    AppointmentStatus::Accepted,
                    AppointmentStatus::InProgress,
                ], true))
                    <textarea wire:model="notes" rows="4" placeholder="Add job notes for the customer or owner..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-[#FF5E14] focus:bg-white focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800"></textarea>
                    @error('notes')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <p class="whitespace-pre-line text-sm text-slate-700 dark:text-gray-300">
                        {{ $appt->notes ?: 'No notes.' }}
                    </p>
                @endif
            </section>
        </div>

        {{-- Actions column --}}
        <aside class="space-y-4">

            {{-- Actions --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Actions
                </h2>

                <div class="space-y-2">

                    {{-- Accept --}}
                    @if (in_array($appt->status, [
                        AppointmentStatus::Scheduled,
                        AppointmentStatus::Confirmed,
                        AppointmentStatus::Assigned,
                    ], true))
                        <button wire:click="accept" wire:loading.attr="disabled" wire:target="accept" type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-3 text-sm font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                            <svg wire:loading.remove wire:target="accept" class="h-4 w-4" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span wire:loading.remove wire:target="accept">Accept job</span>
                            <span wire:loading wire:target="accept">Accepting…</span>
                        </button>
                    @endif

                    {{-- Start --}}
                    @if ($appt->status === AppointmentStatus::Accepted)
                        <button wire:click="start" wire:loading.attr="disabled" wire:target="start" type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span wire:loading.remove wire:target="start">Start job</span>
                            <span wire:loading wire:target="start">Starting…</span>
                        </button>
                    @endif

                    {{-- Complete --}}
                    @if (in_array($appt->status, [
                        AppointmentStatus::Accepted,
                        AppointmentStatus::InProgress,
                    ], true))
                        <button wire:click="complete" wire:loading.attr="disabled" wire:target="complete"
                            type="button" wire:confirm="Mark this appointment as completed?"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span wire:loading.remove wire:target="complete">Mark completed</span>
                            <span wire:loading wire:target="complete">Saving…</span>
                        </button>
                    @endif

                    {{-- Cancel --}}
                    @if ($appt->status->canBeCancelled())
                        <button wire:click="cancel" wire:loading.attr="disabled" wire:target="cancel" type="button"
                            wire:confirm="Cancel this appointment? The customer and owner will be notified."
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 transition hover:bg-red-100 disabled:opacity-60 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                            <span wire:loading.remove wire:target="cancel">Cancel job</span>
                            <span wire:loading wire:target="cancel">Cancelling…</span>
                        </button>
                    @endif

                    {{-- Terminal states --}}
                    @if ($appt->status === AppointmentStatus::Completed)
                        <div class="rounded-xl bg-emerald-50 p-4 text-center dark:bg-emerald-500/10">
                            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Completed</p>
                            <p class="mt-0.5 text-xs text-emerald-600 dark:text-emerald-400">
                                {{ $appt->updated_at?->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    @endif

                    @if ($appt->status === AppointmentStatus::Cancelled)
                        <div class="rounded-xl bg-rose-50 p-4 text-center dark:bg-rose-500/10">
                            <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">Cancelled</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Meta --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Details
                </h2>

                <dl class="space-y-3 text-xs">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500 dark:text-gray-400">Created</dt>
                        <dd class="text-right font-medium text-slate-900 dark:text-white">
                            {{ $appt->created_at?->format('M d, Y') }}
                        </dd>
                    </div>

                    @if ($appt->employee)
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-gray-400">Assigned to</dt>
                            <dd class="text-right font-medium text-slate-900 dark:text-white">
                                {{ $appt->employee->name }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </aside>
    </div>
</div>