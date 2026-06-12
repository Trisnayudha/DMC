{{-- ═══ MONTHLY STATISTICS ═══
     Bar chart Januari–Desember per kategori; klik sebuah bar untuk menampilkan
     daftar sponsor di bulan+kategori tersebut (panel di-pre-render tersembunyi). --}}
@php
    $msMonthNames = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                     7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    $msMonthShort = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
                     7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
    $msCatMeta = [
        'renewal'     => ['label' => 'Renewal',     'color' => '#3abaf4', 'icon' => 'fas fa-redo-alt'],
        'upgrade'     => ['label' => 'Upgrade',     'color' => '#f39c12', 'icon' => 'fas fa-arrow-up'],
        'new'         => ['label' => 'New',         'color' => '#47c363', 'icon' => 'fas fa-star'],
        'not_renewed' => ['label' => 'Not Renewed', 'color' => '#fc544b', 'icon' => 'fas fa-times-circle'],
    ];
    $msPkgColors = ['platinum' => '#6777ef', 'gold' => '#f39c12', 'silver' => '#6c757d'];
    $activeTotal = $renewedCount + $upgradeCount + $newCount + $notRenewedCount;

    // Pending renewal di-plot per bulan KONTRAKNYA HABIS (bukan bulan mulai seperti
    // kategori lain) — itulah bulan keputusan renew/tidaknya ditunggu.
    $msPendingByMonth = array_fill(1, 12, []);
    foreach ($pendingRenewals as $p) {
        if ($p->contract_end) {
            $msPendingByMonth[(int) substr($p->contract_end, 5, 2)][] = $p;
        }
    }
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Statistics by Month — {{ $year }}</h4>
            <small class="text-muted">January – December · click a bar to see which sponsors are behind the number</small>
        </div>
        <div class="text-right">
            <span class="text-muted" style="font-size:13px;">Total records: <strong>{{ $activeTotal }}</strong></span>
        </div>
    </div>
    <div class="card-body pb-2">
        @if($activeTotal > 0)
            <canvas id="monthlyStatsChart" height="84"></canvas>
        @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-chart-bar fa-2x mb-3 d-block" style="opacity:.3;"></i>
                No data available for {{ $year }}
            </div>
        @endif
    </div>

    {{-- Panel detail per bulan+kategori (hanya yang ada isinya) --}}
    <div id="mstatDetailArea" style="display:none;border-top:2px solid #6777ef;">
        @foreach($msMonthNames as $m => $mName)
            @foreach($msCatMeta as $catKey => $cat)
                @php $items = $monthlyDetails[$m][$catKey] ?? []; @endphp
                @if(count($items))
                <div id="mstat{{ $m }}_{{ $catKey }}" style="display:none;">
                    <div style="background:#f8f9fc;padding:10px 20px 8px;display:flex;align-items:center;gap:12px;">
                        <span style="font-weight:700;font-size:14px;color:{{ $cat['color'] }};">
                            <i class="{{ $cat['icon'] }} mr-1"></i>{{ $mName }} {{ $year }} — {{ $cat['label'] }}
                        </span>
                        <span style="font-size:12px;color:#aaa;">{{ count($items) }} sponsor{{ count($items) != 1 ? 's' : '' }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:13px;">
                            <thead>
                                <tr style="background:#fafbff;">
                                    <th style="width:36px;padding:8px 16px;">#</th>
                                    <th style="min-width:220px;">Company</th>
                                    <th style="width:90px;">Package</th>
                                    <th style="min-width:170px;">Period</th>
                                    <th style="min-width:200px;">PIC Contact</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $di => $d)
                                @php
                                    $dPic = $d->sponsor ? $d->sponsor->firstPic : null;
                                    $dPeriod = '—';
                                    if ($d->contract_start && $d->contract_end) {
                                        $dPeriod = \Carbon\Carbon::createFromFormat('Y-m', $d->contract_start)->format('M Y')
                                            . ' – '
                                            . \Carbon\Carbon::createFromFormat('Y-m', $d->contract_end)->format('M Y');
                                    }
                                @endphp
                                <tr>
                                    <td style="padding:10px 16px;color:#aaa;">{{ $di + 1 }}</td>
                                    <td style="padding:10px 16px;">
                                        <div class="font-weight-700" style="color:#2d3748;">{{ $d->sponsor ? $d->sponsor->name : '—' }}</div>
                                        @if($d->sponsor && $d->sponsor->branding_name && $d->sponsor->branding_name !== $d->sponsor->name)
                                            <div style="font-size:11px;color:#888;">{{ $d->sponsor->branding_name }}</div>
                                        @endif
                                    </td>
                                    <td style="padding:10px 16px;">
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $msPkgColors[$d->package] ?? '#6c757d' }};">
                                            {{ ucfirst($d->package ?? '—') }}
                                        </span>
                                    </td>
                                    <td style="padding:10px 16px;color:#555;font-size:12px;">{{ $dPeriod }}</td>
                                    <td style="padding:10px 16px;">
                                        @include('admin.sponsor.annual-report._pic-contact', ['pic' => $dPic, 'color' => $cat['color']])
                                    </td>
                                    <td style="padding:10px 16px;font-size:12px;color:#555;max-width:240px;">
                                        {{ $d->notes ?: '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach

            {{-- Panel pending renewal bulan ini (kontrak habis bulan tsb, belum ada keputusan) --}}
            @php $pendItems = $msPendingByMonth[$m]; @endphp
            @if(count($pendItems))
            <div id="mstat{{ $m }}_pending" style="display:none;">
                <div style="background:#fffbf0;padding:10px 20px 8px;display:flex;align-items:center;gap:12px;">
                    <span style="font-weight:700;font-size:14px;color:#8a4a00;">
                        <i class="fas fa-hourglass-half mr-1"></i>{{ $mName }} {{ $year }} — Pending Renewal
                    </span>
                    <span style="font-size:12px;color:#aaa;">{{ count($pendItems) }} contract{{ count($pendItems) != 1 ? 's' : '' }} ending this month, awaiting decision</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:13px;">
                        <thead>
                            <tr style="background:#fffbf0;">
                                <th style="width:36px;padding:8px 16px;">#</th>
                                <th style="min-width:220px;">Company</th>
                                <th style="width:90px;">Package</th>
                                <th style="min-width:170px;">Current Period</th>
                                <th style="width:130px;">Status</th>
                                <th style="min-width:200px;">PIC Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendItems as $di => $d)
                            @php
                                $dPic = $d->sponsor ? $d->sponsor->firstPic : null;
                                $dPeriod = '—';
                                if ($d->contract_start && $d->contract_end) {
                                    $dPeriod = \Carbon\Carbon::createFromFormat('Y-m', $d->contract_start)->format('M Y')
                                        . ' – '
                                        . \Carbon\Carbon::createFromFormat('Y-m', $d->contract_end)->format('M Y');
                                }
                            @endphp
                            <tr>
                                <td style="padding:10px 16px;color:#aaa;">{{ $di + 1 }}</td>
                                <td style="padding:10px 16px;">
                                    <div class="font-weight-700" style="color:#2d3748;">{{ $d->sponsor ? $d->sponsor->name : '—' }}</div>
                                    @if($d->sponsor && $d->sponsor->branding_name && $d->sponsor->branding_name !== $d->sponsor->name)
                                        <div style="font-size:11px;color:#888;">{{ $d->sponsor->branding_name }}</div>
                                    @endif
                                </td>
                                <td style="padding:10px 16px;">
                                    <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $msPkgColors[$d->package] ?? '#6c757d' }};">
                                        {{ ucfirst($d->package ?? '—') }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px;color:#555;font-size:12px;">{{ $dPeriod }}</td>
                                <td style="padding:10px 16px;">
                                    @include('admin.sponsor.annual-report._pending-stage-badge', ['stage' => $d->pending_stage ?? 'upcoming'])
                                </td>
                                <td style="padding:10px 16px;">
                                    @include('admin.sponsor.annual-report._pic-contact', ['pic' => $dPic, 'color' => '#f39c12'])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>

@if($activeTotal > 0)
@push('bottom')
<script>
    var mstatKeys = ['renewal', 'upgrade', 'new', 'not_renewed', 'pending'];
    var mstatActive = null;

    var mstatChart = new Chart(document.getElementById('monthlyStatsChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_values($msMonthShort)) !!},
            datasets: [
                @foreach($msCatMeta as $catKey => $cat)
                {
                    label: '{{ $cat['label'] }}',
                    backgroundColor: '{{ $cat['color'] }}',
                    hoverBackgroundColor: '{{ $cat['color'] }}',
                    data: {!! json_encode(array_values(array_map(fn ($s) => $s[$catKey], $monthlyStats))) !!}
                },
                @endforeach
                {
                    label: 'Pending Renewal (contract ends)',
                    backgroundColor: '#adb5bd',
                    hoverBackgroundColor: '#adb5bd',
                    data: {!! json_encode(array_values(array_map('count', $msPendingByMonth))) !!}
                }
            ]
        },
        options: {
            legend: { position: 'top', labels: { boxWidth: 12, fontSize: 11 } },
            tooltips: {
                callbacks: {
                    footer: function () { return 'Click to see the sponsor list'; }
                }
            },
            scales: {
                xAxes: [{ gridLines: { display: false } }],
                yAxes: [{ ticks: { beginAtZero: true, stepSize: 1, fontSize: 11 } }]
            },
            onClick: function (evt) {
                var els = mstatChart.getElementAtEvent(evt);
                if (!els.length) return;
                var id = 'mstat' + (els[0]._index + 1) + '_' + mstatKeys[els[0]._datasetIndex];
                mstatShowDetail(id);
            }
        }
    });

    function mstatShowDetail(id) {
        var area = document.getElementById('mstatDetailArea');
        if (mstatActive) {
            var prev = document.getElementById(mstatActive);
            if (prev) prev.style.display = 'none';
        }
        // klik bar yang sama lagi = tutup; bar kosong (panel tidak ada) = tutup juga
        var panel = document.getElementById(id);
        if (mstatActive === id || !panel) {
            area.style.display = 'none';
            mstatActive = null;
            return;
        }
        area.style.display = 'block';
        panel.style.display = 'block';
        mstatActive = id;
        setTimeout(function () {
            area.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 50);
    }
</script>
@endpush
@endif
