            {{-- ═══ SUMMARY CARDS ═══ --}}
            @php
                $pkgMeta = [
                    'platinum' => ['label' => 'Platinum', 'color' => '#6777ef'],
                    'gold'     => ['label' => 'Gold',     'color' => '#f39c12'],
                    'silver'   => ['label' => 'Silver',   'color' => '#6c757d'],
                ];
                $summaryCards = [
                    ['key' => 'renewal',     'label' => 'Renewal',     'count' => $renewedCount,    'color' => '#3abaf4', 'icon' => 'fas fa-redo-alt'],
                    ['key' => 'upgrade',     'label' => 'Upgrade',     'count' => $upgradeCount,    'color' => '#f39c12', 'icon' => 'fas fa-arrow-up'],
                    ['key' => 'new',         'label' => 'New Sponsor', 'count' => $newCount,        'color' => '#47c363', 'icon' => 'fas fa-star'],
                    ['key' => 'not_renewed', 'label' => 'Not Renewed', 'count' => $notRenewedCount, 'color' => '#fc544b', 'icon' => 'fas fa-times-circle'],
                ];
                $grandTotal = $renewedCount + $upgradeCount + $newCount + $notRenewedCount;
                $totalBreakdown = [];
                foreach ($pkgMeta as $pk => $pm) {
                    $totalBreakdown[$pk] = collect($packageBreakdown)->sum(fn ($b) => $b[$pk] ?? 0);
                }
            @endphp
            <div class="row">
                @foreach($summaryCards as $sc)
                <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-4 d-flex">
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

                {{-- Total card --}}
                <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-4 d-flex">
                    <div class="card mb-0 flex-fill" style="border-bottom: 3px solid #6777ef;background:linear-gradient(135deg,#6777ef 0%,#5263d8 100%);">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center" style="gap:12px;">
                                <div style="width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div>
                                    <div class="text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;color:rgba(255,255,255,.75);">Total</div>
                                    <div class="font-weight-700" style="font-size:24px;line-height:1.1;color:#fff;">{{ $grandTotal }}</div>
                                </div>
                            </div>
                            <div class="d-flex mt-3 pt-2" style="gap:6px;border-top:1px dashed rgba(255,255,255,.3);">
                                @foreach($pkgMeta as $pk => $pm)
                                    <div class="flex-fill text-center" style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 2px;">
                                        <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.3px;">{{ $pm['label'] }}</div>
                                        <div style="font-size:14px;font-weight:700;color:#fff;">{{ $totalBreakdown[$pk] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
