{{-- Sponsor headcount per perusahaan: jawaban untuk "jumlah sponsor tahun ini,
     achieved atau tidak vs tahun lalu, dan sisa renewal sebagai pengingat".
     Hanya tampil di tahun berjalan ($headcount null untuk tahun lain). --}}
@if ($headcount)
    @php
        $h = $headcount;
        $pendingCompanies = $pendingRenewals->pluck('sponsor_id')->unique()->count();
        $achieved = $h['netChange'] >= 0;
    @endphp
    <div class="card" style="border-top: 3px solid {{ $achieved ? '#47c363' : '#fc544b' }};">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="fas fa-bullseye mr-2"
                        style="color:{{ $achieved ? '#47c363' : '#fc544b' }};"></i>Sponsor Count — {{ $year }} vs
                    {{ $h['prevYear'] }}</h4>
                <small class="text-muted">Counted per company (one sponsor = one company, regardless of how many
                    contracts)</small>
            </div>
            <div class="text-right">
                <div style="font-size:26px;font-weight:800;line-height:1;color:#2d3748;">
                    {{ $h['currentCount'] }}
                    <span style="font-size:13px;font-weight:700;color:{{ $achieved ? '#47c363' : '#fc544b' }};">
                        {{ $h['netChange'] >= 0 ? '+' : '' }}{{ $h['netChange'] }} vs {{ $h['prevYear'] }}
                    </span>
                </div>
                <div style="font-size:12px;color:#888;">active sponsors today</div>
            </div>
        </div>
        <div class="card-body pb-3">
            {{-- Bridge: last year → lost → new → now --}}
            <div class="d-flex align-items-stretch flex-wrap" style="gap:8px;">
                <div class="flex-fill text-center"
                    style="background:#f8f9fc;border:2px solid #e4e6fc;border-radius:10px;padding:12px 8px;min-width:130px;">
                    <div
                        style="font-size:10px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.4px;">
                        Sponsors {{ $h['prevYear'] }}</div>
                    <div style="font-size:24px;font-weight:800;color:#2d3748;">{{ $h['lastYearCount'] }}</div>
                </div>
                <div class="d-flex align-items-center" style="color:#ccc;"><i class="fas fa-chevron-right"></i></div>
                <div class="flex-fill text-center"
                    style="background:#fde8e8;border:2px solid #fcc;border-radius:10px;padding:12px 8px;min-width:130px;">
                    <div
                        style="font-size:10px;font-weight:700;color:#fc544b;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fas fa-times-circle mr-1"></i>Not Renewed
                    </div>
                    <div style="font-size:24px;font-weight:800;color:#fc544b;">−{{ $h['lostCount'] }}</div>
                </div>
                <div class="d-flex align-items-center" style="color:#ccc;"><i class="fas fa-chevron-right"></i></div>
                <div class="flex-fill text-center"
                    style="background:#eafaf0;border:2px solid #bfe8cc;border-radius:10px;padding:12px 8px;min-width:130px;">
                    <div
                        style="font-size:10px;font-weight:700;color:#47c363;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fas fa-star mr-1"></i>New Sponsors
                    </div>
                    <div style="font-size:24px;font-weight:800;color:#47c363;">+{{ $h['newCount'] }}</div>
                </div>
                <div class="d-flex align-items-center" style="color:#ccc;"><i class="fas fa-chevron-right"></i></div>
                <div class="flex-fill text-center"
                    style="background:{{ $achieved ? '#47c363' : 'linear-gradient(135deg,#6777ef 0%,#5263d8 100%)' }};border-radius:10px;padding:12px 8px;min-width:130px;">
                    <div
                        style="font-size:10px;font-weight:700;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.4px;">
                        Active Sponsors {{ $year }}</div>
                    <div style="font-size:24px;font-weight:800;color:#fff;">{{ $h['currentCount'] }}</div>
                </div>
            </div>

            {{-- Formula helper: biar pembaca tidak perlu menghitung sendiri --}}
            <div class="text-center mt-2" style="font-size:12px;color:#888;">
                <i class="fas fa-calculator mr-1" style="opacity:.5;"></i>
                {{ $h['lastYearCount'] }} − {{ $h['lostCount'] }} + {{ $h['newCount'] }} =
                <strong style="color:#2d3748;">{{ $h['currentCount'] }} active sponsors</strong>
            </div>

            {{-- Reminder sisa renewal --}}
            @if ($pendingCompanies > 0)
                <div class="mt-3">
                    <a href="#pendingPane" onclick="document.getElementById('pending-tab').click();"
                        style="font-size:12px;font-weight:700;color:#8a4a00;background:#fde3aa;border-radius:10px;padding:6px 14px;text-decoration:none;display:inline-block;">
                        <i class="fas fa-bell mr-1"></i>{{ $pendingCompanies }} of {{ $h['currentCount'] }} sponsors
                        still need renewal confirmation this year
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
