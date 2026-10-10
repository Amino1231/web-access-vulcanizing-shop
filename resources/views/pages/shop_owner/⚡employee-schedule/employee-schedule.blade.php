<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('owner.employees') }}" wire:navigate
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back to employees
            </a>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                Weekly schedule
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $employeeName }}</span>
                · {{ $employeePosition }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="resetToDefault" type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
                Reset to default
            </button>
            <button wire:click="save" wire:loading.attr="disabled" wire:target="save" type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span wire:loading.remove wire:target="save">Save schedule</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Error banner --}}
    @if ($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-500/30 dark:bg-red-500/10">
        <p class="text-xs font-bold uppercase tracking-wider text-red-700 dark:text-red-300">Fix these first</p>
        <ul class="mt-1.5 list-disc space-y-0.5 pl-4 text-sm text-red-700 dark:text-red-300">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Info strip --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-400">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" d="M12 8v4l3 2" />
                </svg>
                <span>Toggle days off. Times use 24-hour format.</span>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5 text-sm">
                    <span class="text-slate-500 dark:text-gray-400">Weekly total:</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $this->totalHours }} h</span>
                </div>
                <button wire:click="clearAll" type="button"
                        class="text-xs font-semibold text-slate-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                    Clear all
                </button>
            </div>
        </div>
    </div>

    {{-- Schedule table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-gray-800/50">
                <tr>
                    <th class="w-16 px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        On
                    </th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        Day
                    </th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        Opens
                    </th>
                    <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        Closes
                    </th>
                    <th class="w-32 px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                        Hours
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-gray-800">
                @foreach ($dayLabels as $day => $label)
                    @php
                        $isOn = $schedules[$day]['enabled'] ?? false;

                        $dayHours = null;
                        if ($isOn) {
                            $s = $this->normalizeTime($schedules[$day]['start'] ?? null);
                            $e = $this->normalizeTime($schedules[$day]['end'] ?? null);
                            if ($s && $e && $s < $e) {
                                [$sh, $sm] = array_map('intval', explode(':', $s));
                                [$eh, $em] = array_map('intval', explode(':', $e));
                                $dayHours = round(((($eh * 60) + $em) - (($sh * 60) + $sm)) / 60, 1);
                            }
                        }
                    @endphp
                    <tr wire:key="sched-{{ $day }}"
                        class="transition {{ $isOn ? '' : 'bg-slate-50/60 dark:bg-gray-900/40' }}">

                        <td class="px-4 py-4">
                            <input type="checkbox"
                                   wire:model.live="schedules.{{ $day }}.enabled"
                                   class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" />
                        </td>

                        <td class="px-4 py-4">
                            <span class="text-sm font-semibold {{ $isOn ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-gray-500' }}">
                                {{ $label }}
                            </span>
                        </td>

                        <td class="px-4 py-4">
                            @if ($isOn)
                                <input type="time"
                                       wire:model.live="schedules.{{ $day }}.start"
                                       class="w-full max-w-[160px] rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" />
                            @else
                                <span class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                    Day off
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-4">
                            @if ($isOn)
                                <input type="time"
                                       wire:model.live="schedules.{{ $day }}.end"
                                       class="w-full max-w-[160px] rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" />
                            @else
                                <span class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-gray-500">
                                    —
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-4 text-right">
                            @if ($isOn && $dayHours !== null)
                                <span class="text-sm font-semibold text-slate-700 dark:text-gray-300">
                                    {{ $dayHours }} h
                                </span>
                            @elseif ($isOn)
                                <span class="text-xs font-medium text-red-500">Invalid</span>
                            @else
                                <span class="text-xs text-slate-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                    </tr>

                    @error("schedules.{$day}.end")
                        <tr>
                            <td colspan="5" class="bg-red-50 dark:bg-red-500/10 px-4 py-2">
                                <p class="text-xs text-red-600 dark:text-red-400">
                                    {{ $label }}: {{ $message }}
                                </p>
                            </td>
                        </tr>
                    @enderror
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Bottom save bar --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('owner.employees') }}" wire:navigate
           class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700">
            Back
        </a>
        <button wire:click="save" wire:loading.attr="disabled" wire:target="save" type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
            <span wire:loading.remove wire:target="save">Save schedule</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </div>
</div>