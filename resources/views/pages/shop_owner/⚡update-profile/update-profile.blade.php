

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Update profile</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Manage your account and shop details.</p>
        </div>

        <a href="{{ route('owner.profile') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Account section --}}
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-gray-400">Account</h2>

            <div class="flex items-center gap-4">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-gray-700" />
                @elseif (Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-gray-700" />
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-900 dark:bg-gray-700 text-lg font-bold text-white">
                        {{ strtoupper(substr($name ?: 'U', 0, 2)) }}
                    </div>
                @endif

                <label class="cursor-pointer rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 transition">
                    Change photo
                    <input type="file" wire:model="avatar" accept="image/*" class="hidden" />
                </label>
            </div>
            @error('avatar') <p class="text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Full name</label>
                    <input type="text" wire:model="name"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('name') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Email</label>
                    <input type="email" wire:model="email"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('email') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Phone</label>
                    <input type="text" wire:model="phone"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('phone') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Birth date</label>
                    <input type="date" wire:model="birth_date"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('birth_date') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Gender</label>
                    <select wire:model="gender"
                            class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20">
                        <option value="">Prefer not to say</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    @error('gender') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Address</label>
                    <input type="text" wire:model="address"
                           class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                    @error('address') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Shop section --}}
        @if ($tenantId)
            <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-gray-400">Shop</h2>

                <div class="flex items-center gap-4">
                    @if ($shop_logo)
                        <img src="{{ $shop_logo->temporaryUrl() }}" class="h-16 w-16 rounded-xl object-cover border border-slate-200 dark:border-gray-700" />
                    @elseif (Auth::user()->tenant?->logo)
                        <img src="{{ asset('storage/' . Auth::user()->tenant->logo) }}" class="h-16 w-16 rounded-xl object-cover border border-slate-200 dark:border-gray-700" />
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-900 dark:bg-gray-700 text-lg font-bold text-white">
                            {{ strtoupper(substr($shop_name ?: 'S', 0, 2)) }}
                        </div>
                    @endif

                    <label class="cursor-pointer rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 transition">
                        Change logo
                        <input type="file" wire:model="shop_logo" accept="image/*" class="hidden" />
                    </label>
                </div>
                @error('shop_logo') <p class="text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Shop name</label>
                        <input type="text" wire:model="shop_name"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('shop_name') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Shop email</label>
                        <input type="email" wire:model="shop_email"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('shop_email') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Shop phone</label>
                        <input type="text" wire:model="shop_phone"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('shop_phone') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Shop address</label>
                        <input type="text" wire:model="shop_address"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                        @error('shop_address') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @endif

        <div class="flex justify-end gap-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="save,avatar,shop_logo"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>