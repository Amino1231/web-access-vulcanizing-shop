@php
$toneClasses = [
    'orange'  => 'border-orange-200 bg-orange-50 dark:border-orange-900/40 dark:bg-orange-950/30',
    'green'   => 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/40 dark:bg-emerald-950/30',
    'emerald' => 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/40 dark:bg-emerald-950/30',
    'blue'    => 'border-blue-200 bg-blue-50 dark:border-blue-900/40 dark:bg-blue-950/30',
    'amber'   => 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/30',
    'red'     => 'border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/30',
];

$iconMap = [
    'blue'    => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    'orange'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    'emerald' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'amber'   => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'red'     => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
];
@endphp

<div class="min-h-screen text-slate-900 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Header --}}
        <header class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                    Platform Overview
                </p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">
                    Admin Dashboard
                </h1>
            </div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                {{ now()->format('l, F j, Y') }}
            </p>
        </header>

        {{-- KPI Cards --}}
        <section class="mb-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach($kpis as $kpi)
                    <article class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md {{ $toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' }}">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                {{ $kpi['label'] }}
                            </span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/60 dark:bg-black/20">
                                <svg class="h-4 w-4 text-slate-700 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconMap[$kpi['tone']] ?? '' }}" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ $kpi['value'] }}
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Top Shops Leaderboard --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Leaderboard
                    </p>
                    <h2 class="mt-0.5 text-lg font-bold">Top Rated Shops</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    Based on Reviews
                </span>
            </div>

            @if (count($topShops) === 0)
                <div class="p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">No reviews have been submitted yet.</p>
                </div>
            @else
                {{-- Mobile view --}}
                <div class="space-y-3 p-4 md:hidden">
                    @foreach($topShops as $i => $shop)
                        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-slate-100 dark:text-slate-900">
                                {{ $i + 1 }}
                            </div>
                            
                            @if($shop['logo'])
                                <img src="{{ $shop['logo'] }}" class="h-10 w-10 shrink-0 rounded-lg object-cover" />
                            @else
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    {{ strtoupper(substr($shop['name'], 0, 2)) }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $shop['name'] }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $shop['owner'] }}</p>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center gap-1 text-sm font-bold text-yellow-500">
                                    <span>★</span> {{ $shop['rating'] ?? '—' }}
                                </div>
                                <p class="text-[10px] font-medium text-slate-500">{{ $shop['reviews'] }} reviews</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop view --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="w-16 px-6 py-4 text-left">Rank</th>
                                <th class="px-6 py-4 text-left">Shop Details</th>
                                <th class="px-6 py-4 text-right">Total Reviews</th>
                                <th class="px-6 py-4 text-right">Average Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($topShops as $i => $shop)
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-6 py-4">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            @if($shop['logo'])
                                                <img src="{{ $shop['logo'] }}" class="h-10 w-10 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-slate-700" />
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xs font-bold text-white dark:bg-slate-100 dark:text-slate-900">
                                                    {{ strtoupper(substr($shop['name'], 0, 2)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $shop['name'] }}</p>
                                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">Owner: {{ $shop['owner'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ number_format($shop['reviews']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex items-center gap-1 text-sm font-bold text-slate-900 dark:text-white">
                                            <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            {{ $shop['rating'] ?? '—' }} / 5
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>