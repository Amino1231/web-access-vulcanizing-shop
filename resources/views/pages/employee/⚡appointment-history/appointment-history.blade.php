<div class="space-y-6"
     x-data="{ notifications: [] }"
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
                     'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300': n.type === 'error',
                     'bg-slate-50 border-slate-200 text-slate-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200': !n.type || (n.type !== 'success' && n.type !== 'error')
                 }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    @if (! $this->employee)
        <div class="rounded-2xl border border-dashed border-red-300 dark:border-red-500/40 bg-red-50 dark:bg-red-500/5 p-10 text-center">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                Your account isn't linked to any shop yet.
            </p>
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                Ask your shop owner to add you to their team.
            </p>
        </div>
    @else
        {{-- Header --}}
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                        {{ $this->employee->tenant?->name ?? 'Employee portal' }}
                    </p>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                        My Appointments
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                        Every appointment assigned to you.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Search # or customer..."
                           class="w-full sm:w-72 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />

                    <button wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh" type="button"
                            title="Refresh"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 dark:text-gray-300 transition hover:bg-slate-50 dark:hover:bg-gray-700 disabled:opacity-60">
                        <svg wire:loading.remove wire:target="refresh" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <svg wire:loading wire:target="refresh" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Tabs --}}
            @php
                $counts = $this->counts;
                $tabs = [
                    ['key' => 'upcoming',  'label' => 'Upcoming',  'count' => $counts['upcoming']],
                    ['key' => 'completed', 'label' => 'Completed', 'count' => $counts['completed']],
                    ['key' => 'all',       'label' => 'All',       'count' => $counts['all']],
                ];
            @endphp

            <div class="flex flex-wrap gap-2">
                @foreach ($tabs as $tab)
                    <button wire:click="$set('statusFilter', '{{ $tab['key'] }}')" type="button"
                            class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                                {{ $statusFilter === $tab['key']
                                    ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10 dark:border-orange-500/40 dark:text-orange-300'
                                    : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">
                        {{ $tab['label'] }}
                        <span class="rounded-full bg-slate-100 dark:bg-gray-700 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:text-gray-300">
                            {{ $tab['count'] }}
                        </span>
                    </button>
                @endforeach

                @if ($search !== '')
                    <button wire:click="clearFilters" type="button"
                            class="ml-auto inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-xs font-semibold text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700">
                        Clear search
                    </button>
                @endif
            </div>
        </div>

        {{-- List --}}
        @php $appointments = $this->appointments; @endphp

        @if ($appointments->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none"
                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">
                    No appointments in this view.
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                    @if ($search !== '')
                        Try clearing the search or another tab.
                    @else
                        Nothing scheduled yet.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($appointments as $appt)
                    <x-employee.job-card
                        wire:key="appt-{{ $appt->id }}"
                        type="appointment"
                        :id="$appt->id"
                        :number="$appt->appointment_number"
                        :title="$appt->service?->name ?? 'Service'"
                        :subtitle="$appt->customer?->name ?? 'Customer'"
                        :scheduled-at="$appt->start_time"
                        :status="$appt->status"
                        :href="route('employee.appointment_detail', $appt->id)"
                    />
                @endforeach
            </div>

            <div>{{ $appointments->links() }}</div>
        @endif
    @endif
</div>