{{-- Strip 12 bulan (gaya sama dengan Expiry Forecast di Sponsorship Renewals). Klik bulan →
     kartu & tabel hanya bulan itu; klik lagi bulan aktif / "All months" → setahun penuh.
     Warna: ada unpaid = merah muda, semua paid = hijau muda, kosong = abu; bulan aktif = solid. --}}
@php
    $monthShort = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $baseParams = array_filter(['year' => $filters['year'], 'compare' => $filters['compare'], 'keyword' => $filters['keyword']]);
    $yearCount  = collect($months)->sum('count');
    $yearUnpaid = collect($months)->sum('unpaid');
@endphp
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <h4 class="mb-0"><i class="fas fa-calendar-alt mr-1"></i> Invoices by Month — {{ $filters['year'] }}</h4>
        <div class="d-flex align-items-center" style="gap:10px;font-size:12px;">
            <span class="text-muted">{{ $yearCount }} invoices · <span class="text-danger font-weight-600">{{ $yearUnpaid }} unpaid</span></span>
            <a href="{{ route('admin.finance_reports.index', $baseParams) }}"
               class="btn btn-sm {{ $filters['month'] ? 'btn-light border' : 'btn-primary' }}">All months</a>
        </div>
    </div>
    <div class="card-body pb-2" style="overflow-x:auto;">
        <div class="d-flex" style="margin:0 -3px;min-width:720px;">
            @for ($m = 1; $m <= 12; $m++)
                @php
                    $info   = $months[$m];
                    $active = $filters['month'] === $m;
                    if ($info['count'] === 0) {
                        $bg = '#f8f9fa'; $border = '#e9ecef'; $color = '#bbb';
                    } elseif ($info['unpaid'] > 0) {
                        $bg = '#fdecea'; $border = '#f5a19b'; $color = '#a8322a';
                    } else {
                        $bg = '#eafaf0'; $border = '#9fdcb5'; $color = '#1e7a45';
                    }
                    if ($active) {
                        $bg = $info['unpaid'] > 0 ? '#fc544b' : ($info['count'] ? '#47c363' : '#adb5bd');
                        $border = $bg; $color = '#fff';
                    }
                    $href = route('admin.finance_reports.index', $active ? $baseParams : $baseParams + ['month' => $m]);
                @endphp
                <div style="flex:1;padding:0 3px 8px;">
                    <a href="{{ $href }}" class="d-block text-decoration-none"
                       title="{{ $monthShort[$m] }} {{ $filters['year'] }}: {{ $info['paid'] }} paid, {{ $info['unpaid'] }} unpaid · Net Rp {{ number_format($info['net'], 0, ',', '.') }}"
                       style="background:{{ $bg }};border:2px solid {{ $border }};border-radius:8px;text-align:center;padding:10px 4px;{{ $active ? 'box-shadow:0 4px 12px rgba(0,0,0,.15);' : '' }}">
                        <div style="font-size:10px;font-weight:600;color:{{ $color }};letter-spacing:.3px;text-transform:uppercase;">{{ $monthShort[$m] }}</div>
                        <div style="font-size:20px;font-weight:800;color:{{ $color }};line-height:1.2;margin:2px 0;">{{ $info['count'] }}</div>
                        <div style="font-size:9px;color:{{ $color }};{{ $active ? '' : 'opacity:.8;' }}">
                            @if ($info['count'] === 0)
                                invoices
                            @elseif ($info['unpaid'] > 0)
                                {{ $info['unpaid'] }} unpaid
                            @else
                                all paid
                            @endif
                        </div>
                    </a>
                </div>
            @endfor
        </div>
    </div>
</div>
