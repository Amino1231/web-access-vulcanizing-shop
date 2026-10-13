<div class="mx-auto max-w-3xl space-y-6">

    @if (! $this->employee)
        <div class="rounded-2xl border border-dashed border-red-300 dark:border-red-500/40 bg-red-50 dark:bg-red-500/5 p-10 text-center">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">
                Your account isn't linked to any shop yet.
            </p>
        </div>
    @else
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">
                    {{ $this->employee->tenant?->name ?? 'Employee portal' }}
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    My Profile
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Your details on file with this shop.
                </p>
            </div>

            <a href="{{ route('employee.update_profile') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Update profile
            </a>
        </div>

        {{-- Identity card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

                @if ($this->user->avatar)
                    <img src="{{ asset('storage/' . $this->user->avatar) }}"
                         alt="{{ $this->user->name }}"
                         class="h-24 w-24 shrink-0 rounded-2xl border border-slate-200 object-cover dark:border-gray-700" />
                @else
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-2xl font-bold text-white dark:bg-gray-700">
                        {{ strtoupper(substr($this->employee->name, 0, 2)) }}
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-xl font-bold text-slate-900 dark:text-white">
                        {{ $this->employee->name }}
                    </h2>
                    <p class="mt-0.5 text-sm font-medium text-[#FF5E14]">
                        {{ $this->employee->position ?? 'Employee' }}
                    </p>
                    @if ($this->employee->hired_at)
                        <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                            With {{ $this->employee->tenant?->name }} since {{ $this->employee->hired_at->format('F Y') }}
                        </p>
                    @endif
                </div>

                <div class="shrink-0">
                    <x-employee.status-badge :status="$this->employee->is_active ? 'confirmed' : 'cancelled'" />
                </div>
            </div>
        </div>

        {{-- Contact details --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                Contact
            </h3>

            <dl class="grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-1 break-all text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $this->user->email }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Phone</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        @if ($this->employee->phone)
                            <a href="tel:{{ $this->employee->phone }}" class="text-[#FF5E14] hover:underline">
                                {{ $this->employee->phone }}
                            </a>
                        @else
                            <span class="text-slate-400 dark:text-gray-500">Not set</span>
                        @endif
                    </dd>
                </div>

                @if ($this->user->address)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Address</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $this->user->address }}
                        </dd>
                    </div>
                @endif

                @if ($this->user->birth_date)
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Birth date</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $this->user->birth_date->format('F d, Y') }}
                        </dd>
                    </div>
                @endif

                @if ($this->user->gender)
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Gender</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ ucfirst($this->user->gender) }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Employment details --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">
                Employment
            </h3>

            <dl class="grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Shop</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $this->employee->tenant?->name ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Position</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $this->employee->position ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Hired</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $this->employee->hired_at?->format('F d, Y') ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-slate-500 dark:text-gray-400">Commission rate</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ number_format((float) $this->employee->commission_rate, 2) }}%
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Info note --}}
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/10">
            <div class="flex gap-3">
                <svg class="h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    Position, hired date, and commission rate are set by your shop owner. Contact them if anything is incorrect.
                </p>
            </div>
        </div>
    @endif
</div>