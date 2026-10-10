<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-gray-400">Analytics</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Sales reports</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                Revenue, top products, and customer insights for the selected period.
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

    {{-- Range tabs --}}
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
                                ? 'border-[#FF5E14] bg-orange-50 text-[#FF5E14] dark:bg-orange-500/10 dark:border-orange-500/40'
                                : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <input wire:model.live="from" type="date"
                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
                <span class="text-xs text-slate-400">to</span>
                <input wire:model.live="to" type="date"
                    class="rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-500/20" />
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    @php $kpis = $this->kpis; @endphp
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Revenue</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                ₱{{ number_format($kpis['revenue'], 2) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Sales count</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/10">
                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $kpis['sales'] }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Avg order value</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-500/10">
                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                ₱{{ number_format($kpis['avg'], 2) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Items sold</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ number_format($kpis['items']) }}</p>
        </div>
    </div>

    {{-- Top products + top customers --}}
    <div class="grid gap-6 xl:grid-cols-2">

        {{-- Top products --}}
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Performance</p>
                    <h2 class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">Top products</h2>
                </div>
                <span class="text-xs text-slate-500 dark:text-gray-400">By revenue</span>
            </div>

            @php $topProducts = $this->topProducts; @endphp
            @if (empty($topProducts))
                <div class="rounded-xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                    <p class="text-sm text-slate-500 dark:text-gray-400">No sales in this period.</p>
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
                                    {{ $p['qty'] }} sold @if($p['sku']) · {{ $p['sku'] }} @endif
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                ₱{{ number_format($p['revenue'], 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Top customers --}}
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Loyalty</p>
                    <h2 class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">Top customers</h2>
                </div>
                <span class="text-xs text-slate-500 dark:text-gray-400">By spending</span>
            </div>

            @php $topCustomers = $this->topCustomers; @endphp
            @if (empty($topCustomers))
                <div class="rounded-xl border border-dashed border-slate-300 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/40 p-6 text-center">
                    <p class="text-sm text-slate-500 dark:text-gray-400">No customer activity in this period.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($topCustomers as $i => $c)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-gray-800 bg-slate-50 dark:bg-gray-800/40 p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-gray-700">
                                {{ strtoupper(substr($c['name'], 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $c['name'] }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-gray-400">
                                    {{ $c['orders'] }} {{ \Illuminate\Support\Str::plural('order', $c['orders']) }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                ₱{{ number_format($c['spent'], 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Sales by employee --}}
    @php $byEmployee = $this->salesByEmployee; @endphp
    @if (! empty($byEmployee))
        <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="mb-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Team</p>
                <h2 class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">Sales by employee</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-slate-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Employee</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Commission</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Orders</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Revenue</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Payout</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-gray-800">
                        @foreach ($byEmployee as $e)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $e['name'] }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-gray-400">{{ number_format($e['commission_rate'], 2) }}%</td>
                                <td class="px-4 py-3 text-right text-slate-700 dark:text-gray-300">{{ $e['orders'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">₱{{ number_format($e['revenue'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400">₱{{ number_format($e['commission'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Sales table --}}
    <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="border-b border-slate-200 dark:border-gray-800 px-5 py-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-gray-400">Transaction log</p>
            <h2 class="mt-0.5 text-lg font-bold text-slate-900 dark:text-white">All sales</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-800 text-sm">
                <thead class="bg-slate-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Invoice</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Customer</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Items</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Payment</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Total</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @php $sales = $this->sales; @endphp
                    @forelse ($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}" class="transition hover:bg-slate-50 dark:hover:bg-gray-800/40">

                            <td class="whitespace-nowrap px-4 py-3">
                                <p class="font-mono text-xs text-slate-600 dark:text-gray-300">{{ $sale->invoice_number }}</p>
                                @if ($sale->server)
                                    <p class="text-[10px] text-slate-400 dark:text-gray-500">by {{ $sale->server->name }}</p>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if ($sale->customer)
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $sale->customer->name }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-gray-400">{{ $sale->customer->email }}</p>
                                @else
                                    <span class="text-xs text-slate-400">Walk-in</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <p class="text-sm text-slate-700 dark:text-gray-300">
                                    {{ $sale->items->count() }}
                                    {{ \Illuminate\Support\Str::plural('line', $sale->items->count()) }}
                                </p>
                                <p class="truncate text-xs text-slate-500 dark:text-gray-400">
                                    {{ $sale->items->take(2)->map(fn ($i) => $i->product?->name ?? $i->service?->name ?? 'Item')->filter()->implode(', ') }}
                                    @if ($sale->items->count() > 2)
                                        +{{ $sale->items->count() - 2 }}
                                    @endif
                                </p>
                            </td>

                            <td class="px-4 py-3 text-sm capitalize text-slate-700 dark:text-gray-300">
                                {{ str_replace('_', ' ', $sale->payment_method) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                                ₱{{ number_format($sale->final_amount, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs text-slate-500 dark:text-gray-400">
                                {{ $sale->created_at->format('M d, g:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <p class="mt-3 text-sm font-medium text-slate-700 dark:text-gray-300">No sales in this period</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">Try a different date range.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->total() > 0)
            <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }} of {{ $sales->total() }}
                </p>
                <div>{{ $sales->links() }}</div>
            </div>
        @endif
    </div>
</div>