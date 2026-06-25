{{-- ═══ DASHBOARD CARDS ═══
     5 card utama: Total Sponsor, This Year Sponsor, Pending Renewal, Kadaluarsa, Not Renew --}}
@if (isset($dashboardCards))
    @php $dc = $dashboardCards; @endphp
    <div class="row">
        {{-- 1. Total Sponsor --}}
        <div class="col-lg col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" style="border-bottom: 3px solid #6777ef;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#6777ef;color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;">Total Sponsor</div>
                            <div class="font-weight-700" style="font-size:28px;line-height:1.1;color:#2d3748;">{{ $dc['totalSponsor'] }}</div>
                            <div style="font-size:10px;color:#888;">keseluruhan sponsor aktif</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. This Year Sponsor --}}
        <div class="col-lg col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" style="border-bottom: 3px solid #47c363;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#47c363;color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;">This Year Sponsor</div>
                            <div class="font-weight-700" style="font-size:28px;line-height:1.1;color:#2d3748;">{{ $dc['thisYearConfirmed'] }}</div>
                            <div style="font-size:10px;color:#888;">confirmed di {{ $year }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Pending Renewal --}}
        <div class="col-lg col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" role="button" title="View pending renewal list"
                 onclick="document.getElementById('pending-tab').click(); document.getElementById('reportTabs').scrollIntoView({behavior:'smooth'});"
                 style="border-bottom: 3px solid #f39c12; cursor:pointer;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#f39c12;color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;">Pending Renewal</div>
                            <div class="font-weight-700" style="font-size:28px;line-height:1.1;color:#2d3748;">{{ $dc['pendingRenewalCount'] }}</div>
                            <div style="font-size:10px;color:#888;">perlu di-follow up</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Kadaluarsa --}}
        <div class="col-lg col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" role="button" title="View 30-day priority contracts"
                 onclick="document.getElementById('priorityContracts').scrollIntoView({behavior:'smooth'});"
                 style="border-bottom: 3px solid #e3342f; cursor:pointer;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#e3342f;color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;">Kadaluarsa</div>
                            <div class="font-weight-700" style="font-size:28px;line-height:1.1;color:#2d3748;">{{ $dc['expiredCount'] }}</div>
                            <div style="font-size:10px;color:#888;">kontrak sudah lewat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Not Renew --}}
        <div class="col-lg col-md-6 col-sm-6 mb-4 d-flex">
            <div class="card mb-0 flex-fill" role="button" title="View not renewed list"
                 onclick="document.getElementById('notrenewed-tab').click(); document.getElementById('reportTabs').scrollIntoView({behavior:'smooth'});"
                 style="border-bottom: 3px solid #fc544b; cursor:pointer;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <div style="width:44px;height:44px;border-radius:10px;background:#fc544b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted text-uppercase font-weight-600" style="font-size:10px;letter-spacing:.5px;">Not Renew</div>
                            <div class="font-weight-700" style="font-size:28px;line-height:1.1;color:#2d3748;">{{ $dc['notRenewCount'] }}</div>
                            <div style="font-size:10px;color:#888;">tidak perpanjang {{ $year }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
