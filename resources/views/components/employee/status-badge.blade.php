{{-- resources/views/components/employee/status-badge.blade.php --}}
@props(['status'])

@php
    use App\Enums\AppointmentStatus;
    use App\Enums\OrderStatus;
    use App\Enums\PostStatus;

    // Normalize to a string key
    $key = $status instanceof \BackedEnum ? $status->value : (string) $status;

    // Prefer the enum's own label() if available
    $enumLabel = null;
    foreach ([AppointmentStatus::class, OrderStatus::class, PostStatus::class] as $enumClass) {
        if (method_exists($enumClass, 'tryFrom')) {
            $case = $enumClass::tryFrom($key);
            if ($case && method_exists($case, 'label')) {
                $enumLabel = $case->label();
                break;
            }
        }
    }

    $map = [
        'pending'     => ['bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300',              'Pending'],
        'scheduled'   => ['bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300',              'Scheduled'],
        'confirmed'   => ['bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',              'Confirmed'],
        'assigned'    => ['bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',              'Assigned'],
        'accepted'    => ['bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',      'Accepted'],
        'in_progress' => ['bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',          'In progress'],
        'arrived'     => ['bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',          'Arrived'],
        'completed'   => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',  'Completed'],
        'cancelled'   => ['bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',              'Cancelled'],
        'no_show'     => ['bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',              'No show'],
        'published'   => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',  'Published'],
        'draft'       => ['bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300',              'Draft'],
        'archived'    => ['bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-gray-400',              'Archived'],
    ];

    [$classes, $fallbackLabel] = $map[$key] ?? [
        'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-gray-300',
        ucfirst(str_replace('_', ' ', $key)),
    ];

    $label = $enumLabel ?? $fallbackLabel;
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider', $classes]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $label }}
</span>