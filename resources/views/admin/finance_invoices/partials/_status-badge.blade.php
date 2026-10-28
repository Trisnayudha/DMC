@php
    $badgeClass = [
        'pending'   => 'badge-warning',
        'paid'      => 'badge-success',
        'expired'   => 'badge-secondary',
        'cancelled' => 'badge-dark',
    ][$invoice->status] ?? 'badge-light';
    // VA lewat jatuh tempo tapi webhook expired belum masuk → tampilkan sebagai expired.
    $vaLapsed = $invoice->status === 'pending' && $invoice->va_expires_at && $invoice->va_expires_at->isPast();
@endphp
<span class="badge {{ $vaLapsed ? 'badge-secondary' : $badgeClass }}">
    {{ $vaLapsed ? 'VA Expired' : $invoice->statusLabel() }}
</span>
