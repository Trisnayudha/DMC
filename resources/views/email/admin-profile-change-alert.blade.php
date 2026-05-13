<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Data Change Alert</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f7; margin: 0; padding: 24px; color: #333; }
        .card { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #dc3545; padding: 24px 28px; }
        .header h2 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 24px 28px; }
        .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; }
        .info-row { display: flex; margin-bottom: 8px; font-size: 13px; }
        .info-label { width: 100px; color: #888; flex-shrink: 0; }
        .changes { background: #f8f9fa; border-radius: 6px; padding: 16px; margin: 16px 0; }
        .changes ul { margin: 0; padding-left: 18px; font-size: 13px; line-height: 1.8; }
        .btn { display: inline-block; background: #007bff; color: #fff; text-decoration: none; padding: 10px 22px; border-radius: 5px; font-size: 13px; margin-top: 16px; }
        .footer { background: #f8f9fa; padding: 14px 28px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h2>⚠️ User Mengubah Data Kritis</h2>
    </div>
    <div class="body">
        <div class="alert-box">
            Member yang sudah <strong>verified</strong> baru saja mengubah data penting lewat <strong>{{ $source }}</strong>.
            Harap periksa apakah perubahan ini perlu di-review atau re-verifikasi.
        </div>

        <div class="info-row"><span class="info-label">Nama</span><span>{{ $user_name }}</span></div>
        <div class="info-row"><span class="info-label">Email</span><span>{{ $user_email }}</span></div>
        <div class="info-row"><span class="info-label">User ID</span><span>#{{ $user_id }}</span></div>
        <div class="info-row"><span class="info-label">Sumber</span><span>{{ $source }}</span></div>
        <div class="info-row"><span class="info-label">Waktu</span><span>{{ $changed_at }}</span></div>

        <div class="changes">
            <strong style="font-size:13px;">Field yang berubah:</strong>
            {!! $changes_html !!}
        </div>

        <a href="{{ $admin_url }}" class="btn">Buka Users Management</a>
    </div>
    <div class="footer">
        Email ini dikirim otomatis oleh sistem DMC. Jangan balas email ini.
    </div>
</div>
</body>
</html>
