<div class="space-y-6" x-data="{ notifications: [] }"
    @notify.window="
        notifications.push($event.detail);
        setTimeout(() => notifications.shift(), 4000);
     "
    @if ($this->employee)
    wire:poll.30s="refresh"
    @endif>

    {{-- Toast notifications --}}
    <div class="pointer-events-none fixed right-4 top-4 z-50 flex flex-col gap-2">
        <template x-for="(n, i) in notifications" :key="i">
            <div x-transition.opacity.duration.200ms
                class="pointer-events-auto w-80 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg"
                :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-300': n
                        .type === 'success',
                    'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300': n
                        .type === 'error',
                    'bg-slate-50 border-slate-200 text-slate-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200':
                        !n.type || (n.type !== 'success' && n.type !== 'error')
                }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    @if (!$this->employee)
        {{-- Guard: user has 'employee' role but no Employee row --}}
        <div
            class="rounded-2xl border border-dashed border-red-300 dark:border-red-500/40 bg-red-50 dark:bg-red-500/5 p-10 text-center">
            <svg class="mx-auto h-10 w-10 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="mt-3 text-sm font-semibold text-red-700 dark:text-red-300">
                Your account isn't linked to any shop yet.
            </p>
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                Ask your shop owner to add you to their team.
            </p>
        </div>
    @else
        {{-- Header --}}
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                        {{ $this->tenant?->name ?? 'Employee portal' }}
                    </p>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }},
                        {{ explode(' ', trim($this->employee->name))[0] }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                        {{ now()->format('l, F j, Y') }} · {{ $this->employee->position ?? 'Employee' }}
                    </p>
                </div>

                <button wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh" type="button"
                    title="Refresh"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 dark:text-gray-300 transition hover:bg-slate-50 dark:hover:bg-gray-700 disabled:opacity-60">
                    <svg wire:loading.remove wire:target="refresh" class="h-4 w-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg wire:loading wire:target="refresh" class="h-4 w-4 animate-spin" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </button>
            </div>

            {{-- Stats --}}
            @php
                $statCards = [
                    [
                        'label' => "Today's jobs",
                        'value' => $this->stats['today'],
                        'tone' => 'text-slate-900 dark:text-white',
                        'accent' => 'bg-slate-300 dark:bg-gray-600',
                    ],
                    [
                        'label' => 'In progress',
                        'value' => $this->stats['in_progress'],
                        'tone' => 'text-amber-600 dark:text-amber-400',
                        'accent' => 'bg-amber-400',
                    ],
                    [
                        'label' => 'Completed today',
                        'value' => $this->stats['completed'],
                        'tone' => 'text-emerald-600 dark:text-emerald-400',
                        'accent' => 'bg-emerald-400',
                    ],
                    [
                        'label' => 'Active roadside',
                        'value' => $this->stats['roadside'],
                        'tone' => 'text-red-600 dark:text-red-400',
                        'accent' => 'bg-red-400',
                    ],
                ];
            @endphp

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($statCards as $card)
                    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                            {{ $card['label'] }}
                        </p>
                        <div class="mt-2 flex items-end justify-between">
                            <span class="text-3xl font-bold {{ $card['tone'] }}">{{ $card['value'] }}</span>
                            <span class="mb-1.5 h-1 w-8 rounded-full {{ $card['accent'] }}"></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Body --}}
        <div class="grid gap-6 lg:grid-cols-5">

            {{-- Today's appointments --}}
            <section class="lg:col-span-3 space-y-3">
                <div class="flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Today's appointments</h2>
                        <p class="text-xs text-slate-500 dark:text-gray-400">
                            {{ $this->todayAppointments->count() }}
                            {{ $this->todayAppointments->count() === 1 ? 'job' : 'jobs' }} scheduled
                        </p>
                    </div>
                    <a href="{{ route('employee.appointments') }}"
                        class="text-xs font-semibold text-[#FF5E14] hover:underline">
                        View all →
                    </a>
                </div>

                @forelse ($this->todayAppointments as $appt)
                    <x-employee.job-card wire:key="appt-{{ $appt->id }}" :type="'appointment'" :id="$appt->id"
                        :number="$appt->appointment_number" :title="$appt->service?->name ?? 'Service'" :subtitle="$appt->customer?->name ?? 'Customer'" :scheduled-at="$appt->start_time" :status="$appt->status"
                        :href="route('employee.appointment_detail', $appt->id)" />
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-10 text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-gray-600" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-slate-500 dark:text-gray-400">No appointments today</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-gray-500">Enjoy the quiet — or check roadside.
                        </p>
                    </div>
                @endforelse
            </section>

            {{-- Right column --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Roadside --}}
                <section class="space-y-3">
                    <div class="flex items-end justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Roadside</h2>
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                {{ $this->activeRoadside->count() }}
                                active {{ $this->activeRoadside->count() === 1 ? 'request' : 'requests' }}
                            </p>
                        </div>
                        <a href="{{ route('employee.roadside') }}"
                            class="text-xs font-semibold text-[#FF5E14] hover:underline">
                            View all →
                        </a>
                    </div>

                    @forelse ($this->activeRoadside as $job)
                        <x-employee.job-card wire:key="road-{{ $job->id }}" :type="'roadside'" :id="$job->id"
                            :number="$job->request_number" :title="$job->problem_type ?? 'Roadside assistance'" :subtitle="$job->customer?->name ?? 'Customer'" :location="$job->location"
                            :status="$job->status" :urgency="$job->urgency ?? 'normal'" :href="route('employee.roadside_job_detail', $job->id)" />
                    @empty
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 text-center">
                            <p class="text-xs text-slate-500 dark:text-gray-400">No active roadside requests.</p>
                        </div>
                    @endforelse
                </section>

                {{-- Quick actions --}}
                <section
                    class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 space-y-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Quick actions</h3>
                    <div class="grid gap-2">
                        @foreach ([['route' => 'employee.appointments', 'label' => 'All appointments'], ['route' => 'employee.roadside', 'label' => 'All roadside jobs'], ['route' => 'employee.profile', 'label' => 'My profile']] as $link)
                            <a href="{{ route($link['route']) }}"
                                class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:border-[#FF5E14] hover:bg-orange-50 dark:hover:bg-orange-500/10 dark:hover:text-orange-300">
                                <span>{{ $link['label'] }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    @endif
</div>
