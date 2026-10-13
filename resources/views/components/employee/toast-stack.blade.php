{{-- resources/views/components/toast-stack.blade.php --}}
<div class="pointer-events-none fixed right-4 top-4 z-50 flex flex-col gap-2">
    <template x-for="(n, i) in notifications" :key="i">
        <div
            x-transition.opacity.duration.200ms
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