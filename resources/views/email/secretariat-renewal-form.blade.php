<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Renewal Form</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f7; margin: 0; padding: 24px; color: #333; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #c8611c; padding: 24px 28px; }
        .header h2 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 24px 28px; }
        .alert-box { background: #fdf1e7; border-left: 4px solid #c8611c; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; }
        h3 { font-size: 14px; margin: 22px 0 10px; color: #c8611c; border-bottom: 1px solid #eee; padding-bottom: 6px; }
        table.info { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.info td { padding: 5px 0; vertical-align: top; }
        table.info td.label { width: 150px; color: #888; }
        .empty { font-size: 12px; color: #999; font-style: italic; }
        .footer { background: #f8f9fa; padding: 14px 28px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h2>📄 Renewal Form {{ $isUpdate ? 'Updated' : 'Generated' }} — {{ $sponsor->name }}</h2>
    </div>
    <div class="body">
        <div class="alert-box">
            @if ($isUpdate)
                Renewal form <strong>{{ $form->form_number }}</strong> untuk <strong>{{ $sponsor->name }}</strong> telah <strong>diperbarui</strong>.
                PDF terbaru terlampir — mohon gunakan versi ini dan abaikan versi sebelumnya.
            @else
                Renewal form <strong>{{ $form->form_number }}</strong> untuk <strong>{{ $sponsor->name }}</strong> telah dibuat.
                PDF renewal form terlampir di email ini.
            @endif
        </div>

        <h3>Detail Renewal Form</h3>
        <table class="info">
            <tr><td class="label">Sponsor</td><td><strong>{{ $sponsor->name }}</strong></td></tr>
            <tr><td class="label">Paket</td><td>{{ $package }}</td></tr>
            <tr><td class="label">Kontrak Berakhir</td><td>{{ $contractEnd }}</td></tr>
            <tr><td class="label">No. Form</td><td><strong>{{ $form->form_number }}</strong></td></tr>
            <tr><td class="label">Tahun Renewal</td><td>{{ $form->renewal_year }}</td></tr>
            <tr><td class="label">Quotation Date</td><td>{{ $form->generated_at ? $form->generated_at->format('d F Y') : '-' }}</td></tr>
            <tr><td class="label">KMK Rate</td><td>IDR {{ number_format($form->kmk_rate, 0, ',', '.') }}/USD</td></tr>
            <tr><td class="label">KMK Number</td><td>{{ $form->kmk_number ?: '-' }}</td></tr>
            @if ($form->amount_usd)
                <tr><td class="label">Amount USD</td><td><strong>USD {{ number_format($form->amount_usd, 0, ',', '.') }}</strong></td></tr>
            @endif
            @if ($form->amount_idr)
                <tr><td class="label">Amount IDR</td><td><strong>IDR {{ number_format($form->amount_idr, 0, ',', '.') }}</strong></td></tr>
            @endif
            <tr><td class="label">VAT / PPN</td><td>{{ (float) $form->vat_percent > 0 ? rtrim(rtrim(number_format($form->vat_percent, 2, '.', ''), '0'), '.') . '%' : '0% (No VAT)' }}</td></tr>
            @if ($form->notes)
                <tr><td class="label">Notes</td><td>{!! nl2br(e($form->notes)) !!}</td></tr>
            @endif
        </table>

        <h3>PIC / Contact Person</h3>
        @if ($pic)
            <table class="info">
                <tr><td class="label">Nama</td><td>{{ $pic->name ?: '-' }}</td></tr>
                <tr><td class="label">Jabatan</td><td>{{ $pic->title ?: '-' }}</td></tr>
                <tr><td class="label">Email</td><td>{{ $pic->email ?: '-' }}</td></tr>
                <tr><td class="label">Phone</td><td>{{ $pic->phone ?: '-' }}</td></tr>
            </table>
        @else
            <div class="empty">Belum ada data PIC.</div>
        @endif

        <table class="info" style="margin-top:18px;">
            <tr><td class="label">Dibuat oleh</td><td>{{ $generatedBy }}</td></tr>
            <tr><td class="label">Waktu</td><td>{{ $sentAt }}</td></tr>
        </table>
    </div>
    <div class="footer">
        Email ini dikirim otomatis oleh sistem DMC. Jangan balas email ini.
    </div>
</div>
</body>
</html>
