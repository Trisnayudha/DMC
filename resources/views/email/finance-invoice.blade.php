<!DOCTYPE html>
<html lang="en">

<body style="font-family: Helvetica, Arial, sans-serif; color:#333; background:#f5f6fa; padding:24px;">
    <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:28px;">
        <img src="{{ asset('image/dmc.png') }}" alt="Djakarta Mining Club" style="width:90px;">
        <p>Dear {{ $invoice->bill_to_name }},</p>
        <p>Please find attached invoice <strong>{{ $invoice->invoice_number }}</strong>
            @if ($invoice->bill_to_company) for <strong>{{ $invoice->bill_to_company }}</strong>@endif
            from Djakarta Mining Club.</p>

        <table style="width:100%; border-collapse:collapse; margin:16px 0;">
            @foreach ($invoice->items as $item)
                <tr>
                    <td style="padding:6px 0; border-bottom:1px solid #eee;">{{ $item['description'] }}@if ($item['qty'] > 1) × {{ $item['qty'] }}@endif</td>
                    <td style="padding:6px 0; border-bottom:1px solid #eee; text-align:right;">IDR {{ number_format($item['amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @if ($invoice->vat_amount > 0)
                <tr>
                    <td style="padding:6px 0;">VAT {{ rtrim(rtrim(number_format($invoice->vat_percent, 2, '.', ''), '0'), '.') }}%</td>
                    <td style="padding:6px 0; text-align:right;">IDR {{ number_format($invoice->vat_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding:8px 0; font-weight:bold;">Total</td>
                <td style="padding:8px 0; text-align:right; font-weight:bold;">IDR {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div style="background:#eef2ff; border:1px solid #c7d2fe; border-radius:6px; padding:14px;">
            <div>Please pay via <strong>Mandiri Virtual Account</strong>:</div>
            <div style="font-size:22px; font-weight:bold; letter-spacing:2px; color:#1e3a8a; margin:6px 0;">{{ $invoice->va_number }}</div>
            <div>Amount: <strong>IDR {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong> (exact amount)</div>
            <div>Valid until: <strong>{{ $invoice->va_expires_at ? $invoice->va_expires_at->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB' : $invoice->due_date->format('d M Y') }}</strong></div>
            <div style="color:#666; font-size:13px; margin-top:6px;">Use Livin' by Mandiri, Mandiri ATM, or Mandiri internet banking. Payment is confirmed automatically and you will receive a receipt by email.</div>
        </div>

        <p style="margin-top:20px;">Thank you,<br>Finance Team<br>Djakarta Mining Club</p>
    </div>
</body>

</html>
