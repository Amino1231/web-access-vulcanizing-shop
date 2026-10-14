<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            padding: 24px;
        }
        h1 { font-size: 20px; margin-bottom: 2px; color: #0f172a; }
        h2 { font-size: 13px; margin: 18px 0 8px; color: #0f172a; }
        .muted { color: #64748b; }
        .header { border-bottom: 2px solid #FF5E14; padding-bottom: 12px; margin-bottom: 16px; }
        .header .shop { color: #FF5E14; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .meta { margin-top: 6px; font-size: 10px; color: #475569; }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 8px; }
        .kpi-table td {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            width: 25%;
            vertical-align: top;
        }
        .kpi-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px; }
        .kpi-value { font-size: 14px; font-weight: bold; color: #0f172a; }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data th {
            background: #f1f5f9;
            text-align: left;
            padding: 6px 8px;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
        }
        table.data td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.data tr:nth-child(even) td { background: #fafafa; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .money { color: #059669; font-weight: bold; }
        .empty { padding: 14px; text-align: center; color: #94a3b8; background: #f8fafc; border: 1px dashed #cbd5e1; }
        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="shop">{{ $tenant->name }}</div>
        <h1>Sales Report</h1>
        <div class="meta">
            <strong>{{ $label }}</strong>
            &nbsp;·&nbsp;
            {{ $start->format('M d, Y') }} → {{ $end->format('M d, Y') }}
            &nbsp;·&nbsp;
            Generated {{ now()->format('M d, Y g:i A') }}
        </div>
    </div>

    {{-- KPIs --}}
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">Revenue</div>
                <div class="kpi-value">₱{{ number_format($kpis['revenue'], 2) }}</div>
            </td>
            <td>
                <div class="kpi-label">Sales Count</div>
                <div class="kpi-value">{{ $kpis['sales'] }}</div>
            </td>
            <td>
                <div class="kpi-label">Avg Order Value</div>
                <div class="kpi-value">₱{{ number_format($kpis['avg'], 2) }}</div>
            </td>
            <td>
                <div class="kpi-label">Items Sold</div>
                <div class="kpi-value">{{ number_format($kpis['items']) }}</div>
            </td>
        </tr>
    </table>

    {{-- Top products --}}
    <h2>Top Products</h2>
    @if (empty($topProducts))
        <div class="empty">No sales in this period.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Product</th>
                    <th style="width: 90px;">SKU</th>
                    <th class="right" style="width: 70px;">Qty</th>
                    <th class="right" style="width: 110px;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topProducts as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="bold">{{ $p['name'] }}</td>
                        <td class="muted">{{ $p['sku'] ?? '—' }}</td>
                        <td class="right">{{ $p['qty'] }}</td>
                        <td class="right money">₱{{ number_format($p['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Top customers --}}
    <h2>Top Customers</h2>
    @if (empty($topCustomers))
        <div class="empty">No customer activity in this period.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th class="right" style="width: 70px;">Orders</th>
                    <th class="right" style="width: 110px;">Spent</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topCustomers as $i => $c)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="bold">{{ $c['name'] }}</td>
                        <td class="muted">{{ $c['email'] ?? '—' }}</td>
                        <td class="right">{{ $c['orders'] }}</td>
                        <td class="right money">₱{{ number_format($c['spent'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Sales by employee --}}
    @if (! empty($salesByEmployee))
        <h2>Sales by Employee</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th class="right" style="width: 80px;">Commission</th>
                    <th class="right" style="width: 70px;">Orders</th>
                    <th class="right" style="width: 110px;">Revenue</th>
                    <th class="right" style="width: 110px;">Payout</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salesByEmployee as $e)
                    <tr>
                        <td class="bold">{{ $e['name'] }}</td>
                        <td class="right">{{ number_format($e['commission_rate'], 2) }}%</td>
                        <td class="right">{{ $e['orders'] }}</td>
                        <td class="right bold">₱{{ number_format($e['revenue'], 2) }}</td>
                        <td class="right money">₱{{ number_format($e['commission'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Full sales table --}}
    <h2>All Sales</h2>
    @if ($sales->isEmpty())
        <div class="empty">No sales in this period.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 100px;">Invoice</th>
                    <th>Customer</th>
                    <th style="width: 140px;">Items</th>
                    <th style="width: 70px;">Payment</th>
                    <th class="right" style="width: 100px;">Total</th>
                    <th class="right" style="width: 100px;">When</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    <tr>
                        <td>
                            <div class="bold">{{ $sale->invoice_number }}</div>
                            @if ($sale->server)
                                <div class="muted" style="font-size: 8px;">by {{ $sale->server->name }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($sale->customer)
                                <div class="bold">{{ $sale->customer->name }}</div>
                                <div class="muted" style="font-size: 8px;">{{ $sale->customer->email }}</div>
                            @else
                                <span class="muted">Walk-in</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $sale->items->count() }} {{ \Illuminate\Support\Str::plural('line', $sale->items->count()) }}</div>
                            <div class="muted" style="font-size: 8px;">
                                {{ $sale->items->take(2)->map(fn ($i) => $i->product?->name ?? $i->service?->name ?? 'Item')->filter()->implode(', ') }}
                                @if ($sale->items->count() > 2)
                                    +{{ $sale->items->count() - 2 }}
                                @endif
                            </div>
                        </td>
                        <td>{{ ucwords(str_replace('_', ' ', $sale->payment_method)) }}</td>
                        <td class="right bold">₱{{ number_format($sale->final_amount, 2) }}</td>
                        <td class="right muted">{{ $sale->created_at->format('M d, g:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        {{ $tenant->name }} · Sales report generated on {{ now()->format('F j, Y \a\t g:i A') }}
    </div>

</body>
</html>