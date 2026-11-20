<footer class="border-t border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-950 mt-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

        {{-- Top: Brand and simple links --}}
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            
            {{-- Brand --}}
            <div class="flex flex-col items-center md:items-start gap-3">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FF5E14] shadow-sm shadow-orange-500/30">
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2.5" />
                            <circle cx="12" cy="12" r="3" stroke-width="2" />
                            <path stroke-linecap="round" d="M12 3v3m0 12v3M3 12h3m12 0h3" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">Vulcan</span>
                        <span class="text-lg font-light text-[#FF5E14]">Pro</span>
                    </div>
                </div>
                <p class="text-sm text-center md:text-left text-slate-500 dark:text-gray-400 max-w-xs">
                    Your trusted marketplace for tire and vulcanizing products from verified local shops.
                </p>
            </div>

            {{-- Social Icons --}}
            <div class="flex gap-2">
                <a href="#" title="Facebook"
                   class="flex h-8 w-8 items-center justify-center rounded-lg bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 text-slate-600 dark:text-gray-300 transition hover:bg-[#FF5E14] hover:text-white hover:border-[#FF5E14]">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="#" title="Instagram"
                   class="flex h-8 w-8 items-center justify-center rounded-lg bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 text-slate-600 dark:text-gray-300 transition hover:bg-[#FF5E14] hover:text-white hover:border-[#FF5E14]">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-8 flex flex-col gap-4 border-t border-slate-200 dark:border-gray-800 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500 dark:text-gray-500 text-center sm:text-left">
                &copy; {{ date('Y') }} VulcanPro. All rights reserved.
            </p>
            <div class="flex justify-center gap-x-5 gap-y-2 text-xs">
                <a href="{{ route('privacy_policy') }}" target="_blank" class="text-slate-500 dark:text-gray-500 transition hover:text-[#FF5E14]">Privacy Policy</a>
                <a href="{{ route('terms_of_service') }}" target="_blank" class="text-slate-500 dark:text-gray-500 transition hover:text-[#FF5E14]">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>