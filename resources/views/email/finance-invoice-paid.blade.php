<!DOCTYPE html>
<html lang="en">

<body style="font-family: Helvetica, Arial, sans-serif; color:#333; background:#f5f6fa; padding:24px;">
    <div style="max-width:600px; margin:auto; background:#fff; border-radius:8px; padding:28px;">
        <img src="{{ asset('image/dmc.png') }}" alt="Djakarta Mining Club" style="width:90px;">
        <p>Dear {{ $invoice->bill_to_name }},</p>
        <p>We have received your payment for invoice <strong>{{ $invoice->invoice_number }}</strong>. Thank you.</p>

        <table style="width:100%; border-collapse:collapse; margin:16px 0;">
            <tr>
                <td style="padding:6px 0; border-bottom:1px solid #eee;">Amount received</td>
                <td style="padding:6px 0; border-bottom:1px solid #eee; text-align:right;"><strong>IDR {{ number_format($invoice->paid_amount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="padding:6px 0; border-bottom:1px solid #eee;">Payment date</td>
                <td style="padding:6px 0; border-bottom:1px solid #eee; text-align:right;">{{ $invoice->paid_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</td>
            </tr>
            <tr>
                <td style="padding:6px 0;">Method</td>
                <td style="padding:6px 0; text-align:right;">Mandiri Virtual Account</td>
            </tr>
        </table>

        <p>Best regards,<br>Finance Team<br>Djakarta Mining Club</p>
    </div>
</body>

</html>
