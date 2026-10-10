<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Your bookings</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Appointments</h1>
            </div>

            <select wire:model.live="statusFilter"
                    class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                <option value="">All statuses</option>
                @foreach (\App\Enums\AppointmentStatus::cases() as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>

        @php $appointments = $this->appointments; @endphp

        @if ($appointments->isEmpty())
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-12 text-center">
                <p class="text-slate-500 dark:text-gray-400">No appointments yet.</p>
                <a href="{{ route('customer.services.index') }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#D94E10] transition">
                    Book a service
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($appointments as $appointment)
                    @php
                        $badge = $appointment->status->badge();
                        $canCancel = $appointment->status->canBeCancelled()
                                     && $appointment->start_time->isFuture();
                    @endphp

                    <article wire:key="apt-{{ $appointment->id }}"
                             class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">

                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-[11px] font-mono text-slate-500 dark:text-gray-400">
                                        {{ $appointment->appointment_number }}
                                    </p>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                        {{ $appointment->status->label() }}
                                    </span>
                                </div>

                                <p class="mt-2 text-base font-bold text-slate-900 dark:text-white">
                                    {{ $appointment->tenant?->name }}
                                </p>

                                <p class="mt-1 text-sm text-slate-600 dark:text-gray-400">
                                    {{ $appointment->start_time->format('M d, Y · g:i A') }}
                                    <span class="text-slate-400">–</span>
                                    {{ $appointment->end_time->format('g:i A') }}
                                </p>

                                @if ($appointment->employee)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                                        Staff: {{ $appointment->employee->name }}
                                    </p>
                                @endif

                                @if ($appointment->vehicle)
                                    <p class="text-xs text-slate-500 dark:text-gray-400">
                                        Vehicle: {{ $appointment->vehicle->plate_number }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('customer.appointment_detail', $appointment->id) }}" wire:navigate
                                   class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50">
                                    View
                                </a>

                                @if ($canCancel)
                                    <button wire:click="cancelAppointment({{ $appointment->id }})"
                                            wire:confirm="Cancel this appointment?"
                                            wire:loading.attr="disabled"
                                            wire:target="cancelAppointment({{ $appointment->id }})"
                                            class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-3.5 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-100 disabled:opacity-60">
                                        Cancel
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $appointments->links() }}</div>
        @endif
    </div>
</div>