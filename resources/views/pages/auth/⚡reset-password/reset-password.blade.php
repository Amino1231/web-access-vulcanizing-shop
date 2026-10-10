<div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 dark:bg-gray-950">
    <div class="w-full max-w-md space-y-6">

        <div class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#FF5E14] shadow-lg shadow-orange-500/30">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Set a new password</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Choose a strong password you haven't used before.
            </p>
        </div>

        @if ($errorMessage)
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit="resetPassword"
              class="space-y-5 rounded-2xl border border-gray-100 bg-white p-8 shadow-xl shadow-gray-200/60 dark:border-gray-800 dark:bg-gray-900 dark:shadow-black/40">

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                    Email address
                </label>
                <input wire:model="email" id="email" type="email" autocomplete="email"
                       class="block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                @error('email')
                    <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                    New password
                </label>
                <input wire:model="password" id="password" type="password" autocomplete="new-password"
                       placeholder="••••••••"
                       class="block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500" />
                @error('password')
                    <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                    At least 8 characters. Mix letters and numbers for stronger security.
                </p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                    Confirm new password
                </label>
                <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password"
                       placeholder="••••••••"
                       class="block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500" />
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="resetPassword"
                    class="flex w-full items-center justify-center rounded-lg bg-[#FF5E14] px-3 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-50">
                <span wire:loading.remove wire:target="resetPassword">Update password</span>
                <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Updating…
                </span>
            </button>
        </form>
    </div>
</div>