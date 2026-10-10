@php
$toneClasses = [
'orange' => 'border-orange-200 bg-orange-50',
'green' => 'border-emerald-200 bg-emerald-50',
'amber' => 'border-amber-200 bg-amber-50',
'red' => 'border-red-200 bg-red-50',
];
@endphp

<div class="min-h-screen text-slate-900 lg:rounded-lg">
    <div class="mx-auto px-2 py-2 sm:px-2 lg:px-6">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Platform overview</p>
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Admin Dashboard</h1>
            </div>
        </div>

        {{-- Shop KPIs --}}
        <section class="mb-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($kpis as $kpi)
            <article
                class="rounded-2xl border {{ $toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white' }} p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between text-xs font-medium text-slate-600">
                    <span>{{ $kpi['label'] }}</span>
                    <strong @class([ 'font-semibold' , 'text-slate-400'=> $kpi['delta'] === '—',
                        'text-emerald-600' => str_starts_with($kpi['delta'], '+'),
                        'text-red-600' => str_starts_with($kpi['delta'], '-'),
                        'text-slate-600' => !in_array($kpi['delta'][0] ?? '', ['+', '-', '—'], true),
                        ])>{{ $kpi['delta'] }}</strong>
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $kpi['value'] }}</h2>
            </article>
            @endforeach
        </section>

        {{-- Money KPIs --}}
        <section class="mb-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($moneyKpis as $kpi)
            <article
                class="rounded-2xl border {{ $toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white' }} p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between text-xs font-medium text-slate-600">
                    <span>{{ $kpi['label'] }}</span>
                    <strong @class([ 'font-semibold' , 'text-slate-400'=> $kpi['delta'] === '—',
                        'text-emerald-600' => str_starts_with($kpi['delta'], '+'),
                        'text-red-600' => str_starts_with($kpi['delta'], '-'),
                        ])>{{ $kpi['delta'] }}</strong>
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $kpi['value'] }}</h2>
            </article>
            @endforeach
        </section>

        {{-- Demand KPIs --}}
        <section class="mb-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($appointmentKpis as $kpi)
            <article
                class="rounded-2xl border {{ $toneClasses[$kpi['tone']] ?? 'border-slate-200 bg-white' }} p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between text-xs font-medium text-slate-600">
                    <span>{{ $kpi['label'] }}</span>
                    <strong @class([ 'font-semibold' , 'text-slate-400'=> $kpi['delta'] === '—',
                        'text-emerald-600' => str_starts_with($kpi['delta'], '+'),
                        'text-red-600' => str_starts_with($kpi['delta'], '-'),
                        ])>{{ $kpi['delta'] }}</strong>
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $kpi['value'] }}</h2>
            </article>
            @endforeach
        </section>

        {{-- Growth + Verification mix --}}
        <section class="mb-8 grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Growth</p>
                        <h3 class="text-xl font-semibold text-slate-900">Platform activity</h3>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium text-slate-600">
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

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5">
                    <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Snapshot</p>
                    <h3 class="text-xl font-semibold text-slate-900">Verification mix</h3>
                </div>

                @if($donutTotal > 0)
                <div
                    class="flex flex-col items-center justify-center gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <canvas id="tenantDonutChart" width="220" height="220" class="block"></canvas>
                    <div class="w-full max-w-[220px] space-y-3">
                        @foreach($donutSeries as $item)
                        <div class="grid grid-cols-[10px_1fr_auto] items-center gap-2 text-sm text-slate-600">
                            <span class="inline-block h-2.5 w-2.5 rounded-full {{ $item['swatch'] }}"></span>
                            <span>{{ $item['label'] }}</span>
                            <strong class="font-semibold text-slate-800">{{ $item['value'] }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm text-slate-500">No tenants have submitted applications yet.</p>
                </div>
                @endif
            </article>
        </section>

        {{-- Leaderboard --}}
        <section class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Leaderboard</p>
                <h3 class="text-xl font-semibold text-slate-900">Top shops — last 30 days</h3>
            </div>

            @if (count($topShops) === 0)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="text-sm text-slate-500">No activity recorded yet.</p>
            </div>
            @else

            {{-- Mobile: stacked cards (< md) --}} <div class="space-y-3 md:hidden">
                @foreach($topShops as $i => $shop)
                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xs font-bold text-white">
                            {{ $i + 1 }}
                        </div>

                        @if($shop['logo'])
                        <img src="{{ $shop['logo'] }}" alt=""
                            class="h-10 w-10 shrink-0 rounded-lg border border-slate-200 object-cover" />
                        @else
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white">
                            {{ strtoupper(substr($shop['name'], 0, 2)) }}
                        </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $shop['name'] }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $shop['owner'] ?? '—' }}</p>
                        </div>

                        @if($shop['rating'] !== null)
                        <span
                            class="shrink-0 rounded-full bg-yellow-50 px-2 py-0.5 text-[10px] font-bold text-yellow-700">
                            ★ {{ $shop['rating'] }}
                        </span>
                        @endif
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2 border-t border-slate-200 pt-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Appts</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-900">{{ $shop['appointments'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Orders</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-900">{{ $shop['orders'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Revenue</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-900">₱{{ number_format($shop['revenue'], 0) }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
    </div>

    {{-- Tablet + Desktop: table (md+) --}}
    <div class="hidden overflow-x-auto md:block">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="w-8 pb-2 text-left"></th>
                    <th class="pb-2 text-left">Shop</th>
                    <th class="pb-2 text-right">Appointments</th>
                    <th class="pb-2 text-right">Orders</th>
                    <th class="pb-2 text-right">Revenue</th>
                    <th class="pb-2 text-right">Rating</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($topShops as $i => $shop)
                <tr>
                    <td class="py-3 text-sm font-bold text-slate-400">{{ $i + 1 }}</td>
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            @if($shop['logo'])
                            <img src="{{ $shop['logo'] }}" alt=""
                                class="h-9 w-9 shrink-0 rounded-lg border border-slate-200 object-cover" />
                            @else
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-[10px] font-bold text-white">
                                {{ strtoupper(substr($shop['name'], 0, 2)) }}
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900">{{ $shop['name'] }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $shop['owner'] ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 text-right font-bold text-slate-900">{{ $shop['appointments'] }}</td>
                    <td class="py-3 text-right font-bold text-slate-900">{{ $shop['orders'] }}</td>
                    <td class="py-3 text-right text-slate-700">₱{{ number_format($shop['revenue'], 0) }}</td>
                    <td class="py-3 text-right text-slate-700">
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
                ctx.strokeStyle = '#e2e8f0';
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
                    ctx.fillStyle = '#fff';
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
            ctx.fillStyle = '#64748b';
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
            ctx.fillStyle = '#ffffff';
            ctx.fill();

            ctx.fillStyle = '#0f172a';
            ctx.textAlign = 'center';
            ctx.font = '700 24px sans-serif';
            ctx.fillText(String(total), centerX, centerY - 4);

            ctx.font = '12px sans-serif';
            ctx.fillStyle = '#64748b';
            ctx.fillText(total === 1 ? 'tenant' : 'tenants', centerX, centerY + 16);
        }
    }
</script>
@endscript