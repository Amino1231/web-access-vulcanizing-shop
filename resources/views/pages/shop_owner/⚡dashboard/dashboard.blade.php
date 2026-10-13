<div class="min-h-screen bg-slate-50 dark:bg-gray-950">

    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SHOP STATUS BANNER --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        @php $shopStatus = $this->shopStatus; @endphp

        @if ($shopStatus && in_array($shopStatus['status'], ['needs_more_info', 'rejected', 'pending', 'under_review']))
            @php
                $bannerTone = match ($shopStatus['status']) {
                    'needs_more_info' => [
                        'wrap'  => 'border-purple-200 dark:border-purple-500/30 bg-purple-50 dark:bg-purple-500/10',
                        'icon'  => 'bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400',
                        'title' => 'text-purple-900 dark:text-purple-200',
                        'body'  => 'text-purple-800 dark:text-purple-300',
                    ],
                    'rejected' => [
                        'wrap'  => 'border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10',
                        'icon'  => 'bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400',
                        'title' => 'text-red-900 dark:text-red-200',
                        'body'  => 'text-red-800 dark:text-red-300',
                    ],
                    'under_review' => [
                        'wrap'  => 'border-blue-200 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10',
                        'icon'  => 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400',
                        'title' => 'text-blue-900 dark:text-blue-200',
                        'body'  => 'text-blue-800 dark:text-blue-300',
                    ],
                    default => [
                        'wrap'  => 'border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10',
                        'icon'  => 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400',
                        'title' => 'text-amber-900 dark:text-amber-200',
                        'body'  => 'text-amber-800 dark:text-amber-300',
                    ],
                };

                $bannerTitle = match ($shopStatus['status']) {
                    'needs_more_info' => 'Action required — more info requested',
                    'rejected'        => 'Your shop application was rejected',
                    'under_review'    => 'Your shop is under review',
                    default           => 'Your shop is pending review',
                };

                $bannerMessage = match ($shopStatus['status']) {
                    'needs_more_info' => 'An admin reviewed your shop and needs more information before approving it.',
                    'rejected'        => 'An admin reviewed your shop and could not approve it in its current state.',
                    'under_review'    => 'An admin is currently reviewing your shop. You will be notified once a decision is made.',
                    default           => 'Your shop has been submitted and is waiting to be reviewed.',
                };
            @endphp

            <div class="rounded-2xl border {{ $bannerTone['wrap'] }} p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $bannerTone['icon'] }}">
                        @if ($shopStatus['status'] === 'needs_more_info' || $shopStatus['status'] === 'rejected')
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        @elseif ($shopStatus['status'] === 'under_review')
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-bold {{ $bannerTone['title'] }}">
                            {{ $bannerTitle }}
                        </h3>
                        <p class="mt-1 text-sm {{ $bannerTone['body'] }}">
                            {{ $bannerMessage }}
                        </p>

                        @if ($shopStatus['status'] === 'needs_more_info' && ! empty($shopStatus['missing_requirements']))
                            <div class="mt-3 rounded-xl border border-purple-200/70 dark:border-purple-500/20 bg-white/60 dark:bg-gray-900/40 p-3">
                                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-wider {{ $bannerTone['title'] }}">
                                    What to provide
                                </p>
                                <ul class="list-disc space-y-1 pl-5 text-sm {{ $bannerTone['body'] }}">
                                    @foreach ($shopStatus['missing_requirements'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($shopStatus['status'] === 'rejected' && $shopStatus['rejection_reason'])
                            <div class="mt-3 rounded-xl border border-red-200/70 dark:border-red-500/20 bg-white/60 dark:bg-gray-900/40 p-3">
                                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-wider {{ $bannerTone['title'] }}">
                                    Reason
                                </p>
                                <p class="text-sm {{ $bannerTone['body'] }}">
                                    {{ $shopStatus['rejection_reason'] }}
                                </p>
                            </div>
                        @endif

                        @if (in_array($shopStatus['status'], ['needs_more_info', 'rejected']))
                            <div class="mt-4">
                                <a href="{{ route('owner.business_setup') }}"
                                   class="inline-flex items-center gap-2 rounded-xl bg-[#FF5E14] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-500/30 transition hover:bg-[#D94E10]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Update shop & resubmit
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- HEADER --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                    Owner portal
                </p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                    Dashboard
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-gray-400">
                {{ now()->format('l, F j, Y') }}
            </p>
        </header>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- KPIs — Revenue flow --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        @php $kpis = $this->kpis; @endphp

        @if (! empty($kpis))
            <section>
                <div class="mb-3 flex items-baseline justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                            This month
                        </p>
                        <h2 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">
                            Revenue flow
                        </h2>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($kpis as $kpi)
                        @php
                            $isNetRevenue = $kpi['label'] === 'Net Revenue (mo.)';

                            $toneClasses = match ($kpi['tone']) {
                                'emerald' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                'orange'  => 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300',
                                'blue'    => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300',
                                'red'     => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300',
                                default   => 'bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-gray-300',
                            };
                        @endphp

                        <article class="rounded-2xl border p-5 shadow-sm {{ $isNetRevenue
                            ? 'border-emerald-300 dark:border-emerald-500/40 bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-500/10 dark:to-emerald-500/5'
                            : 'border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900' }}">

                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-[0.18em] {{ $isNetRevenue
                                    ? 'text-emerald-700 dark:text-emerald-400'
                                    : 'text-slate-500 dark:text-gray-400' }}">
                                    {{ $kpi['label'] }}
                                </span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $toneClasses }}">
                                    {{ $kpi['delta'] }}
                                </span>
                            </div>

                            <div class="text-3xl font-bold tracking-tight {{ $isNetRevenue
                                ? 'text-emerald-700 dark:text-emerald-300'
                                : 'text-slate-900 dark:text-white' }}">
                                {{ $kpi['value'] }}
                            </div>

                            @if ($isNetRevenue)
                                <p class="mt-1.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                                    Your take-home after platform fee
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>

                {{-- Revenue flow explanation --}}
                <div class="mt-3 rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3">
                    <div class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 16v-4M12 8h.01" />
                        </svg>
                        <p class="text-[11px] leading-relaxed text-slate-600 dark:text-gray-400">
                            Customers pay the shop, then a small platform fee is deducted.
                            Your <strong class="text-emerald-600 dark:text-emerald-400">Net Revenue</strong>
                            is what you actually receive after the platform's cut.
                        </p>
                    </div>
                </div>
            </section>
        @endif

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- CHARTS --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        @php
            $revenueChart = $this->revenueChart;
            $donut = $this->orderDonut;
        @endphp

        <section class="grid gap-4 xl:grid-cols-[1.6fr_1fr]">

            {{-- Revenue chart --}}
            <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                            Trend
                        </p>
                        <h2 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">
                            Revenue
                        </h2>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-gray-400">Last 6 months</span>
                </div>

                <div class="relative h-56 w-full">
                    <canvas id="revenueChart" class="h-full w-full"></canvas>
                </div>
            </article>

            {{-- Order status donut --}}
            <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <div class="mb-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                        Snapshot
                    </p>
                    <h2 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">
                        Order status
                    </h2>
                </div>

                <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                    <canvas id="orderDonut" width="180" height="180" class="shrink-0"></canvas>

                    <div class="w-full space-y-2 sm:max-w-[220px]">
                        @foreach ($donut as $segment)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full ring-1 ring-inset ring-black/5 dark:ring-white/10"
                                          style="background: {{ $segment['color'] }}"></span>
                                    <span class="text-slate-600 dark:text-gray-400">{{ $segment['label'] }}</span>
                                </div>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $segment['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </section>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- RECENT ORDERS + REVIEWS --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="grid gap-4 xl:grid-cols-[1.6fr_1fr]">

            {{-- Recent orders --}}
            <article class="min-w-0 rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                            Activity
                        </p>
                        <h2 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">
                            Recent orders
                        </h2>
                    </div>
                    <a href="{{ route('owner.order_management') }}"
                       class="text-xs font-semibold text-[#FF5E14] hover:underline dark:text-orange-400">
                        View all →
                    </a>
                </div>

                {{-- Mobile: stacked cards --}}
                <div class="space-y-2 sm:hidden">
                    @forelse ($this->recentOrders as $order)
                        @php $badge = $order->status->badge(); @endphp
                        <div wire:key="mobile-order-{{ $order->id }}"
                             class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-mono text-xs text-slate-600 dark:text-gray-300">
                                        {{ $order->order_number }}
                                    </p>
                                    <p class="mt-0.5 truncate text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $order->customer?->name ?? '—' }}
                                    </p>
                                </div>
                                <span class="shrink-0 text-sm font-bold text-slate-900 dark:text-white">
                                    ₱{{ number_format($order->total, 2) }}
                                </span>
                            </div>

                            <div class="mt-2 flex items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $order->status->label() }}
                                </span>
                                @if ($order->sale?->invoice_number)
                                    <span class="truncate font-mono text-[10px] text-slate-500 dark:text-gray-400">
                                        {{ $order->sale->invoice_number }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                            <p class="text-sm text-slate-500 dark:text-gray-400">No orders yet.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Desktop: table --}}
                <div class="hidden overflow-hidden rounded-xl border border-slate-200 dark:border-gray-800 sm:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                            <thead class="bg-slate-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Order</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Status</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Invoice</th>
                                    <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                @forelse ($this->recentOrders as $order)
                                    @php $badge = $order->status->badge(); @endphp
                                    <tr wire:key="desktop-order-{{ $order->id }}"
                                        class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600 dark:text-gray-300">
                                            {{ $order->order_number }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">
                                            {{ $order->customer?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                                {{ $order->status->label() }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500 dark:text-gray-400">
                                            {{ $order->sale?->invoice_number ?? '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">
                                            ₱{{ number_format($order->total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-gray-400">
                                            No orders yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            {{-- Recent reviews --}}
            <article class="min-w-0 rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                @php $summary = $this->reviewSummary; @endphp

                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">
                            Feedback
                        </p>
                        <h2 class="mt-0.5 text-base font-semibold text-slate-900 dark:text-white">
                            Recent reviews
                        </h2>
                    </div>

                    @if ($summary['count'] > 0)
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ number_format($summary['average'], 1) }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-gray-400">
                                ({{ $summary['count'] }})
                            </span>
                        </div>
                    @endif
                </div>

                @if ($this->recentReviews->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                        <p class="text-sm text-slate-500 dark:text-gray-400">No reviews yet.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($this->recentReviews as $review)
                            @php
                                $kind = $review->kind();
                                $kindClasses = match ($kind) {
                                    'Product' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300',
                                    'Service' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
                                    'Sale'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                    'Staff'   => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300',
                                    default   => 'bg-slate-100 text-slate-600 dark:bg-gray-800 dark:text-gray-300',
                                };
                            @endphp

                            <div wire:key="review-{{ $review->id }}"
                                 class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ $review->customer?->name ?? 'Anonymous' }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-0.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-slate-300 dark:text-gray-600' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-[10px] text-slate-400 dark:text-gray-500">
                                        {{ $review->created_at?->diffForHumans() }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $kindClasses }}">
                                        {{ $kind }}
                                    </span>
                                    <span class="min-w-0 truncate text-xs font-medium text-slate-600 dark:text-gray-400">
                                        {{ $review->subjectLabel() }}
                                    </span>
                                </div>

                                @if ($review->review)
                                    <p class="mt-2 line-clamp-2 text-xs text-slate-600 dark:text-gray-400">
                                        {{ $review->review }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        </section>

    </div>
</div>

@script
<script>
    (function () {
        const revenueLabels = @json($revenueChart['labels'] ?? []);
        const revenueSeries = @json($revenueChart['series'] ?? []);
        const donutData     = @json($donut ?? []);

        let revenueCanvas = document.getElementById('revenueChart');
        let donutCanvas   = document.getElementById('orderDonut');

        const getThemeColors = () => {
            const isDark = document.documentElement.classList.contains('dark');

            return {
                isDark,
                grid:      isDark ? '#1f2937' : '#e2e8f0',
                text:      isDark ? '#9ca3af' : '#64748b',
                centerBold: isDark ? '#ffffff' : '#0f172a',
                dotStroke: isDark ? '#111827' : '#ffffff',
            };
        };

        function drawRevenueChart() {
            if (! revenueCanvas) return;

            const ctx = revenueCanvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;
            const w   = revenueCanvas.clientWidth;
            const h   = revenueCanvas.clientHeight || 224;

            if (w === 0 || h === 0) return;

            revenueCanvas.width  = w * dpr;
            revenueCanvas.height = h * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, w, h);

            const theme = getThemeColors();
            const padding = { top: 16, right: 20, bottom: 28, left: 40 };
            const plotW = w - padding.left - padding.right;
            const plotH = h - padding.top - padding.bottom;
            const max = Math.max(...revenueSeries, 1);

            ctx.strokeStyle = theme.grid;
            ctx.lineWidth = 1;
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = theme.text;

            for (let i = 0; i <= 4; i++) {
                const y   = padding.top + (plotH / 4) * i;
                const val = Math.round(max - (max / 4) * i);

                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(w - padding.right, y);
                ctx.stroke();

                ctx.fillText(String(val), padding.left - 8, y);
            }

            if (! revenueSeries.length) return;

            const points = revenueSeries.map((value, index) => ({
                x: padding.left + (plotW / Math.max(revenueSeries.length - 1, 1)) * index,
                y: padding.top + plotH - (value / max) * plotH,
            }));

            const gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + plotH);
            gradient.addColorStop(0, 'rgba(255, 94, 20, 0.30)');
            gradient.addColorStop(1, 'rgba(255, 94, 20, 0.01)');

            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            points.slice(1).forEach((p) => ctx.lineTo(p.x, p.y));
            ctx.lineTo(points[points.length - 1].x, padding.top + plotH);
            ctx.lineTo(points[0].x, padding.top + plotH);
            ctx.closePath();
            ctx.fillStyle = gradient;
            ctx.fill();

            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            points.slice(1).forEach((p) => ctx.lineTo(p.x, p.y));
            ctx.lineWidth = 3;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#FF5E14';
            ctx.stroke();

            points.forEach((p) => {
                ctx.beginPath();
                ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = '#FF5E14';
                ctx.fill();
                ctx.strokeStyle = theme.dotStroke;
                ctx.lineWidth = 2;
                ctx.stroke();
            });

            ctx.textAlign = 'center';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = theme.text;
            ctx.font = '11px sans-serif';

            revenueLabels.forEach((label, index) => {
                const x = padding.left + (plotW / Math.max(revenueLabels.length - 1, 1)) * index;
                ctx.fillText(label, x, h - 8);
            });
        }

        function drawDonut() {
            if (! donutCanvas) return;

            const ctx = donutCanvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;

            const size = 180;
            donutCanvas.width  = size * dpr;
            donutCanvas.height = size * dpr;
            donutCanvas.style.width  = size + 'px';
            donutCanvas.style.height = size + 'px';

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, size, size);

            const total = donutData.reduce((s, i) => s + Number(i.value || 0), 0);

            const cx = size / 2;
            const cy = size / 2;
            const radius = 70;
            const innerRadius = 42;

            if (total > 0) {
                let angle = -Math.PI / 2;

                donutData.forEach((item) => {
                    const value = Number(item.value || 0);
                    if (value === 0) return;

                    const slice = (value / total) * Math.PI * 2;

                    ctx.beginPath();
                    ctx.arc(cx, cy, radius, angle, angle + slice);
                    ctx.arc(cx, cy, innerRadius, angle + slice, angle, true);
                    ctx.closePath();
                    ctx.fillStyle = item.color;
                    ctx.fill();

                    angle += slice;
                });
            } else {
                ctx.beginPath();
                ctx.arc(cx, cy, radius, 0, Math.PI * 2);
                ctx.arc(cx, cy, innerRadius, 0, Math.PI * 2, true);
                ctx.fillStyle = '#e2e8f0';
                ctx.fill();
            }

            const theme = getThemeColors();
            ctx.fillStyle = theme.centerBold;
            ctx.textAlign = 'center';
            ctx.font = '700 22px sans-serif';
            ctx.fillText(String(total), cx, cy + 4);

            ctx.fillStyle = theme.text;
            ctx.font = '500 10px sans-serif';
            ctx.fillText('ORDERS', cx, cy + 22);
        }

        function renderAll() {
            drawRevenueChart();
            drawDonut();
        }

        renderAll();

        let resizeTimer = null;
        const resizeObserver = new ResizeObserver(() => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(renderAll, 60);
        });

        if (revenueCanvas) {
            resizeObserver.observe(revenueCanvas);
        }

        const themeObserver = new MutationObserver(() => {
            renderAll();
        });
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        document.addEventListener('livewire:navigated', renderAll, { once: true });
    })();
</script>
@endscript