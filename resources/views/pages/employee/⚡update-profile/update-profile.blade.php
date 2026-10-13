<div class="mx-auto max-w-3xl space-y-6"
     x-data="{ notifications: [] }"
     @notify.window="
        notifications.push($event.detail);
        setTimeout(() => notifications.shift(), 4000);
     ">

    {{-- Toast stack --}}
    <div class="pointer-events-none fixed right-4 top-4 z-50 flex flex-col gap-2">
        <template x-for="(n, i) in notifications" :key="i">
            <div x-transition.opacity.duration.200ms
                 class="pointer-events-auto w-80 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg"
                 :class="{
                     'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-300': n.type === 'success',
                     'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300': n.type === 'error'
                 }">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>

    @if (! $this->employee)
        <div class="rounded-2xl border border-dashed border-red-300 dark:border-red-500/40 bg-red-50 dark:bg-red-500/5 p-10 text-center">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                Your account isn't linked to any shop yet.
            </p>
        </div>
    @else
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                    {{ $this->employee->tenant?->name ?? 'Employee portal' }}
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Update Profile
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Keep your contact details up to date.
                </p>
            </div>

            <a href="{{ route('employee.profile') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        </div>

        <form wire:submit="save" class="space-y-6">

            {{-- Avatar --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Photo
                </h3>

                <div class="flex items-center gap-5">
                    <div class="shrink-0">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}"
                                 class="h-20 w-20 rounded-2xl object-cover border border-slate-200 dark:border-gray-700" />
                        @elseif ($this->user->avatar)
                            <img src="{{ asset('storage/' . $this->user->avatar) }}"
                                 class="h-20 w-20 rounded-2xl object-cover border border-slate-200 dark:border-gray-700" />
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-white dark:bg-gray-700">
                                {{ strtoupper(substr($this->user->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Choose photo
                            <input type="file" wire:model="avatar" accept="image/*" class="hidden" />
                        </label>
                        <p class="mt-2 text-xs text-slate-500 dark:text-gray-400">
                            JPG or PNG. Max 2 MB.
                        </p>
                        @error('avatar') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="avatar" class="mt-2 text-xs text-slate-500">
                            Uploading…
                        </div>
                    </div>
                </div>
            </div>

            {{-- Personal --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Personal info
                </h3>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Full name <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text" required
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" type="email" required
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Phone
                        </label>
                        <input wire:model="phone" type="text" placeholder="+63 912 345 6789"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('phone') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Birth date
                        </label>
                        <input wire:model="birth_date" type="date"
                               class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100" />
                        @error('birth_date') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Gender
                        </label>
                        <select wire:model="gender"
                                class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100">
                            <option value="">Prefer not to say</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        @error('gender') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Address
                        </label>
                        <textarea wire:model="address" rows="2"
                                  class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-[#FF5E14] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-orange-100"></textarea>
                        @error('address') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Read-only employment note --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-gray-800 dark:bg-gray-900/60">
                <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                    Locked fields
                </h3>
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Position, hired date, and commission rate are managed by your shop owner.
                    Contact them if any of these need updating.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('employee.profile') }}"
                   class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-gray-200 transition hover:bg-slate-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </form>
    @endif
</div>