{{-- Isi panel "Statistik & Chart" — dirender server-side oleh UsersController@stats
     (AJAX) hanya saat panel dibuka, bukan saat halaman Members dimuat. --}}
<div class="member-stats-panel">

{{-- Row 3: Members Relation SOP — Verification SLA + Leads --}}
<div class="row">

    <div class="col-lg-8 col-md-12 col-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="mb-0">
                    Verification SLA
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Item yang sedang di-review (admin sudah klik Verify, belum diselesaikan). Target: selesai < 24 jam."></i>
                </h4>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-around text-center mb-3" style="gap:10px;">
                    <div>
                        <h5 class="mb-0" style="color:#0ca30c;">🟢 {{ $countSlaGreen }}</h5>
                        <small class="text-muted">&lt; 24h</small>
                    </div>
                    <div>
                        <h5 class="mb-0" style="color:#fab219;">🟡 {{ $countSlaYellow }}</h5>
                        <small class="text-muted">24–48h</small>
                    </div>
                    <div>
                        <h5 class="mb-0" style="color:#d03b3b;">🔴 {{ $countSlaRed }}</h5>
                        <small class="text-muted">&gt; 48h (Over SLA)</small>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $avgVerificationHours !== null ? $avgVerificationHours . 'h' : '—' }}</h5>
                        <small class="text-muted">Avg. Time (30d)</small>
                    </div>
                </div>
                @if ($picPerformance->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0" style="font-size:12px;">
                            <thead>
                                <tr class="text-muted">
                                    <th>PIC</th>
                                    <th class="text-right">Verified (30d)</th>
                                    <th class="text-right">Avg. Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($picPerformance as $pic)
                                    <tr>
                                        <td>{{ $pic->finished_by_name }}</td>
                                        <td class="text-right">{{ $pic->total }}</td>
                                        <td class="text-right">{{ round($pic->avg_minutes / 60, 1) }}h</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0 small">Belum ada data verifikasi selesai dalam 30 hari terakhir.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-12 mb-4">
        <a href="{{ route('admin.member_leads.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-bullseye mr-1"></i>Leads
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                            title="Member dengan Explore Marketing yang perlu di-follow-up sales/marketing."></i>
                    </h4>
                </div>
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h3 class="mb-1">{{ $countLeads }}</h3>
                    <small class="text-muted mb-3">Total Leads</small>
                    <div class="d-flex justify-content-around">
                        <div>
                            <h5 class="mb-0 text-warning">{{ $countLeadsPendingFollowUp }}</h5>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div>
                            <h5 class="mb-0 text-danger">{{ $countLeadsOverSla }}</h5>
                            <small class="text-muted">Over SLA</small>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

</div>{{-- /row 3 --}}

{{-- Charts — quick visual reporting for management: validation speed, registration
     trend (weekly/monthly), and verification status breakdown. --}}
<div class="row">

    <div class="col-lg-4 col-md-6 col-12 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    New Member Validation
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-html="true"
                        title="Mengukur kecepatan admin memvalidasi member baru sejak tanggal daftar (target: 48 jam), untuk member yang daftar dalam 30 hari terakhir."></i>
                </h4>
                <span class="badge badge-primary">30 Days</span>
            </div>
            <div class="card-body">
                <div style="position:relative; height:200px;">
                    <canvas id="validate48hChart"></canvas>
                </div>
                <div class="d-flex justify-content-around mt-3 text-center">
                    <div>
                        <h5 class="mb-0" style="color:#0ca30c;">{{ $countValidatedWithin48h }}</h5>
                        <small class="text-muted">Validated &le; 48h</small>
                    </div>
                    <div>
                        <h5 class="mb-0" style="color:#d03b3b;">{{ $countValidatedAfter48h }}</h5>
                        <small class="text-muted">Validated &gt; 48h</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-12 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Member Registrations
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Jumlah pendaftar baru per periode — untuk reporting cepat ke management."></i>
                </h4>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-primary" id="reg-toggle-weekly">Weekly</button>
                    <button type="button" class="btn btn-outline-primary" id="reg-toggle-monthly">Monthly</button>
                    <button type="button" class="btn btn-outline-primary" id="reg-toggle-yearly">Yearly</button>
                </div>
            </div>
            <div class="card-body">
                <div style="position:relative; height:200px;">
                    <canvas id="registrationsChart" style="cursor:pointer;"></canvas>
                </div>
                <small class="text-muted d-block text-center mt-2">
                    <i class="fas fa-mouse-pointer mr-1"></i>Klik salah satu bar untuk lihat breakdown source periode itu
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="mb-0">
                    Verification Status
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Breakdown seluruh member berdasarkan status verifikasi saat ini."></i>
                </h4>
            </div>
            <div class="card-body">
                <div style="position:relative; height:200px;">
                    <canvas id="statusBreakdownChart"></canvas>
                </div>
                <div class="d-flex flex-wrap justify-content-around mt-3 text-center" style="gap:6px;">
                    <div>
                        <h5 class="mb-0" style="color:#0ca30c;">{{ $countActiveMember }}</h5>
                        <small class="text-muted">Active</small>
                    </div>
                    <div>
                        <h5 class="mb-0" style="color:#fab219;">{{ $countPendingMember }}</h5>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div>
                        <h5 class="mb-0" style="color:#d03b3b;">{{ $countDeclined }}</h5>
                        <small class="text-muted">Declined</small>
                    </div>
                    <div>
                        <h5 class="mb-0" style="color:#898781;">{{ $countDeactivated }}</h5>
                        <small class="text-muted">Deactivated</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /charts row --}}

{{-- Member composition (Internal Operating Guideline: position tier & company
     category dilaporkan terpisah) — hanya member Active. --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Job Tier
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Komposisi member Active (hanya company yang sudah verified) berdasarkan position tier dari job title (otomatis atau diset manual). Klik bar, tier atau role untuk lihat detail & daftar member."></i>
                </h4>
                <span class="badge badge-light">{{ number_format($memberComposition['total'], 0, ',', '.') }} Active · company verified</span>
            </div>
            <div class="card-body">
                @php
                    $jobTierPct = function ($n) use ($memberComposition) {
                        return $memberComposition['total'] ? number_format($n * 100 / $memberComposition['total'], 1, ',', '.') : '0';
                    };
                @endphp
                {{-- Chart kiri, detail kanan (grid 2 kolom: T1 | T2 / T3 | T4) supaya tidak memanjang ke bawah. --}}
                <div class="row align-items-center">
                    <div class="col-lg-5 mb-3 mb-lg-0">
                        <div style="position:relative; height:280px;">
                            <canvas id="jobTierChart" role="img" style="cursor:pointer;"
                                aria-label="Jumlah member Active per job tier"></canvas>
                        </div>
                        <ul class="list-unstyled small mb-0 mt-3">
                            @foreach ($memberComposition['tiers'] as $tierRow)
                                <li class="d-flex align-items-start mb-1">
                                    <span class="mr-2 mt-1 flex-shrink-0" style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $tierRow['color'] }};"></span>
                                    <span><strong>{{ $tierRow['label'] }}</strong> <span class="text-muted">{{ $tierRow['roles'] }}</span></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-lg-7">
                {{-- Detail per tier + rincian role sesuai definisi tier (App\Support\JobTierRole);
                     klik → modal Job Tier detail. --}}
                <div class="job-tier-grid" id="job-tier-list">
                    @foreach ($memberComposition['tiers'] as $tierRow)
                        <div class="cc-tier-group">
                            <button type="button" class="company-category-item cc-tier" data-tier="{{ $tierRow['tier'] }}"
                                title="{{ $tierRow['label'] }} — klik untuk detail">
                                <span class="cc-dot" style="background:{{ $tierRow['color'] }};"></span>
                                <span class="cc-name">{{ $tierRow['label'] }} <span class="text-muted font-weight-normal">{{ $tierRow['action'] }}</span></span>
                                <span class="cc-count">{{ number_format($tierRow['count'], 0, ',', '.') }}</span>
                                <span class="cc-pct">{{ $jobTierPct($tierRow['count']) }}%</span>
                            </button>
                            @foreach ($tierRow['roleCounts'] as $role => $roleCount)
                                <button type="button" class="company-category-item cc-sub" data-tier="{{ $tierRow['tier'] }}"
                                    data-role="{{ $role }}" title="{{ $role }} — klik untuk detail">
                                    <span class="cc-name">{{ $role }}</span>
                                    <span class="cc-count">{{ number_format($roleCount, 0, ',', '.') }}</span>
                                    <span class="cc-pct">{{ $jobTierPct($roleCount) }}%</span>
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Jarang dipakai → disembunyikan default; chart baru digambar saat pertama dibuka. --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Company Category × Job Tier
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Jumlah member Active (hanya company yang sudah verified) per company category, dipecah per job tier. Angka di ujung bar = total kategori."></i>
                </h4>
                <button type="button" class="btn btn-sm btn-outline-primary" id="company-category-tier-toggle"
                    data-toggle="collapse" data-target="#company-category-tier-collapse"
                    aria-expanded="false" aria-controls="company-category-tier-collapse">
                    <i class="fas fa-eye mr-1"></i><span>Show</span>
                </button>
            </div>
            <div class="collapse" id="company-category-tier-collapse">
                <div class="card-body">
                    <div style="position:relative; height:{{ max(260, count($memberComposition['categories']) * 24 + 60) }}px;">
                        <canvas id="companyCategoryTierChart" role="img"
                            aria-label="Jumlah member Active per company category, dipecah per job tier"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /member composition row --}}

{{-- Company Category Tier dari Company Database — hitungan company unik, bukan member,
     dikelompokkan ke 5 grup (App\Support\CompanyCategoryTier). Klik slice / grup / kategori
     → modal category detail + subcategory + daftar company. --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Company Category Tier
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Jumlah company unik yang sudah verified dari Company Database (dedup per nama company, sama seperti tab Verified di halaman Company Database) — bukan jumlah member — dikelompokkan ke 5 grup kategori seperti di event detail IndonesiaMiner. Klik grup atau kategori untuk lihat category detail, subcategory & daftar company."></i>
                </h4>
                <span class="badge badge-light" id="company-category-total">—</span>
            </div>
            <div class="card-body">
                <div id="company-category-loading" class="text-center text-muted py-5">
                    <span class="spinner-border spinner-border-sm mr-2"></span>Memuat...
                </div>
                <div id="company-category-content" class="row align-items-center" style="display:none;">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div style="position:relative; height:460px;">
                            <canvas id="companyCategoryChart" role="img" style="cursor:pointer;"
                                aria-label="Jumlah company per company category tier"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="company-category-list" id="company-category-list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /company category row --}}

{{-- Members by Source (Members Relation SOP §8) --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="mb-0">
                    Members by Source
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Breakdown member, leads, dan konversi per channel pendaftaran. Mengikuti filter yang lagi aktif di tabel Members."></i>
                </h4>
                @php
                    $activeSourceFilters = array_filter(request()->only(['date_from', 'date_to', 'month', 'year', 'source', 'status_member', 'filter']));
                @endphp
                @if (count($activeSourceFilters) > 0)
                    <span class="badge badge-info">
                        <i class="fas fa-filter mr-1"></i>Filtered
                    </span>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:12.5px;">
                        <thead class="thead-light">
                            <tr>
                                <th>Source</th>
                                <th class="text-right">Total Members</th>
                                <th class="text-right">Total Leads</th>
                                <th class="text-right">Win</th>
                                <th class="text-right">Loss</th>
                                <th class="text-right">Conversion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sourceBreakdown as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="text-right">{{ $row['members'] }}</td>
                                    <td class="text-right">{{ $row['leads'] }}</td>
                                    <td class="text-right text-success">{{ $row['win'] }}</td>
                                    <td class="text-right text-secondary">{{ $row['loss'] }}</td>
                                    <td class="text-right">{{ $row['conversion_rate'] !== null ? $row['conversion_rate'] . '%' : '—' }}</td>
                                </tr>
                                @foreach ($row['children'] as $child)
                                    <tr class="text-muted" style="background:#fafbfc;">
                                        <td class="pl-4" title="{{ $child['code'] }}">↳ {{ $child['label'] }}</td>
                                        <td class="text-right">{{ $child['members'] }}</td>
                                        <td class="text-right">{{ $child['leads'] }}</td>
                                        <td class="text-right">{{ $child['win'] }}</td>
                                        <td class="text-right">{{ $child['loss'] }}</td>
                                        <td class="text-right">{{ $child['conversion_rate'] !== null ? $child['conversion_rate'] . '%' : '—' }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /source breakdown row --}}
</div>
