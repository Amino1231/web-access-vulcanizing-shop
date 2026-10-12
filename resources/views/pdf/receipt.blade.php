<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>
    <style>
        @page { margin: 50px 55px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #0f172a; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .right { text-align: right; }
        .slate { color: #64748b; }
        .light { color: #94a3b8; }
        .label { font-size: 9px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #64748b; margin-bottom: 6px; }
        .divider { border-top: 1px solid #e2e8f0; margin: 22px 0; }
        .items th { font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #64748b; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .items td { padding: 14px 0; border-bottom: 1px solid #f1f5f9; }
        .pill { display: inline-block; padding: 3px 12px; border-radius: 10px; font-size: 9px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .paid { background: #d1fae5; color: #047857; }
        .unpaid { background: #fef3c7; color: #b45309; }
    </style>
</head>
<body>
@php
    $money  = fn ($v) => '₱' . number_format((float) $v, 2);
    $isPaid = $order->payment_status === 'paid';
@endphp

    {{-- Header --}}
    <table>
        <tr>
            <td style="width: 50%;">
                <div style="font-size: 24px; font-weight: bold;">Receipt</div>
                <div class="slate" style="font-size: 13px; margin-top: 4px;">{{ $order->order_number }}</div>
                <div class="light" style="font-size: 10px; margin-top: 2px;">
                    Issued {{ $order->created_at->format('F d, Y g:i A') }}
                </div>
            </td>
            <td class="right" style="width: 50%;">
                <div style="font-size: 13px; font-weight: bold;">{{ $order->tenant?->name }}</div>
                @if ($order->tenant?->address)<div class="slate">{{ $order->tenant->address }}</div>@endif
                @if ($order->tenant?->phone)<div class="slate">{{ $order->tenant->phone }}</div>@endif
                @if ($order->tenant?->email)<div class="slate">{{ $order->tenant->email }}</div>@endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Billed to / Payment --}}
    <table>
        <tr>
            <td style="width: 50%;">
                <div class="label">Billed to</div>
                <div style="font-size: 13px; font-weight: bold;">{{ $order->customer?->name ?? 'Guest' }}</div>
                @if ($order->customer?->email)<div class="slate">{{ $order->customer->email }}</div>@endif
                @if ($order->customer?->phone)<div class="slate">{{ $order->customer->phone }}</div>@endif
            </td>
            <td class="right" style="width: 50%;">
                <div class="label">Payment</div>
                <div style="font-size: 13px; font-weight: bold;">
                    {{ ucwords(str_replace('_', ' ', (string) $order->payment_method)) }}
                </div>
                <div style="margin-top: 4px;">
                    <span class="pill {{ $isPaid ? 'paid' : 'unpaid' }}">{{ $order->payment_status }}</span>
                </div>
                @if ($order->paid_at)
                    <div class="light" style="font-size: 10px; margin-top: 4px;">
                        Paid {{ $order->paid_at->format('M d, Y g:i A') }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Items --}}
    <table class="items">
        <thead>
            <tr>
                <th style="text-align: left;">Item</th>
                <th class="right" style="width: 8%;">Qty</th>
                <th class="right" style="width: 20%;">Unit price</th>
                <th class="right" style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        <span style="font-size: 12px; font-weight: bold;">{{ $item->product_name }}</span>
                        @if ($item->variant_label)<br><span class="slate" style="font-size: 10px;">{{ $item->variant_label }}</span>@endif
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ $money($item->unit_price) }}</td>
                    <td class="right" style="font-weight: bold;">{{ $money($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table style="margin-top: 18px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="slate" style="padding: 4px 0;">Subtotal</td>
                        <td class="right" style="padding: 4px 0; font-weight: bold;">{{ $money($order->subtotal) }}</td>
                    </tr>
                    @if ($order->discount > 0)
                        <tr>
                            <td class="slate" style="padding: 4px 0;">Discount</td>
                            <td class="right" style="padding: 4px 0; font-weight: bold;">- {{ $money($order->discount) }}</td>
                        </tr>
                    @endif
                    @if ($order->tax > 0)
                        <tr>
                            <td class="slate" style="padding: 4px 0;">Tax</td>
                            <td class="right" style="padding: 4px 0; font-weight: bold;">{{ $money($order->tax) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 13px; font-weight: bold;">Total</td>
                        <td class="right" style="padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 16px; font-weight: bold; color: #FF5E14;">
                            {{ $money($order->total) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($order->notes)
        <div class="divider"></div>
        <div class="label">Notes</div>
        <div>{{ $order->notes }}</div>
    @endif

    {{-- Footer --}}
    <div class="divider" style="margin-top: 36px;"></div>
    <div style="text-align: center;">
        <div style="font-size: 12px; font-weight: bold;">Thank you for your order!</div>
        <div class="slate" style="font-size: 10px; margin-top: 3px;">
            This receipt was generated by {{ config('app.name') }}.
        </div>
    </div>
</body>
</html>