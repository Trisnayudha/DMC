<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Renewal Confirmed</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f7; margin: 0; padding: 24px; color: #333; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #1f6f43; padding: 24px 28px; }
        .header h2 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 24px 28px; }
        .alert-box { background: #e8f5ee; border-left: 4px solid #1f6f43; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; }
        .warn-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px 14px; border-radius: 4px; font-size: 12px; }
        h3 { font-size: 14px; margin: 22px 0 10px; color: #1f6f43; border-bottom: 1px solid #eee; padding-bottom: 6px; }
        table.info { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.info td { padding: 5px 0; vertical-align: top; }
        table.info td.label { width: 150px; color: #888; }
        table.contacts { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.contacts th { background: #f8f9fa; text-align: left; padding: 7px 8px; border: 1px solid #e5e5e5; }
        table.contacts td { padding: 7px 8px; border: 1px solid #e5e5e5; }
        .empty { font-size: 12px; color: #999; font-style: italic; }
        .footer { background: #f8f9fa; padding: 14px 28px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h2>✅ Renewal Confirmed — Mohon Issue Invoice</h2>
    </div>
    <div class="body">
        <div class="alert-box">
            <strong>{{ $sponsor->name }}</strong> sudah <strong>konfirmasi renewal</strong> untuk tahun {{ $followup->renewal_year }}
            berdasarkan follow-up tim secretariat. Mohon dibuatkan invoice-nya di Accurate.
            Bukti konfirmasi terlampir.
        </div>

        <h3>Detail Sponsor</h3>
        <table class="info">
            <tr><td class="label">Nama Perusahaan</td><td><strong>{{ $sponsor->name }}</strong></td></tr>
            <tr><td class="label">Paket</td><td>{{ $package }}</td></tr>
            <tr><td class="label">Kontrak Berakhir</td><td>{{ $contractEnd }}</td></tr>
            <tr><td class="label">Alamat</td><td>{!! nl2br(e($sponsor->address ?: '-')) !!}</td></tr>
            <tr><td class="label">Email Perusahaan</td><td>{{ $sponsor->email ?: '-' }}</td></tr>
        </table>

        <h3>Nilai Renewal (Renewal Form)</h3>
        @if ($form)
            <table class="info">
                <tr><td class="label">No. Form</td><td><strong>{{ $form->form_number }}</strong></td></tr>
                <tr><td class="label">Tahun Renewal</td><td>{{ $form->renewal_year }}</td></tr>
                <tr><td class="label">KMK Rate</td><td>IDR {{ number_format($form->kmk_rate, 0, ',', '.') }}/USD</td></tr>
                <tr><td class="label">KMK Number</td><td>{{ $form->kmk_number ?: '-' }}</td></tr>
                @if ($form->amount_usd)
                    <tr><td class="label">Amount USD</td><td><strong>USD {{ number_format($form->amount_usd, 0, ',', '.') }}</strong></td></tr>
                @endif
                @if ($form->amount_idr)
                    <tr><td class="label">Amount IDR</td><td><strong>IDR {{ number_format($form->amount_idr, 0, ',', '.') }}</strong></td></tr>
                @endif
                <tr><td class="label">VAT / PPN</td><td>{{ (float) $form->vat_percent > 0 ? rtrim(rtrim(number_format($form->vat_percent, 2, '.', ''), '0'), '.') . '%' : '0% (No VAT)' }}</td></tr>
            </table>
        @else
            <div class="warn-box">Renewal form untuk tahun {{ $followup->renewal_year }} tidak ditemukan — mohon cek nilai ke tim sponsorship.</div>
        @endif

        <h3>Hasil Follow-up</h3>
        <table class="info">
            <tr><td class="label">Tanggal</td><td>{{ $followup->followed_up_at->format('d F Y') }}</td></tr>
            <tr><td class="label">Channel</td><td>{{ $followup->channel ? ucfirst($followup->channel) : '-' }}</td></tr>
            @if ($followup->notes)
                <tr><td class="label">Notes</td><td>{!! nl2br(e($followup->notes)) !!}</td></tr>
            @endif
            <tr><td class="label">Dikonfirmasi oleh</td><td>{{ $confirmedBy }}</td></tr>
            <tr><td class="label">Waktu</td><td>{{ $sentAt }}</td></tr>
        </table>

        <h3>Billing Contact</h3>
        @if ($billings->isEmpty())
            <div class="empty">Belum ada data billing — silakan konfirmasi ke tim sponsorship.</div>
        @else
            <table class="contacts">
                <tr><th>Nama</th><th>Jabatan</th><th>Email</th><th>Phone</th></tr>
                @foreach ($billings as $billing)
                    <tr>
                        <td>{{ $billing->name }}</td>
                        <td>{{ $billing->title ?: '-' }}</td>
                        <td>{{ $billing->email ?: '-' }}</td>
                        <td>{{ $billing->phone ?: '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        <h3>PIC / Contact Person</h3>
        @if ($pics->isEmpty())
            <div class="empty">Belum ada data PIC.</div>
        @else
            <table class="contacts">
                <tr><th>Nama</th><th>Jabatan</th><th>Email</th><th>Phone</th></tr>
                @foreach ($pics as $pic)
                    <tr>
                        <td>{{ $pic->name }}</td>
                        <td>{{ $pic->title ?: '-' }}</td>
                        <td>{{ $pic->email ?: '-' }}</td>
                        <td>{{ $pic->phone ?: '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
    <div class="footer">
        Email ini dikirim otomatis oleh DMC Finance Notification System. Jangan balas email ini.
    </div>
</div>
</body>
</html>
