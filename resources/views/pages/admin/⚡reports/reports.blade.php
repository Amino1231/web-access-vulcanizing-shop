@php
$toneClasses = [
    'orange' => 'border-orange-200 bg-orange-50 dark:border-orange-900/40 dark:bg-orange-950/30',
    'green'  => 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/40 dark:bg-emerald-950/30',
    'amber'  => 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/30',
    'red'    => 'border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/30',
];
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Analytics</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Platform reports</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Revenue, top performers, and category breakdown across every shop.
            </p>
        </div>

        @php $period = $this->period; @endphp
        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-right">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">{{ $period[2] }}</p>
            <p class="text-xs font-semibold text-slate-700 dark:text-gray-300">
                {{ $period[0]->format('M d, Y') }} → {{ $period[1]->format('M d, Y') }}
            </p>
        </div>
    </div>

    {{-- Range selector --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                @php
                    $ranges = [
                        'today' => 'Today',
                        'week'  => 'This week',
                        'month' => 'This month',
                        'year'  => 'This year',
                    ];
                @endphp
                @foreach ($ranges as $key => $label)
                    <button type="button" wire:click="setRange('{{ $key }}')"
                        class="inline-flex items-center rounded-xl border px-3.5 py-2 text-sm font-semibold transition
                            {{ $range === $key
                                ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10 dark:border-orange-500/40 dark:text-orange-400'
                                : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <input wire:model.live="from" type="date"
                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20 dark:[color-scheme:dark]" />
                <span class="text-xs text-slate-400 dark:text-gray-500">to</span>
                <input wire:model.live="to" type="date"
                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20 dark:[color-scheme:dark]" />
            </div>
        </div>
    </div>

    {{-- KPI grid --}}
    @php $kpis = $this->kpis; @endphp
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($kpis as $kpi)
            <article class="rounded-2xl border {{ $toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-5 shadow-sm">
                <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-600 dark:text-gray-400">
                    {{ $kpi['label'] }}
                </p>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $kpi['value'] }}</h2>
            </article>
        @endforeach
    </section>

    {{-- Revenue trend chart --}}
    <section class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Trend</p>
                <h3 class="text-xl font-semibold text-slate-900 dark:text-white">GMV — last 12 months</h3>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-gray-400">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#FF5E14]"></span>
                Monthly revenue
            </div>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="adminRevenueChart" class="h-full w-full"></canvas>
        </div>
    </section>

    {{-- Top shops + Top products --}}
    <div class="grid gap-6 xl:grid-cols-2">

        {{-- Top shops --}}
        <section class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Ranking</p>
                <h3 class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">Top shops by revenue</h3>
            </div>

            @php $topShops = $this->topShops; @endphp
            @if (empty($topShops))
                <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                    <p class="text-sm text-slate-500 dark:text-gray-400">No sales in this period.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($topShops as $i => $shop)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xs font-bold text-white dark:bg-gray-100 dark:text-gray-900">
                                {{ $i + 1 }}
                            </div>
                            @if ($shop['logo'])
                                <img src="{{ $shop['logo'] }}" alt=""
                                     class="h-9 w-9 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-gray-700" />
                            @else
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-gray-700">
                                    {{ strtoupper(substr($shop['name'], 0, 2)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $shop['name'] }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-gray-400">
                                    {{ $shop['owner'] ?? '—' }} · {{ $shop['orders'] }} {{ \Illuminate\Support\Str::plural('order', $shop['orders']) }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                ₱{{ number_format($shop['revenue'], 0) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Top products --}}
        <section class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Performance</p>
                <h3 class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">Top products across platform</h3>
            </div>

            @php $topProducts = $this->topProducts; @endphp
            @if (empty($topProducts))
                <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                    <p class="text-sm text-slate-500 dark:text-gray-400">No products sold in this period.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($topProducts as $i => $p)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#FF5E14] text-xs font-bold text-white">
                                {{ $i + 1 }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $p['name'] }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-gray-400">
                                    {{ $p['shop'] ?? '—' }} · {{ $p['qty'] }} sold
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                ₱{{ number_format($p['revenue'], 0) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- Category breakdown + New signups --}}
    <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">

        {{-- Category breakdown --}}
        <section class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Segments</p>
                <h3 class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">Revenue by category</h3>
            </div>

            @php $categories = $this->categoryBreakdown; @endphp
            @if (empty($categories))
                <div class="rounded-2xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                    <p class="text-sm text-slate-500 dark:text-gray-400">No category sales in this period.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($categories as $cat)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $cat['label'] }}</span>
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="text-slate-500 dark:text-gray-400">{{ $cat['pct'] }}%</span>
                                    <span class="font-bold text-slate-900 dark:text-white">₱{{ number_format($cat['revenue'], 0) }}</span>
                                </div>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-gray-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-orange-400 to-orange-600"
                                     style="width: {{ $cat['pct'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- New signups --}}
        <section class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Growth</p>
                <h3 class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">New signups</h3>
            </div>

            @php $signups = $this->newSignups; @endphp
            <div class="space-y-3">
                <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-gray-400">Shop owners</span>
                        <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $signups['owners'] }}</span>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-gray-400">Customers</span>
                        <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $signups['customers'] }}</span>
                    </div>
                </div>
                <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-900/40 dark:bg-orange-950/30">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-orange-700 dark:text-orange-400">Total</span>
                        <span class="text-2xl font-bold text-orange-700 dark:text-orange-400">{{ $signups['total'] }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@script
<script>
    // ─────────────────────────────────────────
    // Revenue trend chart
    // ─────────────────────────────────────────
    const revenueCanvas = document.getElementById('adminRevenueChart');
    if (revenueCanvas) {
        const labels = @json($this->revenueTrend['labels']);
        const values = @json($this->revenueTrend['series']).map(Number);
        const isDark = document.documentElement.classList.contains('dark');

        const drawRevenue = () => {
            const ctx = revenueCanvas.getContext('2d');
            const bounds = revenueCanvas.getBoundingClientRect();
            const width  = Math.max(Math.floor(bounds.width), 1);
            const height = Math.max(Math.floor(bounds.height), 1);
            const dpr    = window.devicePixelRatio || 1;

            revenueCanvas.width  = width  * dpr;
            revenueCanvas.height = height * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, width, height);

            const pad = { top: 20, right: 20, bottom: 36, left: 64 };
            const pw  = width  - pad.left - pad.right;
            const ph  = height - pad.top  - pad.bottom;

            const max = Math.max(...values, 1);
            const niceMax = Math.ceil(max / 1000) * 1000 || 1000;

            const xAt = (i) => pad.left + (pw / Math.max(labels.length - 1, 1)) * i;
            const yAt = (v) => pad.top + ph - (v / niceMax) * ph;

            // Grid + Y axis
            ctx.strokeStyle = isDark ? '#1f2937' : '#e2e8f0';
            ctx.lineWidth = 1;
            ctx.font = '11px sans-serif';
            ctx.textBaseline = 'middle';

            for (let i = 0; i <= 4; i++) {
                const y = pad.top + (ph / 4) * i;
                const val = niceMax - (niceMax / 4) * i;

                ctx.beginPath();
                ctx.moveTo(pad.left, y);
                ctx.lineTo(width - pad.right, y);
                ctx.stroke();

                ctx.textAlign = 'right';
                ctx.fillStyle = isDark ? '#9ca3af' : '#64748b';
                ctx.fillText('₱' + Math.round(val).toLocaleString(), pad.left - 10, y);
            }

            // Area
            const gradient = ctx.createLinearGradient(0, pad.top, 0, pad.top + ph);
            gradient.addColorStop(0, 'rgba(255, 94, 20, 0.35)');
            gradient.addColorStop(1, 'rgba(255, 94, 20, 0.01)');

            ctx.beginPath();
            ctx.moveTo(xAt(0), yAt(values[0]));
            values.slice(1).forEach((v, i) => ctx.lineTo(xAt(i + 1), yAt(v)));
            ctx.lineTo(xAt(values.length - 1), pad.top + ph);
            ctx.lineTo(xAt(0), pad.top + ph);
            ctx.closePath();
            ctx.fillStyle = gradient;
            ctx.fill();

            // Line
            ctx.beginPath();
            ctx.moveTo(xAt(0), yAt(values[0]));
            values.slice(1).forEach((v, i) => ctx.lineTo(xAt(i + 1), yAt(v)));
            ctx.strokeStyle = '#FF5E14';
            ctx.lineWidth = 3;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            ctx.stroke();

            // Dots
            values.forEach((v, i) => {
                ctx.beginPath();
                ctx.arc(xAt(i), yAt(v), 4, 0, Math.PI * 2);
                ctx.fillStyle = isDark ? '#111827' : '#fff';
                ctx.fill();
                ctx.strokeStyle = '#FF5E14';
                ctx.lineWidth = 2;
                ctx.stroke();
            });

            // X labels
            ctx.textAlign = 'center';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = isDark ? '#9ca3af' : '#64748b';
            labels.forEach((label, i) => {
                ctx.fillText(label, xAt(i), height - 10);
            });
        };

        drawRevenue();
        new ResizeObserver(drawRevenue).observe(revenueCanvas);
    }
</script>
@endscript