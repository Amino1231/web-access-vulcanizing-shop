<div class="min-h-screen bg-slate-100 dark:bg-gray-950">
    <div class="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-[28px] border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-[0_30px_80px_rgba(15,23,42,0.08)] lg:grid-cols-[1.05fr_0.95fr]">

            {{-- LEFT: brand panel --}}
            <div class="relative hidden flex-col justify-between bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-8 text-white lg:flex">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-200">
                        Start your shop
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="text-4xl font-black tracking-tight">Open your business profile.</div>
                    <p class="max-w-md text-sm leading-7 text-slate-300">
                        Create your store account, send your business details for review, and access your dashboard only after approval.
                    </p>
                </div>
            </div>

            {{-- RIGHT: form --}}
            <div class="flex items-center justify-center bg-white dark:bg-gray-900 p-6 sm:p-10">
                <div class="w-full max-w-xl">

                    <div class="mb-8 text-center lg:text-left">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-lg font-black text-white shadow-sm lg:mx-0">
                            VS
                        </div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500 dark:text-gray-400">Create account</p>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Owner registration</h1>
                    </div>

                    {{-- Global error (rate limits, server-side failures) --}}
                    @if ($errorMessage)
                        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                            {{ $errorMessage }}
                        </div>
                    @endif

                    <form wire:submit="register" class="space-y-5">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="name" class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                    Full name
                                </label>
                                <input wire:model.blur="name" id="name" type="text" autocomplete="name"
                                       class="w-full rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition focus:border-slate-900 dark:focus:border-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-slate-100 dark:focus:ring-gray-800"
                                       placeholder="Juan Dela Cruz" />
                                @error('name') <span class="mt-2 block text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="email" class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                    Email
                                </label>
                                <input wire:model.blur="email" id="email" type="email" autocomplete="email"
                                       class="w-full rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition focus:border-slate-900 dark:focus:border-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-slate-100 dark:focus:ring-gray-800"
                                       placeholder="owner@shop.com" />
                                @error('email') <span class="mt-2 block text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div x-data="{ show: false }">
                                <label for="password" class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                    Password
                                </label>
                                <div class="relative">
                                    <input wire:model.blur="password" id="password"
                                           :type="show ? 'text' : 'password'"
                                           autocomplete="new-password"
                                           class="w-full rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-4 py-3 pr-11 text-sm text-slate-900 dark:text-white outline-none transition focus:border-slate-900 dark:focus:border-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-slate-100 dark:focus:ring-gray-800"
                                           placeholder="Minimum 8 characters" />
                                    <button type="button" @click="show = !show"
                                            :aria-label="show ? 'Hide password' : 'Show password'"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600 dark:hover:text-gray-200">
                                        <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password') <span class="mt-2 block text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                <p class="mt-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                                    At least 8 characters with uppercase, lowercase, and a number.
                                </p>
                            </div>

                            <div x-data="{ show: false }">
                                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                                    Confirm password
                                </label>
                                <div class="relative">
                                    <input wire:model.blur="password_confirmation" id="password_confirmation"
                                           :type="show ? 'text' : 'password'"
                                           autocomplete="new-password"
                                           class="w-full rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-4 py-3 pr-11 text-sm text-slate-900 dark:text-white outline-none transition focus:border-slate-900 dark:focus:border-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-slate-100 dark:focus:ring-gray-800"
                                           placeholder="Repeat password" />
                                    <button type="button" @click="show = !show"
                                            :aria-label="show ? 'Hide password' : 'Show password'"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600 dark:hover:text-gray-200">
                                        <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password_confirmation') <span class="mt-2 block text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="register"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 dark:bg-gray-800 px-4 py-3.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="register">Create owner account</span>
                            <span wire:loading wire:target="register" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Submitting…
                            </span>
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-slate-500 dark:text-gray-400">
                        Already have an account?
                        <a href="{{ route('login') }}"
                           class="font-semibold text-slate-700 dark:text-gray-200 hover:text-slate-900 dark:hover:text-white">
                            Login here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>