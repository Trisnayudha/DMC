{{-- Badge penamaan urgensi untuk kontrak yang belum ada konfirmasi.
     Param: $stage ('pending' = sudah kelewatan, 'awaiting' = bulan ini, 'upcoming' = bulan berikutnya). --}}
@php $stage = $stage ?? 'upcoming'; @endphp
@if($stage === 'pending')
    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:#fc544b;">
        <i class="fas fa-exclamation-triangle" style="font-size:10px;"></i> Pending
    </span>
    <div style="font-size:10px;color:#fc544b;margin-top:3px;">Contract passed — needs follow-up</div>
@elseif($stage === 'awaiting')
    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:#f39c12;">
        <i class="fas fa-hourglass-half" style="font-size:10px;"></i> Awaiting
    </span>
    <div style="font-size:10px;color:#e67e22;margin-top:3px;">Due this month</div>
@else
    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;color:#6c757d;background:#f0f1f5;">
        <i class="fas fa-clock" style="font-size:10px;"></i> Upcoming
    </span>
@endif
