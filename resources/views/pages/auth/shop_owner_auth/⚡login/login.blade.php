<div>
  <div class="min-h-screen bg-white dark:bg-gray-950">
    <div class="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
      <div class="grid w-full overflow-hidden rounded-[28px] border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-[0_30px_80px_rgba(15,23,42,0.08)] dark:shadow-black/40 lg:grid-cols-[1.15fr_0.85fr]">

        <!-- LEFT SIDE — Owner info panel -->
        <div class="relative hidden flex-col justify-between bg-white dark:bg-gray-950 p-8 lg:flex border-r border-slate-200 dark:border-gray-800">
          <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-600 dark:text-gray-300">
              <span class="w-1.5 h-1.5 rounded-full bg-[#FF5E14]"></span>
              Shop owner access
            </div>
          </div>

          <div class="space-y-5">
            <div class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">
              Sell smarter. <br/>
              <span class="text-[#FF5E14]">Serve faster.</span>
            </div>
            <p class="max-w-md text-sm leading-7 text-slate-600 dark:text-gray-400">
              Manage your inventory, products, orders, and storefront with a clean, focused owner dashboard built for everyday operations.
            </p>
          </div>

          <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-900 p-4">
            <div class="flex items-center justify-between text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
              <span>Business status</span>
              <span class="rounded-full bg-emerald-100 dark:bg-emerald-500/20 px-2 py-1 text-[10px] font-semibold text-emerald-700 dark:text-emerald-300">Online</span>
            </div>
            <div class="mt-4 flex items-end gap-3">
              <div class="text-3xl font-bold text-slate-900 dark:text-white">24K</div>
              <div class="pb-1 text-sm text-slate-500 dark:text-gray-400">monthly sales</div>
            </div>
          </div>
        </div>

        <!-- RIGHT SIDE — Owner login form -->
        <div class="flex items-center justify-center bg-white dark:bg-gray-900 p-6 sm:p-10">
          <div class="w-full max-w-md">
            <div class="mb-8 text-center lg:text-left">
              <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#FF5E14] text-lg font-black text-white shadow-lg shadow-orange-500/30 lg:mx-0">
                VS
              </div>
              <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500 dark:text-gray-400">Welcome back</p>
              <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Owner login</h1>
              <p class="mt-2 text-sm text-slate-600 dark:text-gray-400">Sign in to access your shop dashboard.</p>
            </div>

            <form wire:submit="login" class="space-y-5">
              <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-800 dark:text-gray-200">Email address</label>
                <input
                  id="email"
                  wire:model.defer="email"
                  type="email"
                  autocomplete="email"
                  placeholder="you@shop.com"
                  class="w-full rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition placeholder:text-slate-400 dark:placeholder:text-gray-500 focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#FF5E14]/15"
                >
                @error('email')
                  <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-800 dark:text-gray-200">Password</label>
                <input
                  id="password"
                  wire:model.defer="password"
                  type="password"
                  autocomplete="current-password"
                  placeholder="Enter your password"
                  class="w-full rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-4 py-3 text-sm text-slate-900 dark:text-white outline-none transition placeholder:text-slate-400 dark:placeholder:text-gray-500 focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#FF5E14]/15"
                >
                @error('password')
                  <p class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
              </div>

              <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                  <input type="checkbox" class="h-4 w-4 rounded border-slate-300 dark:border-gray-600 text-[#FF5E14] focus:ring-2 focus:ring-[#FF5E14]/30 dark:bg-gray-800">
                  <span class="ml-2 text-sm text-slate-700 dark:text-gray-300 group-hover:text-slate-900 dark:group-hover:text-white transition">Remember me</span>
                </label>
                <a href="#" class="text-sm font-semibold text-[#FF5E14] hover:text-[#D94E10] dark:hover:text-orange-400 transition">Forgot password?</a>
              </div>

              <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-[#FF5E14] px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] hover:shadow-orange-500/40 focus:outline-none focus:ring-2 focus:ring-[#FF5E14] focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:cursor-not-allowed disabled:opacity-70 hover:-translate-y-0.5"
              >
                <span wire:loading.remove>Sign in to dashboard</span>
                <span wire:loading class="flex items-center gap-2">
                  <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Signing in...
                </span>
              </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500 dark:text-gray-400">
              Need help? <a href="{{ route('index.page') }}" class="font-semibold text-[#FF5E14] hover:text-[#D94E10] dark:hover:text-orange-400 transition">Go back home</a>
            </p>

            <!-- Trust footer -->
            <div class="mt-8 flex items-center justify-center gap-6 text-xs text-slate-500 dark:text-gray-500">
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Secure login
              </div>
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                SSL protected
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>