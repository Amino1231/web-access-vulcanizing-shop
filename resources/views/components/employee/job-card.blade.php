{{-- resources/views/components/employee/job-card.blade.php --}}
@props([
    'type'        => 'appointment',
    'id'          => null,
    'number'      => '',
    'title'       => '',
    'subtitle'    => '',
    'scheduledAt' => null,
    'location'    => null,
    'status'      => 'pending',
    'urgency'     => null,
    'href'        => '#',
])

<div class="group relative rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700">
    <a href="{{ $href }}" class="absolute inset-0 rounded-2xl" aria-label="Open {{ $number }}"></a>

    <div class="flex items-start justify-between gap-3">

        {{-- Left: identity --}}
        <div class="min-w-0 flex-1">

            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-gray-500">
                    {{ $type === 'roadside' ? 'Roadside' : 'Appointment' }}
                </span>
                <span class="font-mono text-xs text-slate-500 dark:text-gray-400">{{ $number }}</span>
            </div>

            <p class="mt-1 truncate text-sm font-semibold text-slate-900 dark:text-white">
                {{ $title }}
            </p>

            <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-gray-400">
                {{ $subtitle }}
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600 dark:text-gray-300">

                @if ($type === 'appointment' && $scheduledAt)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ \Illuminate\Support\Carbon::parse($scheduledAt)->format('M d · g:i A') }}
                    </span>
                @endif

                @if ($type === 'roadside' && $location)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="max-w-[16rem] truncate">{{ $location }}</span>
                    </span>
                @endif
            </div>
        </div>

        {{-- Right: badges --}}
        <div class="flex shrink-0 flex-col items-end gap-2">
            <x-employee.status-badge :status="$status" />
            @if ($type === 'roadside' && $urgency)
                <x-employee.urgency-badge :urgency="$urgency" />
            @endif
        </div>
    </div>
</div>  