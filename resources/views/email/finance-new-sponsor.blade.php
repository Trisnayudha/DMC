<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sponsor Baru</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f7; margin: 0; padding: 24px; color: #333; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #1f6f43; padding: 24px 28px; }
        .header h2 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 24px 28px; }
        .alert-box { background: #e8f5ee; border-left: 4px solid #1f6f43; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; }
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
        <h2>🏢 Sponsor Baru — Mohon Dibuatkan Invoice</h2>
    </div>
    <div class="body">
        <div class="alert-box">
            Sponsor baru <strong>{{ $sponsor->name }}</strong> baru saja ditambahkan ke sistem DMC.
            Mohon tim finance membuatkan invoice berdasarkan detail di bawah ini.
        </div>
        @if ($sponsor->status === 'draft')
            <div class="alert-box" style="background:#fff3cd;border-left-color:#ffc107;">
                Status sponsor masih <strong>DRAFT</strong> (belum tampil di website).
            </div>
        @endif

        <h3>Detail Sponsor</h3>
        <table class="info">
            <tr><td class="label">Nama Perusahaan</td><td><strong>{{ $sponsor->name }}</strong></td></tr>
            @if ($sponsor->branding_name)
                <tr><td class="label">Branding Name</td><td>{{ $sponsor->branding_name }}</td></tr>
            @endif
            <tr><td class="label">Paket</td><td><strong>{{ $package }}</strong></td></tr>
            <tr><td class="label">Periode Kontrak</td><td><strong>{{ $period }}</strong></td></tr>
            <tr><td class="label">Status</td><td>{{ ucfirst($sponsor->status ?? '-') }}</td></tr>
            <tr><td class="label">Alamat</td><td>{!! nl2br(e($sponsor->address ?: '-')) !!}</td></tr>
            @if ($sponsor->location_office)
                <tr><td class="label">Lokasi Kantor</td><td>{{ $sponsor->location_office }}</td></tr>
            @endif
            <tr><td class="label">Email Perusahaan</td><td>{{ $sponsor->email ?: '-' }}</td></tr>
            <tr><td class="label">Website</td><td>{{ $sponsor->company_website ?: '-' }}</td></tr>
            @if ($sponsor->company_category)
                <tr><td class="label">Kategori</td><td>{{ $sponsor->company_category }}</td></tr>
            @endif
        </table>

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

        <table class="info" style="margin-top:18px;">
            <tr><td class="label">Dibuat oleh</td><td>{{ $created_by }}</td></tr>
            <tr><td class="label">Waktu</td><td>{{ $created_at }}</td></tr>
        </table>
    </div>
    <div class="footer">
        Email ini dikirim otomatis oleh DMC Finance Notification System. Jangan balas email ini.
    </div>
</div>
</body>
</html>
