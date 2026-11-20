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
        h2 { font-size: 13px; margin: 18px 0 8px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
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

        /* Table Styles */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed; /* Forces columns to respect widths */
        }
        table.data th {
            background: #f1f5f9;
            text-align: left;
            padding: 8px;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
        }
        table.data td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            word-wrap: break-word; /* Prevents long emails from stretching the table */
        }
        table.data tr:nth-child(even) td { background: #fafafa; }
        
        /* FIXED ALIGNMENT CLASSES */
        .right { text-align: right; }
        table.data th.right, 
        table.data td.right { 
            text-align: right !important; 
            padding-left: 15px; /* Adds a gap so numbers don't touch the previous column */
        }

        .bold { font-weight: bold; }
        .money { color: #059669; font-weight: bold; }
        .empty { padding: 14px; text-align: center; color: #94a3b8; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="shop">{{ $tenant->name }}</div>
        <h1>Executive Sales Report</h1>
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
                <div class="kpi-label">Gross Revenue</div>
                <div class="kpi-value">₱{{ number_format($kpis['revenue'], 2) }}</div>
            </td>
            <td>
                <div class="kpi-label">Net Payout</div>
                <div class="kpi-value" style="color: #059669;">₱{{ number_format($kpis['net'], 2) }}</div>
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
    <h2>Top Selling Products</h2>
    @if (empty($topProducts))
        <div class="empty">No product sales in this period.</div>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Product Name</th>
                    <th style="width: 150px;">SKU</th>
                    <th class="right" style="width: 80px;">Qty Sold</th>
                    <th class="right" style="width: 120px;">Revenue</th>
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
                    <th>Customer Name</th>
                    <th style="width: 180px;">Email</th>
                    <th class="right" style="width: 70px;">Orders</th>
                    <th class="right" style="width: 120px;">Total Spent</th>
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
        <h2>Employee Performance</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th class="right" style="width: 90px;">Comm. Rate</th>
                    <th class="right" style="width: 80px;">Orders</th>
                    <th class="right" style="width: 120px;">Revenue</th>
                    <th class="right" style="width: 120px;">Est. Payout</th>
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

    {{-- Summary block --}}
    <div class="summary-box">
        <h2 style="margin-top: 0; border: none; padding-bottom: 0;">Report Summary</h2>
        <p class="muted" style="margin-top: 5px;">
            This report summarizes the performance of <strong>{{ $tenant->name }}</strong> for the selected period.
            A total of <strong>{{ $kpis['sales'] }} transactions</strong> were completed, generating 
            <strong>₱{{ number_format($kpis['revenue'], 2) }}</strong> in gross revenue and 
            <strong>₱{{ number_format($kpis['net'], 2) }}</strong> in net payouts. 
            Detailed transaction logs are available in the web dashboard.
        </p>
    </div>

    <div class="footer">
        {{ $tenant->name }} · Confidential Executive Sales Report · Generated on {{ now()->format('F j, Y \a\t g:i A') }}
    </div>

</body>
</html>