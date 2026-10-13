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
                     'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300': n.type === 'error'
                 }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    {{-- Back link --}}
    <a href="{{ route('employee.roadside') }}"
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-gray-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to roadside jobs
    </a>

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                Roadside request
            </p>
            <h1 class="mt-1 truncate font-mono text-2xl font-bold text-slate-900 dark:text-white">
                {{ $job->request_number }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                {{ $job->tenant?->name }}
            </p>
        </div>

        <x-employee.status-badge :status="$job->status" class="shrink-0" />
    </div>

    {{-- Detail grid --}}
    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Emergency summary --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Emergency details
                </h2>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500 dark:text-gray-400">Problem</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $job->problem_type ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-slate-500 dark:text-gray-400">Reported</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $job->created_at?->format('M d, Y') }}
                        </dd>
                        <dd class="text-xs text-slate-500 dark:text-gray-400">
                            {{ $job->created_at?->diffForHumans() }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Location --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Location
                </h2>

                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $job->location ?? 'No location provided' }}
                        </p>
                        @if ($job->location)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($job->location) }}"
                               target="_blank" rel="noopener"
                               class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-[#FF5E14] hover:underline">
                                Open in Google Maps
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Customer --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Customer
                </h2>

                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white dark:bg-gray-700">
                        {{ strtoupper(substr($job->customer?->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $job->customer?->name ?? 'Unknown customer' }}
                        </p>
                        @if ($job->customer?->phone)
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                {{ $job->customer->phone }}
                            </p>
                        @endif
                    </div>

                    @if ($job->customer?->phone)
                        <div class="flex items-center gap-2">
                            <a href="tel:{{ $job->customer->phone }}"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                               title="Call customer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </a>
                            <a href="sms:{{ $job->customer->phone }}"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                               title="Text customer">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Vehicle --}}
            @if ($job->vehicle)
                <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        Vehicle
                    </h2>

                    <p class="text-sm font-semibold text-slate-900 dark:text-white">
                        {{ trim(($job->vehicle->brand ?? '') . ' ' . ($job->vehicle->model ?? '')) ?: '—' }}
                        @if ($job->vehicle->vehicle_type)
                            <span class="text-slate-500 dark:text-gray-400">· {{ ucfirst($job->vehicle->vehicle_type) }}</span>
                        @endif
                    </p>
                    @if ($job->vehicle->plate_number)
                        <p class="mt-0.5 font-mono text-xs text-slate-500 dark:text-gray-400">
                            {{ $job->vehicle->plate_number }}
                        </p>
                    @endif
                </section>
            @endif

            {{-- Notes --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Notes
                </h2>

                @if (in_array($job->status, ['accepted', 'arrived'], true))
                    <textarea wire:model="notes" rows="4" placeholder="Add completion notes..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-[#FF5E14] focus:bg-white focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:bg-gray-800"></textarea>
                    @error('notes') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                @else
                    <p class="whitespace-pre-line text-sm text-slate-700 dark:text-gray-300">
                        {{ $job->notes ?: 'No notes.' }}
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
                    @if (in_array($job->status, ['assigned', 'pending'], true))
                        <button wire:click="accept" wire:loading.attr="disabled" wire:target="accept" type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-3 text-sm font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                            <span wire:loading.remove wire:target="accept">Accept job</span>
                            <span wire:loading wire:target="accept">Accepting…</span>
                        </button>
                    @endif

                    {{-- Arrived --}}
                    @if ($job->status === 'accepted')
                        <button wire:click="markArrived" wire:loading.attr="disabled" wire:target="markArrived" type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-amber-600 disabled:opacity-60">
                            <span wire:loading.remove wire:target="markArrived">I've arrived</span>
                            <span wire:loading wire:target="markArrived">Updating…</span>
                        </button>
                    @endif

                    {{-- Complete --}}
                    @if (in_array($job->status, ['accepted', 'arrived'], true))
                        <button wire:click="complete" wire:loading.attr="disabled" wire:target="complete" type="button"
                                wire:confirm="Mark this roadside job as completed?"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="complete">Mark completed</span>
                            <span wire:loading wire:target="complete">Saving…</span>
                        </button>
                    @endif

                    {{-- Terminal --}}
                    @if ($job->status === 'completed')
                        <div class="rounded-xl bg-emerald-50 p-4 text-center dark:bg-emerald-500/10">
                            <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Completed</p>
                            <p class="mt-0.5 text-xs text-emerald-600 dark:text-emerald-400">
                                {{ $job->updated_at?->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    @endif

                    @if ($job->status === 'cancelled')
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
                        <dt class="text-slate-500 dark:text-gray-400">Requested</dt>
                        <dd class="text-right font-medium text-slate-900 dark:text-white">
                            {{ $job->created_at?->format('M d, Y') }}
                        </dd>
                    </div>

                    @if ($job->updated_at && $job->updated_at->ne($job->created_at))
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500 dark:text-gray-400">Last update</dt>
                            <dd class="text-right font-medium text-slate-900 dark:text-white">
                                {{ $job->updated_at->diffForHumans() }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </aside>
    </div>
</div>