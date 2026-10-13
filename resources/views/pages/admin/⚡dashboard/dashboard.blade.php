@php
$toneClasses = [
    'orange'  => 'border-orange-200 bg-orange-50 dark:border-orange-900/40 dark:bg-orange-950/30',
    'green'   => 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/40 dark:bg-emerald-950/30',
    'emerald' => 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/40 dark:bg-emerald-950/30',
    'blue'    => 'border-blue-200 bg-blue-50 dark:border-blue-900/40 dark:bg-blue-950/30',
    'amber'   => 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/30',
    'red'     => 'border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/30',
];
@endphp

<div class="min-h-screen text-slate-900 dark:text-slate-100">
    <div class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- HEADER --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                    Platform overview
                </p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">
                    Admin dashboard
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ now()->format('l, F j, Y') }}
            </p>
        </header>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- REVENUE FLOW — the headline numbers --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="mb-6">
            <div class="mb-3 flex items-baseline justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Revenue flow
                    </p>
                    <h2 class="mt-0.5 text-base font-semibold">
                        Customer → Shop → Platform
                    </h2>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                @foreach($moneyKpis as $kpi)
                @php
                    $isEarnings = $kpi['label'] === 'Platform earnings';
                    $isPayouts  = $kpi['label'] === 'Shop payouts';
                    $isHeadline = $isEarnings;
                @endphp
                <article class="rounded-2xl border p-5 shadow-sm {{ $isHeadline
                    ? 'border-emerald-300 dark:border-emerald-500/40 bg-gradient-to-br from-emerald-50 to-emerald-100/50 dark:from-emerald-500/10 dark:to-emerald-500/5'
                    : ($toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900') }}">

                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-[0.16em] {{ $isHeadline ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
                            {{ $kpi['label'] }}
                        </span>
                        <strong class="text-xs font-semibold {{ $kpi['delta'] === '—'
                            ? 'text-slate-400 dark:text-slate-500'
                            : (str_starts_with($kpi['delta'], '+')
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : (str_starts_with($kpi['delta'], '-')
                                    ? 'text-red-600 dark:text-red-400'
                                    : 'text-slate-500 dark:text-slate-400')) }}">
                            {{ $kpi['delta'] }}
                        </strong>
                    </div>

                    <div class="text-3xl font-bold tracking-tight {{ $isHeadline ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-900 dark:text-slate-100' }}">
                        {{ $kpi['value'] }}
                    </div>

                    @if ($isEarnings)
                        <p class="mt-1.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                            Admin's commission cut
                        </p>
                    @elseif ($isPayouts)
                        <p class="mt-1.5 text-[11px] font-medium text-blue-600 dark:text-blue-400">
                            Paid out to shop owners
                        </p>
                    @endif
                </article>
                @endforeach
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SUPPLY + DEMAND KPIs (compact 2-column layout) --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="mb-6 grid gap-4 lg:grid-cols-2">

            {{-- Supply side --}}
            <div>
                <div class="mb-3 flex items-baseline justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Shops
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Supply</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($kpis as $kpi)
                    <article class="rounded-2xl border p-4 shadow-sm {{ $toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' }}">
                        <div class="mb-2.5 flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $kpi['label'] }}
                            </span>
                            <strong class="text-[11px] font-semibold {{ $kpi['delta'] === '—'
                                ? 'text-slate-400 dark:text-slate-500'
                                : (str_starts_with($kpi['delta'], '+')
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : (str_starts_with($kpi['delta'], '-')
                                        ? 'text-red-600 dark:text-red-400'
                                        : 'text-slate-500 dark:text-slate-400')) }}">
                                {{ $kpi['delta'] }}
                            </strong>
                        </div>
                        <div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            {{ $kpi['value'] }}
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>

            {{-- Demand side --}}
            <div>
                <div class="mb-3 flex items-baseline justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Appointments
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Demand</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($appointmentKpis as $kpi)
                    <article class="rounded-2xl border p-4 shadow-sm {{ $toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' }}">
                        <div class="mb-2.5 flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $kpi['label'] }}
                            </span>
                            <strong class="text-[11px] font-semibold {{ $kpi['delta'] === '—'
                                ? 'text-slate-400 dark:text-slate-500'
                                : (str_starts_with($kpi['delta'], '+')
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : (str_starts_with($kpi['delta'], '-')
                                        ? 'text-red-600 dark:text-red-400'
                                        : 'text-slate-500 dark:text-slate-400')) }}">
                                {{ $kpi['delta'] }}
                            </strong>
                        </div>
                        <div class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                            {{ $kpi['value'] }}
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- CHARTS — activity + verification mix --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="mb-6 grid gap-4 xl:grid-cols-[1.6fr_1fr]">

            {{-- Activity chart --}}
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            Growth
                        </p>
                        <h3 class="mt-0.5 text-base font-semibold">Platform activity</h3>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] font-medium text-slate-600 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span> Shops
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Appointments
                        </span>
                    </div>
                </div>
                <div class="relative h-56 w-full">
                    <canvas id="activityLineChart" class="h-full w-full" role="img"
                        aria-label="Shops vs appointments over the last seven months"></canvas>
                </div>
            </article>

            {{-- Verification mix --}}
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Snapshot
                    </p>
                    <h3 class="mt-0.5 text-base font-semibold">Verification mix</h3>
                </div>

                @if($donutTotal > 0)
                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                        <canvas id="tenantDonutChart" width="200" height="200" class="block shrink-0"></canvas>
                        <div class="w-full space-y-2.5 sm:max-w-[220px]">
                            @foreach($donutSeries as $item)
                                <div class="grid grid-cols-[10px_1fr_auto] items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                    <span class="inline-block h-2.5 w-2.5 rounded-full {{ $item['swatch'] }}"></span>
                                    <span>{{ $item['label'] }}</span>
                                    <strong class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/40">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            No tenants have submitted applications yet.
                        </p>
                    </div>
                @endif
            </article>
        </section>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- LEADERBOARD --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        Leaderboard
                    </p>
                    <h3 class="mt-0.5 text-base font-semibold">Top shops — last 30 days</h3>
                </div>
            </div>

            @if (count($topShops) === 0)
                <div class="p-5">
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center dark:border-slate-700 dark:bg-slate-800/40">
                        <p class="text-xs text-slate-500 dark:text-slate-400">No activity recorded yet.</p>
                    </div>
                </div>
            @else

                {{-- Mobile: stacked cards --}}
                <div class="space-y-2 p-4 md:hidden">
                    @foreach($topShops as $i => $shop)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 dark:border-slate-700 dark:bg-slate-800/40">
                            <div class="flex items-start gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[11px] font-bold text-white dark:bg-slate-100 dark:text-slate-900">
                                    {{ $i + 1 }}
                                </div>

                                @if($shop['logo'])
                                    <img src="{{ $shop['logo'] }}" alt=""
                                        class="h-9 w-9 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-slate-700" />
                                @else
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-slate-100 dark:text-slate-900">
                                        {{ strtoupper(substr($shop['name'], 0, 2)) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $shop['name'] }}</p>
                                    <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $shop['owner'] ?? '—' }}</p>
                                </div>

                                @if($shop['rating'] !== null)
                                    <span class="shrink-0 rounded-full bg-yellow-50 px-2 py-0.5 text-[10px] font-bold text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        ★ {{ $shop['rating'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 grid grid-cols-4 gap-2 border-t border-slate-200 pt-3 dark:border-slate-700">
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Appts</p>
                                    <p class="mt-0.5 text-sm font-bold">{{ $shop['appointments'] }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Orders</p>
                                    <p class="mt-0.5 text-sm font-bold">{{ $shop['orders'] }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Revenue</p>
                                    <p class="mt-0.5 text-sm font-bold">₱{{ number_format($shop['revenue'], 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Comm.</p>
                                    <p class="mt-0.5 text-sm font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($shop['commission'] ?? 0, 0) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop: table --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="w-10 px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Shop</th>
                                <th class="px-4 py-3 text-right">Appointments</th>
                                <th class="px-4 py-3 text-right">Orders</th>
                                <th class="px-4 py-3 text-right">Revenue</th>
                                <th class="px-4 py-3 text-right">Commission</th>
                                <th class="px-4 py-3 text-right">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($topShops as $i => $shop)
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 text-xs font-bold text-slate-400 dark:text-slate-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            @if($shop['logo'])
                                                <img src="{{ $shop['logo'] }}" alt=""
                                                    class="h-8 w-8 shrink-0 rounded-lg border border-slate-200 object-cover dark:border-slate-700" />
                                            @else
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white dark:bg-slate-100 dark:text-slate-900">
                                                    {{ strtoupper(substr($shop['name'], 0, 2)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold">{{ $shop['name'] }}</p>
                                                <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $shop['owner'] ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-bold">{{ $shop['appointments'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-bold">{{ $shop['orders'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-slate-700 dark:text-slate-300">₱{{ number_format($shop['revenue'], 0) }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                        ₱{{ number_format($shop['commission'] ?? 0, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-slate-700 dark:text-slate-300">
                                        {{ $shop['rating'] !== null ? '★ ' . $shop['rating'] : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>

@script
<script>
    // ─────────────────────────────────────────
    // Dual-line chart — shops vs appointments
    // ─────────────────────────────────────────
    const activityCanvas = document.getElementById('activityLineChart');
    if (activityCanvas) {
        const labels      = @json($activityLabels);
        const tenantData  = @json($activityTenants).map(Number);
        const appointData = @json($activityAppointments).map(Number);

        const drawDualLine = () => {
            const ctx    = activityCanvas.getContext('2d');
            const bounds = activityCanvas.getBoundingClientRect();
            const width  = Math.max(Math.floor(bounds.width), 1);
            const height = Math.max(Math.floor(bounds.height), 1);
            const dpr    = window.devicePixelRatio || 1;

            activityCanvas.width  = width  * dpr;
            activityCanvas.height = height * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, width, height);

            const isDark     = document.documentElement.classList.contains('dark');
            const gridColor  = isDark ? '#334155' : '#e2e8f0';
            const labelColor = isDark ? '#94a3b8' : '#64748b';

            const pad = { top: 16, right: 36, bottom: 32, left: 32 };
            const pw  = width  - pad.left - pad.right;
            const ph  = height - pad.top  - pad.bottom;

            const niceMax = (arr) => {
                const m = Math.max(...arr, 1);
                const step = Math.pow(10, Math.floor(Math.log10(m)));
                return Math.ceil(m / step) * step;
            };

            const tenantsMax = niceMax(tenantData);
            const appointMax = niceMax(appointData);

            const xAt = (i) => pad.left + (pw / Math.max(labels.length - 1, 1)) * i;
            const yAt = (v, max) => pad.top + ph - (v / max) * ph;

            ctx.font = '11px sans-serif';
            ctx.textBaseline = 'middle';
            for (let i = 0; i <= 4; i++) {
                const y = pad.top + (ph / 4) * i;

                ctx.beginPath();
                ctx.moveTo(pad.left, y);
                ctx.lineTo(width - pad.right, y);
                ctx.strokeStyle = gridColor;
                ctx.lineWidth = 1;
                ctx.stroke();

                ctx.textAlign = 'right';
                ctx.fillStyle = '#f97316';
                ctx.fillText(String(Math.round(tenantsMax - (tenantsMax / 4) * i)), pad.left - 8, y);

                ctx.textAlign = 'left';
                ctx.fillStyle = '#10b981';
                ctx.fillText(String(Math.round(appointMax - (appointMax / 4) * i)), width - pad.right + 8, y);
            }

            const drawSeries = (values, max, color, rgb) => {
                if (!values.length) return;

                const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + ph);
                grad.addColorStop(0, `rgba(${rgb}, 0.20)`);
                grad.addColorStop(1, `rgba(${rgb}, 0.01)`);

                ctx.beginPath();
                ctx.moveTo(xAt(0), yAt(values[0], max));
                values.slice(1).forEach((v, i) => ctx.lineTo(xAt(i + 1), yAt(v, max)));
                ctx.lineTo(xAt(values.length - 1), pad.top + ph);
                ctx.lineTo(xAt(0), pad.top + ph);
                ctx.closePath();
                ctx.fillStyle = grad;
                ctx.fill();

                ctx.beginPath();
                ctx.moveTo(xAt(0), yAt(values[0], max));
                values.slice(1).forEach((v, i) => ctx.lineTo(xAt(i + 1), yAt(v, max)));
                ctx.strokeStyle = color;
                ctx.lineWidth = 3;
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';
                ctx.stroke();

                values.forEach((v, i) => {
                    ctx.beginPath();
                    ctx.arc(xAt(i), yAt(v, max), 3.5, 0, Math.PI * 2);
                    ctx.fillStyle = isDark ? '#0f172a' : '#fff';
                    ctx.fill();
                    ctx.strokeStyle = color;
                    ctx.lineWidth = 2;
                    ctx.stroke();
                });
            };

            drawSeries(tenantData,  tenantsMax, '#f97316', '249,115,22');
            drawSeries(appointData, appointMax, '#10b981', '16,185,129');

            ctx.textAlign = 'center';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = labelColor;
            labels.forEach((label, i) => ctx.fillText(label, xAt(i), height - 8));
        };

        drawDualLine();
        new ResizeObserver(drawDualLine).observe(activityCanvas);
    }

    // ─────────────────────────────────────────
    // Donut chart — verification mix
    // ─────────────────────────────────────────
    const donutCanvas = document.getElementById('tenantDonutChart');
    if (donutCanvas) {
        const ctx = donutCanvas.getContext('2d');
        const data = @json($donutSeries);
        const total = {{ $donutTotal }};

        if (total > 0) {
            const isDark = document.documentElement.classList.contains('dark');
            const centerX = donutCanvas.width / 2;
            const centerY = donutCanvas.height / 2;
            const radius = 70;
            let currentAngle = -Math.PI / 2;

            data.forEach((item) => {
                const value = Number(item.value || 0);
                if (value === 0) return;

                const slice = (value / total) * Math.PI * 2;
                const endAngle = currentAngle + slice;

                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, currentAngle, endAngle);
                ctx.closePath();
                ctx.fillStyle = item.color;
                ctx.fill();

                currentAngle = endAngle;
            });

            ctx.beginPath();
            ctx.arc(centerX, centerY, 42, 0, Math.PI * 2);
            ctx.fillStyle = isDark ? '#0f172a' : '#ffffff';
            ctx.fill();

            ctx.fillStyle = isDark ? '#f1f5f9' : '#0f172a';
            ctx.textAlign = 'center';
            ctx.font = '700 22px sans-serif';
            ctx.fillText(String(total), centerX, centerY - 4);

            ctx.font = '11px sans-serif';
            ctx.fillStyle = isDark ? '#94a3b8' : '#64748b';
            ctx.fillText(total === 1 ? 'tenant' : 'tenants', centerX, centerY + 14);
        }
    }
</script>
@endscript