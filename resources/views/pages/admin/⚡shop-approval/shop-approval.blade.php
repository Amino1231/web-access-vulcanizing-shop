<div class="space-y-6">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Platform management</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Shop approval queue</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review pending applications, re-approve resubmitted shops, or reject
                with feedback.</p>
        </div>
        <div class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
            {{ count($shops) }} {{ \Illuminate\Support\Str::plural('submission', count($shops)) }}
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-400">
        {{ session('success') }}
    </div>
    @endif

    @if (empty($shops))
    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
        No business submissions are waiting for review.
    </div>
    @else
    <div class="space-y-4">
        @foreach ($shops as $shop)
        @php
        $statusColor = match ($shop['status']) {
        'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-400',
        'rejected' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400',
        'under_review' => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-400',
        'needs_more_info' => 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-900/40 dark:bg-purple-950/30 dark:text-purple-400',
        'pending' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-400',
        default => 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
        };
        @endphp

        <div wire:key="shop-{{ $shop['id'] }}"
            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $shop['shop_name'] }}</h2>
                        <span
                            class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] {{ $statusColor }}">
                            {{ $shop['status_label'] }}
                        </span>
                    </div>

                    <div class="grid gap-3 text-sm text-slate-600 md:grid-cols-2 dark:text-slate-400">
                        <div><span class="font-medium text-slate-800 dark:text-slate-200">Owner:</span> {{ $shop['owner_name'] }}</div>
                        <div><span class="font-medium text-slate-800 dark:text-slate-200">Email:</span> {{ $shop['owner_email'] }}</div>
                        <div><span class="font-medium text-slate-800 dark:text-slate-200">Phone:</span> {{ $shop['phone'] }}</div>
                        <div><span class="font-medium text-slate-800 dark:text-slate-200">Submitted:</span> {{ $shop['submitted_at'] }}
                        </div>
                    </div>

                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        <span class="font-medium text-slate-800 dark:text-slate-200">Address:</span> {{ $shop['address'] }}
                    </div>

                    @if (! empty($shop['rejection_reason']))
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400">
                        <span class="font-semibold">Reject reason:</span> {{ $shop['rejection_reason'] }}
                    </div>
                    @endif

                    @if (! empty($shop['missing_requirements']))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Missing requirements:</span>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($shop['missing_requirements'] as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if ($shop['attachment'])
                    <a href="{{ asset('storage/' . $shop['attachment']) }}" target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        View attachment
                    </a>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                    @if ($shop['status'] === 'rejected')
                    <button wire:click="approve({{ $shop['id'] }})" type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 dark:bg-emerald-600 dark:hover:bg-emerald-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Re-approve
                    </button>
                    @else
                    <button wire:click="approve({{ $shop['id'] }})" type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 dark:bg-emerald-600 dark:hover:bg-emerald-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approve
                    </button>
                    @endif

                    {{-- Needs more info --}}
                    @if (! in_array($shop['status'], ['approved']))
                    <button wire:click="openNeedsInfo({{ $shop['id'] }})" type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-purple-200 bg-purple-50 px-4 py-2.5 text-sm font-semibold text-purple-700 transition hover:bg-purple-100 dark:border-purple-900/40 dark:bg-purple-950/30 dark:text-purple-400 dark:hover:bg-purple-950/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Needs info
                    </button>
                    @endif

                    <button wire:click="openReject({{ $shop['id'] }})" type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-950/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reject
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ──────────────────────────────────────── --}}
    {{-- Reject modal --}}
    {{-- ──────────────────────────────────────── --}}
    @if ($rejectingId)
    <div x-data @keydown.escape.window="$wire.cancelReject()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm dark:bg-slate-950/70">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/40">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Reject {{ $rejectingName }}?</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                        The owner will see this reason and can resubmit with corrections.
                    </p>

                    <div class="mt-4">
                        <label for="rejectionReason" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Reason for rejection
                        </label>
                        <textarea id="rejectionReason" wire:model="rejectionReason" rows="4"
                            placeholder="e.g. Business permit document is unreadable. Please upload a clearer scan."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-orange-500 dark:focus:bg-slate-800 dark:focus:ring-orange-500/20"></textarea>
                        @error('rejectionReason')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelReject" type="button"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    Cancel
                </button>
                <button wire:click="confirmReject" wire:loading.attr="disabled" wire:target="confirmReject"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60 dark:bg-red-600 dark:hover:bg-red-500">
                    <span wire:loading.remove wire:target="confirmReject">Reject shop</span>
                    <span wire:loading wire:target="confirmReject">Rejecting…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ──────────────────────────────────────── --}}
    {{-- Needs more info modal --}}
    {{-- ──────────────────────────────────────── --}}
    @if ($needsInfoId)
    <div x-data @keydown.escape.window="$wire.cancelNeedsInfo()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 backdrop-blur-sm dark:bg-slate-950/70">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-950/40">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Request more info from {{ $needsInfoName }}?</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                        The owner will see your message and can resubmit once corrected.
                    </p>

                    <div class="mt-4">
                        <label for="needsInfoReason" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            What do they need to provide?
                        </label>
                        <textarea id="needsInfoReason" wire:model="needsInfoReason" rows="4"
                            placeholder="e.g. The BIR permit scan is unreadable. Please re-upload a clearer photo."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-purple-400 focus:bg-white focus:ring-2 focus:ring-purple-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-purple-500 dark:focus:bg-slate-800 dark:focus:ring-purple-500/20"></textarea>
                        @error('needsInfoReason')
                        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="cancelNeedsInfo" type="button"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                    Cancel
                </button>
                <button wire:click="confirmNeedsInfo" wire:loading.attr="disabled" wire:target="confirmNeedsInfo"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-700 disabled:opacity-60 dark:bg-purple-600 dark:hover:bg-purple-500">
                    <span wire:loading.remove wire:target="confirmNeedsInfo">Send request</span>
                    <span wire:loading wire:target="confirmNeedsInfo">Sending…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>