{{-- ═══ SUMMARY CARDS ═══
     Collapsed by default. Toggle via button. --}}
@php
    $pkgMeta = [
        'platinum' => ['label' => 'Platinum', 'color' => '#6777ef'],
        'gold'     => ['label' => 'Gold',     'color' => '#f39c12'],
        'silver'   => ['label' => 'Silver',   'color' => '#6c757d'],
    ];
    $progressCards = [
        ['key' => 'renewal', 'label' => 'Renewal',     'count' => $renewedCount, 'color' => '#3abaf4', 'icon' => 'fas fa-redo-alt'],
        ['key' => 'upgrade', 'label' => 'Upgrade',     'count' => $upgradeCount, 'color' => '#f39c12', 'icon' => 'fas fa-arrow-up'],
        ['key' => 'new',     'label' => 'New Sponsor', 'count' => $newCount,     'color' => '#47c363', 'icon' => 'fas fa-star'],
    ];
    $confirmedTotal = $renewedCount + $upgradeCount + $newCount;
    $confirmedBreakdown = [];
    foreach ($pkgMeta as $pk => $pm) {
        $confirmedBreakdown[$pk] = ($packageBreakdown['renewal'][$pk] ?? 0)
            + ($packageBreakdown['upgrade'][$pk] ?? 0)
            + ($packageBreakdown['new'][$pk] ?? 0);
    }
    $pendingPkg = $pendingRenewals->countBy('package');
@endphp

<div class="mb-3">
    <button class="btn btn-light border btn-sm" type="button" data-toggle="collapse" data-target="#summaryCardsCollapse" aria-expanded="false" aria-controls="summaryCardsCollapse">
        <i class="fas fa-chart-bar mr-1"></i> Detail Progress & Pipeline
        <i class="fas fa-chevron-down ml-1" style="font-size:10px;"></i>
    </button>
</div>

<div class="collapse" id="summaryCardsCollapse">

    {{-- ── Group A: Positive Progress ── --}}
    <div class="d-flex align-items-center mb-2" style="gap:8px;">
        <span class="text-uppercase font-weight-700" style="font-size:11px;letter-spacing:.8px;color:#47c363;">
            <i class="fas fa-chart-line mr-1"></i>Positive Progress
        </span>
        <span style="font-size:11px;color:#aaa;">deals closed in {{ $year }}</span>
    </div>
    <div class="row">
        @foreach($progressCards as $sc)
        <div class="col-lg-4 col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" style="border-bottom: 3px solid {{ $sc['color'] }};">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:{{ $sc['color'] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="{{ $sc['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;">{{ $sc['label'] }}</div>
                            <div class="font-weight-700" style="font-size:24px;line-height:1.1;color:#2d3748;">{{ $sc['count'] }}</div>
                        </div>
                    </div>
                    <div class="d-flex mt-3 pt-2" style="gap:6px;border-top:1px dashed #e4e6fc;">
                        @foreach($pkgMeta as $pk => $pm)
                            @php $cnt = $packageBreakdown[$sc['key']][$pk] ?? 0; @endphp
                            <div class="flex-fill text-center" style="background:#f8f9fc;border-radius:6px;padding:4px 2px;">
                                <div style="font-size:9px;font-weight:700;color:{{ $pm['color'] }};text-transform:uppercase;letter-spacing:.3px;">{{ $pm['label'] }}</div>
                                <div style="font-size:14px;font-weight:700;color:{{ $cnt > 0 ? '#2d3748' : '#cbd5e0' }};">{{ $cnt }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Group B: Pipeline Status ── --}}
    <div class="d-flex align-items-center mb-2" style="gap:8px;">
        <span class="text-uppercase font-weight-700" style="font-size:11px;letter-spacing:.8px;color:#6777ef;">
            <i class="fas fa-stream mr-1"></i>Current Pipeline Status
        </span>
        <span style="font-size:11px;color:#aaa;">confirmed &rarr; pending &rarr; lost</span>
    </div>
    <div class="row">
        {{-- Confirmed --}}
        <div class="col-lg-4 col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" style="border-bottom: 3px solid #6777ef;background:linear-gradient(135deg,#6777ef 0%,#5263d8 100%);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div>
                            <div class="text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;color:rgba(255,255,255,.75);">Confirmed</div>
                            <div class="font-weight-700" style="font-size:24px;line-height:1.1;color:#fff;">{{ $confirmedTotal }}</div>
                            <div style="font-size:9px;color:rgba(255,255,255,.7);line-height:1.2;">renewal + upgrade + new sponsor</div>
                        </div>
                    </div>
                    <div class="d-flex mt-2 pt-2" style="gap:6px;border-top:1px dashed rgba(255,255,255,.3);">
                        @foreach($pkgMeta as $pk => $pm)
                            <div class="flex-fill text-center" style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 2px;">
                                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.3px;">{{ $pm['label'] }}</div>
                                <div style="font-size:14px;font-weight:700;color:#fff;">{{ $confirmedBreakdown[$pk] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="col-lg-4 col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" role="button" title="View pending renewal list"
                 onclick="document.getElementById('pending-tab').click(); document.getElementById('reportTabs').scrollIntoView({behavior:'smooth'});"
                 style="border:2px dashed #f39c12;background:#fffbf0;box-shadow:none;cursor:pointer;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#f39c12;color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div>
                            <div class="text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;color:#b07c20;">Pending</div>
                            <div class="font-weight-700" style="font-size:24px;line-height:1.1;color:#8a4a00;">{{ $pendingRenewals->count() }}</div>
                            <div style="font-size:9px;color:#b07c20;line-height:1.2;">waiting sponsor confirmation</div>
                        </div>
                    </div>
                    <div class="d-flex mt-2 pt-2" style="gap:6px;border-top:1px dashed #fde3aa;">
                        @foreach($pkgMeta as $pk => $pm)
                            @php $cnt = $pendingPkg[$pk] ?? 0; @endphp
                            <div class="flex-fill text-center" style="background:rgba(243,156,18,.08);border-radius:6px;padding:4px 2px;">
                                <div style="font-size:9px;font-weight:700;color:{{ $pm['color'] }};text-transform:uppercase;letter-spacing:.3px;">{{ $pm['label'] }}</div>
                                <div style="font-size:14px;font-weight:700;color:{{ $cnt > 0 ? '#8a4a00' : '#dcc9a0' }};">{{ $cnt }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Lost --}}
        <div class="col-lg-4 col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" style="border:2px dashed #fc544b;background:#fff7f7;box-shadow:none;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#fc544b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <div class="text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;color:#c0392b;">Lost</div>
                            <div class="font-weight-700" style="font-size:24px;line-height:1.1;color:#a02622;">{{ $notRenewedCount }}</div>
                            <div style="font-size:9px;color:#c0392b;line-height:1.2;">did not renew this year</div>
                        </div>
                    </div>
                    <div class="d-flex mt-2 pt-2" style="gap:6px;border-top:1px dashed #fcc;">
                        @foreach($pkgMeta as $pk => $pm)
                            @php $cnt = $packageBreakdown['not_renewed'][$pk] ?? 0; @endphp
                            <div class="flex-fill text-center" style="background:rgba(252,84,75,.06);border-radius:6px;padding:4px 2px;">
                                <div style="font-size:9px;font-weight:700;color:{{ $pm['color'] }};text-transform:uppercase;letter-spacing:.3px;">{{ $pm['label'] }}</div>
                                <div style="font-size:14px;font-weight:700;color:{{ $cnt > 0 ? '#a02622' : '#e8c2c0' }};">{{ $cnt }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end collapse --}}
