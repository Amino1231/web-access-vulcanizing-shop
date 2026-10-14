<div class="bg-gray-50 dark:bg-gray-950 min-h-screen">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        @php $post = $this->post; @endphp

        @if (! $post)
        <div
            class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-12 text-center">
            <p class="text-sm text-slate-500 dark:text-gray-400">This service is no longer available.</p>
            <a href="{{ route('customer.services.index') }}"
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white">
                Browse services
            </a>
        </div>
        @else

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <a href="{{ route('customer.services.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to services
                </a>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $post->name }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    {{ $post->tenant?->name }} · {{ $post->service?->category?->name ?? 'Service' }}
                </p>

                @php $stats = $this->reviewStats; @endphp
                @if ($stats['count'] > 0)
                <div class="mt-2 flex items-center gap-2">
                    <div class="flex items-center gap-0.5">
                        @for ($i = 1; $i <= 5; $i++) <svg
                            class="h-4 w-4 {{ $i <= round($stats['average']) ? 'text-yellow-400' : 'text-slate-300 dark:text-gray-600' }}"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            @endfor
                    </div>
                    <span class="text-sm font-semibold text-slate-700 dark:text-gray-300">
                        {{ number_format($stats['average'], 1) }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-gray-400">
                        ({{ $stats['count'] }} {{ \Illuminate\Support\Str::plural('review', $stats['count']) }})
                    </span>
                </div>
                @endif
            </div>
            <div class="shrink-0 text-right">
                <p class="text-2xl font-bold text-[#FF5E14]">₱{{ number_format($post->price, 2) }}</p>
                @if ($post->service?->duration_minutes)
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    ~{{ $post->service->duration_minutes }} min
                </p>
                @endif
            </div>
        </div>

        @if ($post->description)
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <h2 class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">About</h2>
            <p class="text-sm leading-relaxed text-slate-600 dark:text-gray-400">{{ $post->description }}</p>
        </div>
        @endif

        {{-- Booking form --}}
        <div
            class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6 space-y-6">
            @error('general')
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Choose a date</label>
                <input wire:model.live="selectedDate" type="date" min="{{ now()->toDateString() }}"
                    max="{{ now()->addMonths(2)->toDateString() }}"
                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100" />
                @error('selectedDate') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Pick a time</label>

                @php $slotList = $this->timeSlots; @endphp

                @if (count($slotList) === 0)
                <p class="text-sm text-slate-500 dark:text-gray-400">Select a date to see available slots.</p>
                @else
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                    @foreach ($slotList as $slot)
                    @php
                    $reasonLabel = match ($slot['reason']) {
                    'booked' => 'Already booked',
                    'past' => 'Time has passed',
                    'no_staff' => 'No staff scheduled',
                    default => 'Click to book',
                    };
                    @endphp
                    <button type="button" wire:click="$set('selectedTime', '{{ $slot['time'] }}')" @disabled(!
                        $slot['available']) title="{{ $reasonLabel }}"
                        class="rounded-lg border px-2 py-2 text-xs font-semibold transition
                    {{ $selectedTime === $slot['time']
                        ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14]'
                        : ($slot['available']
                            ? 'border-slate-200 bg-white text-slate-700 hover:border-orange-300 hover:bg-orange-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200'
                            : 'cursor-not-allowed border-slate-100 bg-slate-50 text-slate-300 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-600') }}">
                        {{ $slot['time'] }}
                    </button>
                    @endforeach
                </div>

                {{-- Legend — only if some slots are greyed --}}
                @php $hasUnavailable = collect($slotList)->contains(fn ($s) => ! $s['available']); @endphp
                @if ($hasUnavailable)
                <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] text-slate-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5">
                        <span
                            class="inline-block h-2.5 w-2.5 rounded-full border border-slate-200 bg-white dark:border-gray-700 dark:bg-gray-800"></span>
                        Available
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-slate-100 dark:bg-gray-800/50"></span>
                        Unavailable
                    </span>
                </div>
                @endif
                @endif

                @error('selectedTime') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Vehicle details (all optional, created inline) --}}
            <div
                class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50/60 dark:bg-gray-800/30 p-4 sm:p-5 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Vehicle details</h3>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-gray-400">
                        Optional — fill in what you know. Leave blank if you don't want to specify.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="min-w-0">
                        <label for="plateNumber"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Plate Number <span class="text-slate-400">(optional)</span>
                        </label>
                        <input wire:model="plateNumber" id="plateNumber" type="text" placeholder="e.g. ABC 1234"
                            class="block w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                        @error('plateNumber') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="min-w-0">
                        <label for="vehicleType"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Vehicle Type <span class="text-slate-400">(optional)</span>
                        </label>
                        <select wire:model="vehicleType" id="vehicleType"
                            class="block w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                            <option value="">Select a type</option>
                            @foreach ($this->vehicleTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('vehicleType') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="min-w-0">
                        <label for="brand" class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Brand <span class="text-slate-400">(optional)</span>
                        </label>
                        <input wire:model="brand" id="brand" type="text" placeholder="e.g. Toyota"
                            class="block w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                        @error('brand') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="min-w-0">
                        <label for="model" class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Model <span class="text-slate-400">(optional)</span>
                        </label>
                        <input wire:model="model" id="model" type="text" placeholder="e.g. Vios"
                            class="block w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                        @error('model') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="min-w-0 sm:col-span-2">
                        <label for="yearModel" class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                            Year Model <span class="text-slate-400">(optional)</span>
                        </label>
                        <input wire:model="yearModel" id="yearModel" type="text" placeholder="e.g. 2020"
                            class="block w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                        @error('yearModel') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">
                    Preferred staff <span class="text-slate-400">(optional)</span>
                </label>

                @if ($this->employees->isNotEmpty())
                <select wire:model="selectedEmployee"
                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100">
                    <option value="">Any available staff</option>
                    @foreach ($this->employees as $e)
                    <option value="{{ $e->id }}">{{ $e->name }} — {{ $e->position }}</option>
                    @endforeach
                </select>

                <p class="mt-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                    Showing staff scheduled to work this time.
                </p>
                @else
                <div
                    class="rounded-xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 px-3.5 py-3">
                    <p class="text-xs text-slate-500 dark:text-gray-400">
                        No staff are scheduled for this time. You can still book — the shop will assign someone.
                    </p>
                </div>
                @endif

                @error('selectedEmployee')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Notes <span
                        class="text-slate-400">(optional)</span></label>
                <textarea wire:model="notes" rows="3" placeholder="Anything the shop should know..."
                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-100"></textarea>
            </div>

            <div
                class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100 dark:border-gray-800 pt-5">
                <a href="{{ route('customer.services.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-gray-200 hover:bg-slate-50">
                    Cancel
                </a>
                <button type="button" wire:click="book" wire:loading.attr="disabled" wire:target="book"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF5E14] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-[#D94E10] disabled:opacity-60">
                    <span wire:loading.remove wire:target="book">Confirm booking</span>
                    <span wire:loading wire:target="book">Booking…</span>
                </button>
            </div>
        </div>

        {{-- REVIEWS --}}
        <section class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 sm:p-6">
            @php
            $stats = $this->reviewStats;
            $reviews = $this->reviews;
            @endphp

            <h2 class="mb-5 text-lg font-bold text-slate-900 dark:text-white">
                Reviews
                @if ($stats['count'] > 0)
                <span class="ml-2 text-sm font-normal text-slate-500 dark:text-gray-400">({{ $stats['count'] }})</span>
                @endif
            </h2>

            <div class="grid gap-6 lg:grid-cols-[260px_1fr]">

                {{-- Summary --}}
                <div>
                    @if ($stats['count'] > 0)
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-5 text-center">
                        <p class="text-4xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['average'],
                            1) }}</p>
                        <div class="mt-2 flex items-center justify-center gap-0.5">
                            @for ($i = 1; $i <= 5; $i++) <svg
                                class="h-4 w-4 {{ $i <= round($stats['average']) ? 'text-yellow-400' : 'text-slate-300 dark:text-gray-600' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                @endfor
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">{{ $stats['count'] }} {{
                            \Illuminate\Support\Str::plural('review', $stats['count']) }}</p>
                    </div>
                    @else
                    <div
                        class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                        <p class="text-sm text-slate-500 dark:text-gray-400">No reviews yet.</p>
                    </div>
                    @endif
                </div>

                {{-- List + form --}}
                <div class="space-y-4">

                    @auth
                    @if ($this->canReview)
                    <div
                        class="rounded-2xl border border-orange-200 dark:border-orange-500/30 bg-orange-50/50 dark:bg-orange-500/5 p-5">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Leave a review</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">
                            You completed this service — share your experience.
                        </p>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-gray-300">Your
                                    rating</label>
                                <div class="flex items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++) <button type="button"
                                        wire:click="$set('reviewRating', {{ $i }})" class="transition hover:scale-110">
                                        <svg class="h-8 w-8 {{ $i <= $reviewRating ? 'text-yellow-400' : 'text-slate-300 dark:text-gray-600' }}"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        </button>
                                        @endfor
                                </div>
                            </div>

                            <div>
                                <textarea wire:model="reviewText" rows="3"
                                    placeholder="How was the service? Quality, speed, staff?"
                                    class="w-full rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100"></textarea>
                            </div>

                            @if ($reviewMessage)
                            <p class="text-sm font-medium text-emerald-600">{{ $reviewMessage }}</p>
                            @endif

                            <button type="button" wire:click="submitReview" wire:loading.attr="disabled"
                                wire:target="submitReview"
                                class="inline-flex items-center justify-center rounded-xl bg-[#FF5E14] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#D94E10] disabled:opacity-60">
                                <span wire:loading.remove wire:target="submitReview">Post review</span>
                                <span wire:loading wire:target="submitReview">Posting…</span>
                            </button>
                        </div>
                    </div>
                    @elseif ($this->hasReviewedService)
                    <div
                        class="rounded-2xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 p-4 flex items-center gap-2">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none"
                            stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                            Thanks for your review!
                        </p>
                    </div>
                    @endif
                    @else
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4 text-center">
                        <p class="text-sm text-slate-500 dark:text-gray-400">
                            <a href="{{ route('login') }}" class="font-semibold text-[#FF5E14] hover:underline">Sign
                                in</a>
                            to leave a review.
                        </p>
                    </div>
                    @endauth

                    @if ($reviews->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($reviews as $review)
                        <div wire:key="svc-review-{{ $review->id }}"
                            class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#FF5E14] text-xs font-bold text-white">
                                    {{ strtoupper(substr($review->customer?->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ $review->customer?->name ?? 'Anonymous' }}
                                            </p>
                                            <div class="mt-0.5 flex items-center gap-0.5">
                                                @for ($i = 1; $i <= 5; $i++) <svg
                                                    class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-slate-300 dark:text-gray-600' }}"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    @endfor
                                            </div>
                                        </div>
                                        <span class="shrink-0 text-[10px] text-slate-400">{{
                                            $review->created_at?->diffForHumans() }}</span>
                                    </div>

                                    @if ($review->review)
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-gray-400">
                                        {{ $review->review }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </section>
        @endif
    </div>
</div>