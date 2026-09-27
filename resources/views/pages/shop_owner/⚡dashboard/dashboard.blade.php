<div class="space-y-6 dark:bg-gray-900">

    {{-- Header --}}
    <div>
        <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Owner portal</p>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">Your shop at a glance.</p>
    </div>

    {{-- KPIs --}}
    @php $kpis = $this->kpis; @endphp
    @if (! empty($kpis))
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                @php
                    $toneClasses = match ($kpi['tone']) {
                        'emerald' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        'orange'  => 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300',
                        'blue'    => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300',
                        'red'     => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300',
                        default   => 'bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-gray-300',
                    };
                @endphp
                <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">{{ $kpi['label'] }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $toneClasses }}">{{ $kpi['delta'] }}</span>
                    </div>
                    <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ $kpi['value'] }}</div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Charts --}}
    @php
        $revenueChart = $this->revenueChart;
        $donut = $this->orderDonut;
    @endphp
    <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Revenue</h2>
                <span class="text-xs text-slate-500 dark:text-gray-400">Last 6 months</span>
            </div>
            <canvas id="revenueChart" class="h-56 w-full"></canvas>
        </article>

        <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Order status</h2>
            </div>
            <div class="flex flex-col items-center gap-4">
                <canvas id="orderDonut" width="180" height="180"></canvas>
                <div class="w-full space-y-2">
                    @foreach ($donut as $segment)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full ring-1 ring-inset ring-black/5 dark:ring-white/10" style="background: {{ $segment['color'] }}"></span>
                                <span class="text-slate-600 dark:text-gray-400">{{ $segment['label'] }}</span>
                            </div>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $segment['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>
    </div>

    {{-- Recent orders + reviews --}}
    <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Recent orders</h2>
                <a href="{{ route('owner.order_management') }}" class="text-xs font-semibold text-[#FF5E14] hover:underline dark:text-orange-400">View all</a>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-gray-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-slate-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Order</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Invoice</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse ($this->recentOrders as $order)
                            @php $badge = $order->status->badge(); @endphp
                            <tr wire:key="recent-order-{{ $order->id }}" class="hover:bg-slate-50 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-gray-300">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-slate-900 dark:text-white">{{ $order->customer?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-gray-400">
                                    {{ $order->sale?->invoice_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">₱{{ number_format($order->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-gray-400">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm dark:shadow-none">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Recent reviews</h2>
                <div class="flex items-center gap-1">
                    <svg class="h-4 w-4 text-yellow-400 dark:text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
            </div>

            <div class="space-y-4">
                <p class="text-sm text-slate-500 dark:text-gray-400">No reviews yet.</p>
            </div>
        </article>
    </div>
</div>

@script
<script>
    const revenueLabels = @json($revenueChart['labels'] ?? []);
    const revenueSeries = @json($revenueChart['series'] ?? []);
    const donutData = @json($donut ?? []);

    function drawRevenueChart() {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const w = canvas.clientWidth;
        const h = 224;

        canvas.width = w * dpr;
        canvas.height = h * dpr;
        ctx.scale(dpr, dpr);

        const padding = 30;
        const max = Math.max(...revenueSeries, 1);
        const isDark = document.documentElement.classList.contains('dark');

        ctx.clearRect(0, 0, w, h);

        // Grid
        ctx.strokeStyle = isDark ? '#1f2937' : '#e2e8f0';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = padding + ((h - padding * 2) / 4) * i;
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(w - padding, y);
            ctx.stroke();
        }

        if (!revenueSeries.length) return;

        const points = revenueSeries.map((value, index) => {
            const x = padding + ((w - padding * 2) / Math.max(revenueSeries.length - 1, 1)) * index;
            const y = h - padding - (value / max) * (h - padding * 2);
            return { x, y };
        });

        // Gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, h);
        gradient.addColorStop(0, 'rgba(255, 94, 20, 0.35)');
        gradient.addColorStop(1, 'rgba(255, 94, 20, 0.02)');

        // Line
        ctx.beginPath();
        ctx.moveTo(points[0].x, points[0].y);
        points.slice(1).forEach((p) => ctx.lineTo(p.x, p.y));
        ctx.lineWidth = 3;
        ctx.strokeStyle = '#FF5E14';
        ctx.stroke();

        // Fill
        ctx.lineTo(points[points.length - 1].x, h - padding);
        ctx.lineTo(points[0].x, h - padding);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();

        // Dots
        points.forEach((p) => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
            ctx.fillStyle = '#FF5E14';
            ctx.fill();
            ctx.strokeStyle = isDark ? '#111827' : '#fff';
            ctx.lineWidth = 2;
            ctx.stroke();
        });

        // Labels
        revenueLabels.forEach((label, index) => {
            const x = padding + ((w - padding * 2) / Math.max(revenueLabels.length - 1, 1)) * index;
            ctx.fillStyle = isDark ? '#9ca3af' : '#64748b';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(label, x, h - 8);
        });
    }

    function drawDonut() {
        const canvas = document.getElementById('orderDonut');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const total = donutData.reduce((s, i) => s + Number(i.value || 0), 0) || 1;
        const cx = canvas.width / 2;
        const cy = canvas.height / 2;
        const radius = 70;
        const innerRadius = 42;
        let angle = -Math.PI / 2;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        donutData.forEach((item) => {
            const slice = (Number(item.value || 0) / total) * Math.PI * 2;
            ctx.beginPath();
            ctx.arc(cx, cy, radius, angle, angle + slice);
            ctx.arc(cx, cy, innerRadius, angle + slice, angle, true);
            ctx.closePath();
            ctx.fillStyle = item.color;
            ctx.fill();
            angle += slice;
        });

        // Center text
        const isDark = document.documentElement.classList.contains('dark');
        ctx.fillStyle = isDark ? '#ffffff' : '#0f172a';
        ctx.textAlign = 'center';
        ctx.font = '700 20px sans-serif';
        ctx.fillText(String(total), cx, cy + 4);
        ctx.fillStyle = isDark ? '#9ca3af' : '#64748b';
        ctx.font = '500 10px sans-serif';
        ctx.fillText('ORDERS', cx, cy + 20);
    }

    function renderAll() {
        drawRevenueChart();
        drawDonut();
    }

    renderAll();

    const resizeObserver = new ResizeObserver(() => renderAll());
    const revenueCanvas = document.getElementById('revenueChart');
    if (revenueCanvas) resizeObserver.observe(revenueCanvas);

    const themeObserver = new MutationObserver(renderAll);
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
</script>
@endscript