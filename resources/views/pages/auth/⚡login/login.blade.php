<div>
  <div class="grid min-h-screen lg:grid-cols-2 bg-white dark:bg-gray-950">

    <!-- LEFT SIDE — Welcome / Engagement Panel -->
    <div class="relative hidden lg:flex items-center justify-end overflow-hidden bg-white dark:bg-gray-950 px-12 xl:px-16">

      <div class="w-full max-w-md">

        <!-- Welcome copy -->
        <h1 class="text-4xl xl:text-5xl font-extrabold text-gray-900 dark:text-white leading-tight mb-6">
          Welcome back to <br/>
          <span class="text-[#FF5E14]">VulcanPro.</span>
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed mb-8">
          Your trusted vulcanizing partner. Sign in to book services, track your repairs, and manage your tire needs — all in one place.
        </p>

        <!-- Feature bullets -->
        <ul class="space-y-4 mb-10">
          <li class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center mt-0.5">
              <svg class="w-3.5 h-3.5 text-[#FF5E14]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-gray-700 dark:text-gray-300">Book appointments & track service status in real time</span>
          </li>
          <li class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center mt-0.5">
              <svg class="w-3.5 h-3.5 text-[#FF5E14]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-gray-700 dark:text-gray-300">Exclusive member discounts & priority roadside assistance</span>
          </li>
          <li class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center mt-0.5">
              <svg class="w-3.5 h-3.5 text-[#FF5E14]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-gray-700 dark:text-gray-300">Full service history & digital warranty records</span>
          </li>
        </ul>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 pt-8 border-t border-gray-200 dark:border-gray-800">
          <div>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">15+</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Years of Service</p>
          </div>
          <div>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">10K+</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Happy Customers</p>
          </div>
          <div>
            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">24/7</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Roadside Help</p>
          </div>
        </div>

      </div>
    </div>

    <!-- RIGHT SIDE — Sign In Form -->
    <div class="flex items-center justify-start bg-white dark:bg-gray-950 px-4 py-12 sm:px-6 lg:px-12 xl:px-16">
      <div class="w-full max-w-md">

        <!-- Mobile-only header -->
        <div class="lg:hidden text-center mb-8">
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[#FF5E14] mb-4 shadow-lg shadow-orange-500/30">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="9" stroke-width="2.5"/>
              <circle cx="12" cy="12" r="3" stroke-width="2"/>
              <path stroke-linecap="round" d="M12 3v3m0 12v3M3 12h3m12 0h3"/>
            </svg>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back</h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Sign in to continue to VulcanPro</p>
        </div>

        <!-- Form header (desktop) -->
        <div class="hidden lg:block mb-8">
          <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Sign in to your account</h2>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Welcome back! Please enter your details below.</p>
        </div>

        <form wire:submit="login" class="space-y-5 rounded-2xl bg-white dark:bg-gray-900 p-8 shadow-xl shadow-gray-200/60 dark:shadow-black/40 border border-gray-100 dark:border-gray-800">

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Email address</label>
                <input wire:model="email" id="email" type="email" autocomplete="email" placeholder="example@gmail.com"
                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 outline-none py-2.5 px-3.5 text-sm text-gray-900 dark:text-white transition">
                @error('email') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Password</label>
                <div class="relative">
                  <input wire:model="password" id="password" type="password" autocomplete="current-password" placeholder="••••••••"
                      class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:border-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 outline-none py-2.5 px-3.5 pr-11 text-sm text-gray-900 dark:text-white transition">
                  <button type="button" onclick="const p=document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
                @error('password') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p> @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                    <input wire:model="remember" type="checkbox" 
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 dark:bg-gray-800">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition">Remember me</span>
                </label>
                <a href="#" class="text-sm font-semibold text-[#FF5E14] hover:text-[#D94E10] dark:hover:text-orange-400 transition">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" wire:loading.attr="disabled" wire:target="login"
                class="flex w-full justify-center items-center rounded-lg bg-[#FF5E14] px-3 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 hover:bg-[#D94E10] hover:shadow-orange-500/40 focus:outline-none focus:ring-2 focus:ring-[#FF5E14] focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 transition-all hover:-translate-y-0.5">
                <span wire:loading.remove wire:target="login">Sign in</span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Signing in…
                </span>
            </button>

            <!-- Divider -->
            <div class="relative my-2">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
              </div>
              <div class="relative flex justify-center text-xs">
                <span class="bg-white dark:bg-gray-900 px-3 text-gray-500 dark:text-gray-400 font-medium">OR CONTINUE WITH</span>
              </div>
            </div>

            <!-- Social Buttons -->
            <div class="grid grid-cols-2 gap-3">
              <button type="button" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                  <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                  <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                  <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
              </button>
              <button type="button" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="#1877F2"/>
                </svg>
                Facebook
              </button>
            </div>

            <!-- Register Link -->
            <p class="text-center text-sm text-gray-600 dark:text-gray-400 pt-2">
                Don't have an account?
                <a href="{{ route('register') }}" wire:navigate class="font-bold text-[#FF5E14] hover:text-[#D94E10] dark:hover:text-orange-400 transition">Create one now</a>
            </p>
        </form>

        <!-- Trust footer -->
        <div class="mt-8 flex items-center justify-center gap-6 text-xs text-gray-500 dark:text-gray-500">
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Secure login
          </div>
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            SSL protected
          </div>
        </div>

      </div>
    </div>
  </div>
</div>