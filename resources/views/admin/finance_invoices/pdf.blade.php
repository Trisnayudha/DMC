<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .title { font-size: 22px; font-weight: bold; color: #1a202c; letter-spacing: 1px; }
        .muted { color: #777; }
        .items th { background: #f3f4f8; text-align: left; padding: 7px; border-bottom: 1px solid #ddd; }
        .items td { padding: 7px; border-bottom: 1px solid #eee; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals td { padding: 5px 7px; }
        .grand td { font-size: 13px; font-weight: bold; border-top: 2px solid #333; }
        .pay-box { margin-top: 18px; border: 1px solid #c7d2fe; background: #eef2ff; padding: 12px; }
        .va { font-size: 18px; font-weight: bold; letter-spacing: 2px; color: #1e3a8a; }
        .paid-stamp { color: #15803d; font-size: 16px; font-weight: bold; border: 2px solid #15803d; padding: 4px 10px; display: inline-block; }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <img src="{{ public_path('image/dmc.png') }}" alt="Djakarta Mining Club" style="width:90px;">
                <div style="margin-top:6px;"><strong>Djakarta Mining Club</strong></div>
            </td>
            <td class="right">
                <div class="title">INVOICE</div>
                <div>No: <strong>{{ $invoice->invoice_number }}</strong></div>
                <div>Date: {{ $invoice->issued_date->format('d M Y') }}</div>
                <div>Due Date: {{ $invoice->due_date->format('d M Y') }}</div>
                @if ($invoice->status === 'paid')
                    <div style="margin-top:6px;"><span class="paid-stamp">PAID</span></div>
                @endif
            </td>
        </tr>
    </table>

    <div style="margin:18px 0 12px;">
        <div class="muted">BILL TO</div>
        @if ($invoice->bill_to_company)<strong>{{ $invoice->bill_to_company }}</strong><br>@endif
        Attn: {{ $invoice->bill_to_name }}<br>
        {{ $invoice->bill_to_email }}@if ($invoice->bill_to_phone) · {{ $invoice->bill_to_phone }}@endif
        @if ($invoice->bill_to_address)<br>{!! nl2br(e($invoice->bill_to_address)) !!}@endif
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="center" style="width:50px;">Qty</th>
                <th class="right" style="width:120px;">Unit Price (IDR)</th>
                <th class="right" style="width:120px;">Amount (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="center">{{ $item['qty'] }}</td>
                    <td class="right">{{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item['amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals" style="width:45%; margin-left:55%; margin-top:6px;">
        <tr><td>Subtotal</td><td class="right">IDR {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
        @if ($invoice->vat_amount > 0)
            <tr><td>VAT {{ rtrim(rtrim(number_format($invoice->vat_percent, 2, '.', ''), '0'), '.') }}%</td><td class="right">IDR {{ number_format($invoice->vat_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="right">IDR {{ number_format($invoice->total_amount, 0, ',', '.') }}</td></tr>
    </table>

    @if ($invoice->notes)
        <div style="margin-top:12px;"><span class="muted">Notes:</span> {!! nl2br(e($invoice->notes)) !!}</div>
    @endif

    @if ($invoice->status !== 'paid' && $invoice->va_number)
        <div class="pay-box">
            <div><strong>Payment via Mandiri Virtual Account</strong></div>
            <div class="va" style="margin:6px 0;">{{ $invoice->va_number }}</div>
            <div>Account name: {{ $invoice->bill_to_company ?: $invoice->bill_to_name }}</div>
            <div>Amount: <strong>IDR {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong> (must be paid in the exact amount)</div>
            <div>Valid until: {{ $invoice->va_expires_at ? $invoice->va_expires_at->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB' : $invoice->due_date->format('d M Y') }}</div>
            <div class="muted" style="margin-top:6px;">Pay through Livin' by Mandiri, Mandiri ATM, or Mandiri internet banking using the virtual account number above.
                Payment is confirmed automatically.</div>
        </div>
    @endif
</body>

</html>
