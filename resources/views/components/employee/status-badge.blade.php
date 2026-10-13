{{-- resources/views/components/employee/status-badge.blade.php --}}
@props(['status'])

@php
    $key = $status instanceof \BackedEnum ? $status->value : (string) $status;

    $map = [
        // appointment
        'pending'     => ['bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300',              'Pending'],
        'confirmed'   => ['bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',              'Confirmed'],
        'assigned'    => ['bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',              'Assigned'],
        'accepted'    => ['bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',      'Accepted'],
        'in_progress' => ['bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',          'In progress'],
        'arrived'     => ['bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',          'Arrived'],
        'completed'   => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',  'Completed'],
        'cancelled'   => ['bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',              'Cancelled'],
        'no_show'     => ['bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',              'No show'],
    ];

    [$classes, $label] = $map[$key] ?? [
        'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300',
        ucfirst(str_replace('_', ' ', $key)),
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider', $classes]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $label }}
</span>