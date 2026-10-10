<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @php $a = $this->appointment; @endphp

        @if (session('booking_success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                Booking confirmed — <span class="font-bold">{{ session('booking_success') }}</span>
            </div>
        @endif

        <a href="{{ route('customer.appointments') }}" wire:navigate
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to appointments
        </a>

        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 sm:p-8 space-y-6">
            {{-- Status header --}}
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-mono text-slate-500 dark:text-gray-400">{{ $a->appointment_number }}</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $a->tenant?->name }}</h1>
                </div>
                <span class="rounded-full bg-slate-100 dark:bg-gray-800 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ $a->status->label() }}
                </span>
            </div>

            {{-- Service being booked --}}
            @if ($a->service)
                <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400 mb-1">Service</p>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white">{{ $a->service->name }}</p>
                    @if ($a->service->category)
                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ $a->service->category->name }}</p>
                    @endif
                </div>
            @endif

            {{-- When --}}
            <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400 mb-1">Date & time</p>
                <p class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ $a->start_time->format('l, M d, Y') }}
                </p>
                <p class="text-sm text-slate-600 dark:text-gray-400">
                    {{ $a->start_time->format('g:i A') }} – {{ $a->end_time->format('g:i A') }}
                </p>
            </div>

            {{-- Details --}}
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-gray-800 pb-3">
                    <dt class="text-slate-500 dark:text-gray-400">Shop</dt>
                    <dd class="text-right font-semibold text-slate-900 dark:text-white">{{ $a->tenant?->name }}</dd>
                </div>
                @if ($a->tenant?->phone)
                    <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-gray-800 pb-3">
                        <dt class="text-slate-500 dark:text-gray-400">Contact</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">{{ $a->tenant->phone }}</dd>
                    </div>
                @endif
                @if ($a->tenant?->address)
                    <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-gray-800 pb-3">
                        <dt class="text-slate-500 dark:text-gray-400">Address</dt>
                        <dd class="text-right font-semibold text-slate-900 dark:text-white">{{ $a->tenant->address }}</dd>
                    </div>
                @endif
                @if ($a->employee)
                    <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-gray-800 pb-3">
                        <dt class="text-slate-500 dark:text-gray-400">Assigned staff</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">
                            {{ $a->employee->name }} <span class="text-slate-400">·</span>
                            <span class="font-normal text-slate-500">{{ $a->employee->position }}</span>
                        </dd>
                    </div>
                @endif
                @if ($a->vehicle)
                    <div class="flex justify-between gap-4 border-b border-slate-100 dark:border-gray-800 pb-3">
                        <dt class="text-slate-500 dark:text-gray-400">Vehicle</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">
                            {{ $a->vehicle->plate_number }}
                            <span class="text-slate-400">·</span>
                            <span class="font-normal text-slate-500">{{ $a->vehicle->brand }} {{ $a->vehicle->model }}</span>
                        </dd>
                    </div>
                @endif
                @if ($a->notes)
                    <div class="border-b border-slate-100 dark:border-gray-800 pb-3">
                        <dt class="text-slate-500 dark:text-gray-400 mb-1">Notes</dt>
                        <dd class="text-slate-700 dark:text-gray-300">{{ $a->notes }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 dark:text-gray-400">Booked on</dt>
                    <dd class="text-slate-700 dark:text-gray-300">{{ $a->created_at->format('M d, Y g:i A') }}</dd>
                </div>
            </dl>

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- RATE THIS SERVICE — only when completed --}}
            {{-- ═══════════════════════════════════════════════ --}}
            @if ($this->canRateService)
                <div class="rounded-2xl border border-orange-200 dark:border-orange-500/30 bg-gradient-to-br from-orange-50 to-orange-100/50 dark:from-orange-500/10 dark:to-orange-500/5 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FF5E14] shadow-lg shadow-orange-500/30">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">How was the service?</p>
                                <p class="mt-0.5 text-xs text-slate-600 dark:text-gray-400">
                                    Share your experience to help other customers find trusted shops.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('customer.services.book', $this->servicePost->id) }}"
                           class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            Rate this service
                        </a>
                    </div>
                </div>
            @elseif ($this->isCompleted && $this->hasReviewedService)
                {{-- Already reviewed --}}
                <div class="rounded-2xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 p-4 flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                            Thanks for your review!
                        </p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">
                            You already rated this service. Your feedback helps other customers.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            @if ($a->status->canBeCancelled())
                <div class="flex justify-end gap-3 border-t border-slate-100 dark:border-gray-800 pt-5">
                    <button wire:click="cancel"
                            wire:confirm="Cancel this appointment?"
                            wire:loading.attr="disabled" wire:target="cancel"
                            class="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-5 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-100 disabled:opacity-60">
                        Cancel appointment
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>