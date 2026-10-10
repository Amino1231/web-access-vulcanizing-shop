<div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 dark:bg-gray-950">
    <div class="w-full max-w-md space-y-6">

        <div class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#FF5E14] shadow-lg shadow-orange-500/30">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" stroke-width="2.5"/>
                    <circle cx="12" cy="12" r="3" stroke-width="2"/>
                    <path stroke-linecap="round" d="M12 3v3m0 12v3M3 12h3m12 0h3"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reset your password</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Enter your email and we'll send you a link to reset it.
            </p>
        </div>

        @if ($sent)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center dark:border-emerald-500/30 dark:bg-emerald-500/10">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Check your inbox</p>
                <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-400">
                    If an account exists for <strong>{{ $email }}</strong>, you'll receive a password reset link shortly.
                </p>
                <p class="mt-3 text-xs text-emerald-600 dark:text-emerald-500">
                    Didn't get it? Check your spam folder, or wait a minute and try again.
                </p>
            </div>
        @else
            <form wire:submit="sendResetLink"
                  class="space-y-5 rounded-2xl border border-gray-100 bg-white p-8 shadow-xl shadow-gray-200/60 dark:border-gray-800 dark:bg-gray-900 dark:shadow-black/40">

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                        Email address
                    </label>
                    <input wire:model="email" id="email" type="email" autocomplete="email" autofocus
                           placeholder="juan@example.com"
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500" />
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="sendResetLink"
                        class="flex w-full items-center justify-center rounded-lg bg-[#FF5E14] px-3 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-50">
                    <span wire:loading.remove wire:target="sendResetLink">Send reset link</span>
                    <span wire:loading wire:target="sendResetLink" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Sending…
                    </span>
                </button>

                <p class="pt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Remembered it?
                    <a href="{{ route('login') }}" wire:navigate
                       class="font-bold text-[#FF5E14] transition hover:text-[#D94E10] dark:hover:text-orange-400">
                        Back to sign in
                    </a>
                </p>
            </form>
        @endif
    </div>
</div>