{{-- resources/views/layouts/employee/footer.blade.php --}}
@php
    $footerShop = auth()->user()?->currentEmployee()?->tenant;
@endphp

<footer class="border-t border-slate-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-col items-center justify-between gap-3 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8 dark:text-gray-400">
        <p>&copy; {{ date('Y') }} VulcanPro · Employee portal</p>
        <div class="flex items-center gap-4">
            <span class="inline-flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                {{ $footerShop?->name ?? 'No shop' }}
            </span>
            <a href="{{ route('privacy_policy') }}" target="_blank" class="hover:text-slate-900 dark:hover:text-white">Privacy</a>
            <a href="{{ route('terms_of_service') }}" target="_blank" class="hover:text-slate-900 dark:hover:text-white">Terms</a>
        </div>
    </div>
</footer>