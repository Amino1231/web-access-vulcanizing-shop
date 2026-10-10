<div class="grid min-h-screen lg:grid-cols-2 bg-white dark:bg-gray-950">

    {{-- LEFT — Welcome / Brand panel (desktop only) --}}
    <div class="relative hidden lg:flex items-center justify-end overflow-hidden bg-slate-50 dark:bg-gray-950 px-12 xl:px-16">
        <div class="pointer-events-none absolute -top-32 -right-24 h-96 w-96 rounded-full bg-orange-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 h-72 w-72 rounded-full bg-orange-300/10 blur-3xl"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-10 flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#FF5E14] shadow-lg shadow-orange-500/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-width="2.5"/>
                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                        <path stroke-linecap="round" d="M12 3v3m0 12v3M3 12h3m12 0h3"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight">Vulcan<span class="text-[#FF5E14]">Pro</span></p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Multi-shop platform</p>
                </div>
            </div>

            <h1 class="text-4xl xl:text-5xl font-extrabold text-gray-900 dark:text-white leading-tight mb-6">
                Join thousands of <br/>
                <span class="text-[#FF5E14]">happy customers.</span>
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed mb-8">
                Create your free account and start booking vulcanizing services from trusted shops near you — in minutes.
            </p>

            <ul class="space-y-4 mb-10">
                @foreach ([
                    'Free account — no credit card required',
                    'Book services at any shop in the network',
                    'Track repairs & get real-time updates',
                    'Digital warranty records for every service',
                ] as $feature)
                    <li class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-500/20">
                            <svg class="h-3.5 w-3.5 text-[#FF5E14]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="grid grid-cols-3 gap-6 border-t border-gray-200 dark:border-gray-800 pt-8">
                <div>
                    <p class="text-3xl font-extrabold text-gray-900 dark:text-white">15+</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Years of Service</p>
                </div>
                <div>
                    <p class="text-3xl font-extrabold text-gray-900 dark:text-white">10K+</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Happy Customers</p>
                </div>
                <div>
                    <p class="text-3xl font-extrabold text-gray-900 dark:text-white">24/7</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Roadside Help</p>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT — Registration form --}}
    <div class="flex items-center justify-center bg-white dark:bg-gray-950 px-4 py-12 sm:px-6 lg:px-12 xl:px-16">
        <div class="w-full max-w-md">

            {{-- Mobile-only brand header --}}
            <div class="lg:hidden text-center mb-8">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#FF5E14] shadow-lg shadow-orange-500/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-width="2.5"/>
                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                        <path stroke-linecap="round" d="M12 3v3m0 12v3M3 12h3m12 0h3"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create your account</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Join us today — it only takes a minute</p>
            </div>

            {{-- Desktop heading --}}
            <div class="hidden lg:block mb-8">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Create your account</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Join us today! Fill in your details below.</p>
            </div>

            {{-- Global error (rate limits, server-side failures) --}}
            @if ($errorMessage)
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    {{ $errorMessage }}
                </div>
            @endif

            <form wire:submit="register"
                  class="space-y-5 rounded-2xl bg-white dark:bg-gray-900 p-6 sm:p-8 shadow-xl shadow-gray-200/60 dark:shadow-black/40 border border-gray-100 dark:border-gray-800">

                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Full name
                    </label>
                    <input wire:model.blur="name" id="name" type="text" autocomplete="name"
                           placeholder="Juan Dela Cruz"
                           class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 outline-none py-2.5 px-3.5 text-sm text-gray-900 dark:text-white transition" />
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Email address
                    </label>
                    <input wire:model.blur="email" id="email" type="email" autocomplete="email"
                           placeholder="juan@gmail.com"
                           class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 outline-none py-2.5 px-3.5 text-sm text-gray-900 dark:text-white transition" />
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div x-data="{ show: false }">
                    <label for="password" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input wire:model.blur="password" id="password"
                               :type="show ? 'text' : 'password'"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 outline-none py-2.5 px-3.5 pr-11 text-sm text-gray-900 dark:text-white transition" />
                        <button type="button"
                                @click="show = !show"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-300">
                            <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                        At least 8 characters with uppercase, lowercase, and a number.
                    </p>
                </div>

                {{-- Confirm Password --}}
                <div x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Confirm password
                    </label>
                    <div class="relative">
                        <input wire:model.blur="password_confirmation" id="password_confirmation"
                               :type="show ? 'text' : 'password'"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 outline-none py-2.5 px-3.5 pr-11 text-sm text-gray-900 dark:text-white transition" />
                        <button type="button"
                                @click="show = !show"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-300">
                            <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1.5 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" wire:loading.attr="disabled" wire:target="register"
                        class="flex w-full items-center justify-center rounded-lg bg-[#FF5E14] px-3 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition-all hover:-translate-y-0.5 hover:bg-[#D94E10] hover:shadow-orange-500/40 focus:outline-none focus:ring-2 focus:ring-[#FF5E14] focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-900">
                    <span wire:loading.remove wire:target="register">Create account</span>
                    <span wire:loading wire:target="register" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating account…
                    </span>
                </button>

                {{-- Sign In Link --}}
                <p class="pt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Already have an account?
                    <a href="{{ route('login') }}" wire:navigate
                       class="font-bold text-[#FF5E14] transition hover:text-[#D94E10] dark:hover:text-orange-400">
                        Sign in
                    </a>
                </p>
            </form>

            {{-- Trust footer --}}
            <div class="mt-8 flex items-center justify-center gap-6 text-xs text-gray-500 dark:text-gray-500">
                <div class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Secure signup
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    SSL protected
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>