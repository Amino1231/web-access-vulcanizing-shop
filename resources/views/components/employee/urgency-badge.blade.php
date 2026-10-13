{{-- resources/views/components/employee/urgency-badge.blade.php --}}
@props(['urgency' => 'normal'])

@php
    $map = [
        'normal'   => ['bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300',              'Normal'],
        'urgent'   => ['bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',          'Urgent'],
        'critical' => ['bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',                  'Critical'],
    ];

    [$classes, $label] = $map[$urgency] ?? $map['normal'];
    $pulse = in_array($urgency, ['urgent', 'critical'], true);
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider', $classes]) }}>
    <span @class(['h-1.5 w-1.5 rounded-full bg-current', 'animate-pulse' => $pulse])></span>
    {{ $label }}
</span>