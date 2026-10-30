<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Received</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f7; margin: 0; padding: 24px; color: #333; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #1f7a4d; padding: 22px 28px; }
        .header .eyebrow { color: #cdeedd; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; margin: 0 0 4px; }
        .header h2 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 24px 28px; font-size: 13.5px; line-height: 1.55; }
        .intro { margin: 0 0 16px; }
        .alert-box { background: #e9f6ef; border-left: 4px solid #1f7a4d; padding: 12px 16px; border-radius: 4px; margin-bottom: 8px; font-size: 13px; }
        h3 { font-size: 13px; margin: 24px 0 10px; color: #1f7a4d; border-bottom: 1px solid #eee; padding-bottom: 6px; text-transform: uppercase; letter-spacing: .5px; }
        table.info { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.info td { padding: 5px 0; vertical-align: top; }
        table.info td.label { width: 190px; color: #888; }
        table.info tr.total td { border-top: 1px solid #eee; padding-top: 8px; font-weight: bold; }
        .meta { font-size: 12px; color: #888; margin-top: 22px; }
        .footer { background: #f8f9fa; padding: 14px 28px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
@php
    $money = function ($value) {
        return $value === null ? '-' : number_format(round($value), 0, ',', '.');
    };
    $pct = function ($value) {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%';
    };
    $sponsorName = $sponsor->branding_name ?: $sponsor->name;
    $currencies  = array_filter(['IDR' => $tax['idr'], 'USD' => $tax['usd']]);
@endphp
<div class="card">
    <div class="header">
        <p class="eyebrow">Sponsorship Payment &middot; Received</p>
        <h2>Payment Received &mdash; {{ $sponsorName }}</h2>
    </div>
    <div class="body">
        <p class="intro">Dear Secretariat Team,</p>
        <div class="alert-box">
            The Finance team has confirmed that <strong>{{ $sponsorName }}</strong> has paid
            @if ($renewal->invoice_number) invoice <strong>{{ $renewal->invoice_number }}</strong> @else the invoice @endif
            for the <strong>{{ $period }}</strong> sponsorship contract.
            Payment was received on <strong>{{ $renewal->paid_date->format('d F Y') }}</strong>.
        </div>

        <h3>Sponsor &amp; Contract</h3>
        <table class="info">
            <tr><td class="label">Sponsor</td><td><strong>{{ $sponsorName }}</strong>@if ($sponsorName !== $sponsor->name)<br><span style="color:#888;">{{ $sponsor->name }}</span>@endif</td></tr>
            <tr><td class="label">Package</td><td>{{ $package }}</td></tr>
            <tr><td class="label">Contract Period</td><td>{{ $period }}</td></tr>
            <tr><td class="label">Quotation Number</td><td>{{ $renewal->quotation_number ?: '-' }}</td></tr>
        </table>

        <h3>Invoice &amp; Payment</h3>
        <table class="info">
            <tr><td class="label">Invoice Number</td><td>{{ $renewal->invoice_number ?: '-' }}</td></tr>
            <tr><td class="label">Invoice Date</td><td>{{ $renewal->invoice_date ? $renewal->invoice_date->format('d F Y') : '-' }}</td></tr>
            <tr><td class="label">Invoice Sent</td><td>{{ $renewal->invoice_sent_date ? $renewal->invoice_sent_date->format('d F Y') : '-' }}</td></tr>
            <tr><td class="label">Paid Date</td><td><strong>{{ $renewal->paid_date->format('d F Y') }}</strong></td></tr>
            @if ($renewal->payment_turnaround_days !== null)
                <tr><td class="label">Payment Turnaround</td><td>{{ $renewal->payment_turnaround_days }} days from invoice date</td></tr>
            @endif
        </table>

        <h3>Amount</h3>
        @if (empty($currencies))
            <div style="font-size:12px;color:#999;font-style:italic;">No contract amount on record.</div>
        @else
            <table class="info">
                @foreach ($currencies as $currency => $row)
                    <tr><td class="label">Amount ({{ $currency }})</td><td>{{ $currency }} {{ $money($row['base']) }}</td></tr>
                    @if ($tax['vatPercent'] > 0)
                        <tr><td class="label">VAT {{ $pct($tax['vatPercent']) }}</td><td>{{ $currency }} {{ $money($row['vat']) }}</td></tr>
                        <tr><td class="label">Grand Total ({{ $currency }})</td><td><strong>{{ $currency }} {{ $money($row['grand']) }}</strong> <span style="color:#888;">(incl. VAT)</span></td></tr>
                    @endif
                    @if ($tax['pph23Percent'] > 0)
                        <tr><td class="label">PPh 23 {{ $pct($tax['pph23Percent']) }} withheld</td><td>- {{ $currency }} {{ $money($row['pph23']) }}</td></tr>
                    @endif
                    <tr class="total"><td class="label">{{ $tax['pph23Percent'] > 0 ? 'Net Received' : 'Total' }} ({{ $currency }})</td><td>{{ $currency }} {{ $money($row['net']) }}</td></tr>
                    @if (!$loop->last)<tr><td colspan="2" style="padding:6px 0;"></td></tr>@endif
                @endforeach
            </table>
        @endif

        @if ($renewal->notes)
            <h3>Notes</h3>
            <div>{!! nl2br(e($renewal->notes)) !!}</div>
        @endif

        <div class="meta">Recorded by {{ $recordedBy }} &middot; {{ $sentAt }}</div>
    </div>
    <div class="footer">
        This is an automated notification from the Djakarta Mining Club system.
    </div>
</div>
</body>
</html>
