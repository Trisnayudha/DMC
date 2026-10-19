{{-- Stats cards + self-edit banner --}}

{{-- Row 1: Business KPI --}}
<div class="row">

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?status_member=active') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-users"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Total Members</h4></div>
                    <div class="card-body">{{ $countActiveMember }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?filter=company_verified') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-info"><i class="fas fa-building"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Companies Verified
                            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                                title="Jumlah company unik yang terverifikasi (dedup per nama company, sama seperti angka di halaman Company Database)."
                                data-toggle="tooltip"></i>
                        </h4>
                    </div>
                    <div class="card-body">{{ $countCompaniesVerified }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?status_member=declined') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger"><i class="fas fa-ban"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Disqualified</h4></div>
                    <div class="card-body">{{ $countDeclined }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?filter=prospecting') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="fas fa-star"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Prospecting
                            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                                title="Member yang bersedia menerima penawaran sponsorship (Open to Sponsorship)."
                                data-toggle="tooltip"></i>
                        </h4>
                    </div>
                    <div class="card-body">{{ $countProspecting }}</div>
                </div>
            </div>
        </a>
    </div>

</div>{{-- /row 1 --}}

{{-- Row 2: Operational --}}
<div class="row">

    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?status_member=pending') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="fas fa-user-clock"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Pending Verification</h4></div>
                    <div class="card-body">{{ $countPendingMember }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?filter=this_month') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary"><i class="fas fa-user-plus"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>New Applicants This Month</h4></div>
                    <div class="card-body">{{ $countNewThisMonth }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-info"><i class="fab fa-mailchimp"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Mailchimp Contacts</h4></div>
                <div class="card-body" id="mc-contact-count">
                    <span class="spinner-border spinner-border-sm text-info" role="status"></span>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /row 2 --}}

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

@if ($countSelfEdited > 0)
    <div class="alert alert-warning alert-dismissible show fade d-flex align-items-center py-2 mb-3" style="gap:10px;">
        <i class="fas fa-exclamation-triangle fa-lg"></i>
        <div class="flex-grow-1">
            <strong>{{ $countSelfEdited }} user</strong> telah mengubah data mereka sendiri melalui apps/website.
            <a href="{{ url('admin/users?filter=self_edited') }}" class="font-weight-bold ml-2">Lihat daftar →</a>
        </div>
        <button type="button" class="close ml-2" data-dismiss="alert"><span>×</span></button>
    </div>
@endif

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
                        title="Komposisi member Active berdasarkan position tier dari job title (otomatis atau diset manual). Klik bar, tier atau role untuk lihat detail & daftar member."></i>
                </h4>
                <span class="badge badge-light">{{ number_format($memberComposition['total'], 0, ',', '.') }} Active</span>
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
                        title="Jumlah member Active per company category, dipecah per job tier. Angka di ujung bar = total kategori."></i>
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
                        title="Jumlah company unik dari Company Database (dedup per nama company, sama seperti halaman Company Database) — bukan jumlah member — dikelompokkan ke 5 grup kategori seperti di event detail IndonesiaMiner. Klik grup atau kategori untuk lihat category detail, subcategory & daftar company."></i>
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



<style>
    /* Source Breakdown modal — same compact/modern language as the Members
       DMC and Follow-Up tables (sticky header, dense rows, hover, truncate). */
    .reg-stat-row {
        display: flex;
        gap: 12px;
    }
    .reg-stat-card {
        flex: 1;
        border-radius: 10px;
        padding: 14px 16px;
        text-align: center;
    }
    .reg-stat-total { background: #eef2ff; }
    .reg-stat-leads { background: #fff6e6; }
    .reg-stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1.1;
        color: #2a2f45;
    }
    .reg-stat-leads .reg-stat-value { color: #b3720c; }
    .reg-stat-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        margin-top: 4px;
    }
    .reg-stat-sub {
        font-size: 11px;
        color: #6c757d;
        margin-top: 6px;
    }
    .reg-stat-sub .viable { color: #0ca30c; font-weight: 600; }
    .reg-stat-sub .dead { color: #d03b3b; font-weight: 600; }

    .reg-section-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #adb5bd;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .reg-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .reg-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: #f4f5f9;
        font-size: 12px;
        font-weight: 600;
        color: #3c4257;
    }
    .reg-chip i, .reg-chip .reg-chip-dot { font-size: 10px; }
    .reg-chip .reg-chip-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .reg-chip .reg-chip-pct { color: #8a8fa3; font-weight: 500; }

    .reg-status-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        padding: .2em .5em;
        border-radius: 4px;
        color: #fff;
    }

    .reg-search-wrap { position: relative; }
    .reg-search-wrap i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        color: #adb5bd;
    }
    .reg-search-wrap input {
        width: 240px;
        padding-left: 28px;
    }

    .reg-breakdown-table-wrap {
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #eef0f4;
        border-radius: 8px;
    }
    .reg-breakdown-table-wrap table {
        border-collapse: separate !important;
        font-size: 12px;
        margin-bottom: 0;
    }
    .reg-breakdown-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fc;
        border-top: none !important;
        border-bottom: 2px solid #e3e6f0 !important;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        padding: .5rem .6rem;
    }
    .reg-breakdown-table-wrap tbody td {
        border-top: none !important;
        border-bottom: 1px solid #eef0f4 !important;
        vertical-align: middle;
        padding: .4rem .6rem;
    }
    .reg-breakdown-table-wrap tbody tr:hover { background-color: #f7f9fc; }
    .reg-breakdown-table-wrap .cell-truncate {
        display: inline-block;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Company Category: daftar semua kategori di samping pie — tiap baris bisa diklik. */
    .company-category-list {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0;
    }
    .company-category-item {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 5px 8px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        font-size: 12px;
        color: #3c4257;
        text-align: left;
        cursor: pointer;
    }
    .company-category-item:hover,
    .company-category-item:focus { background: #f4f5f9; outline: none; }
    .company-category-item .cc-dot {
        flex-shrink: 0;
        width: 10px;
        height: 10px;
        border-radius: 2px;
    }
    .company-category-item .cc-name {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .company-category-item .cc-count { font-weight: 700; }
    .cc-tier-group { margin-bottom: 6px; }
    .company-category-item.cc-tier .cc-name { font-weight: 700; white-space: normal; }
    .company-category-item.cc-sub { padding: 1px 8px 1px 26px; font-size: 11.5px; color: #6c757d; }
    .company-category-item.cc-sub .cc-count { font-weight: 600; }
    .company-category-item .cc-pct { color: #8a8fa3; width: 44px; text-align: right; }
    .cc-filter-chip { cursor: pointer; border: 1px solid transparent; }

    /* Job Tier: detail tier dalam grid 2 kolom di samping chart. */
    .job-tier-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 4px 24px;
    }
    .job-tier-grid .cc-tier-group { margin-bottom: 10px; }
    @media (max-width: 767.98px) {
        .job-tier-grid { grid-template-columns: minmax(0, 1fr); }
    }
    .cc-filter-chip.active { border-color: #2a78d6; background: #eaf2fd; color: #1c5cab; }

    @media (max-width: 575.98px) {
        .company-category-list { grid-template-columns: minmax(0, 1fr); }
        .reg-stat-row { flex-direction: column; }
        .reg-search-wrap input { width: 100%; }
    }
</style>

@push('bottom')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('validate48hChart'), {
            type: 'doughnut',
            data: {
                labels: ['Validated ≤ 48h', 'Validated > 48h'],
                datasets: [{
                    data: [{{ (int) $countValidatedWithin48h }}, {{ (int) $countValidatedAfter48h }}],
                    backgroundColor: ['#0ca30c', '#d03b3b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        (function() {
            var periods = {
                weekly:  { labels: @json($registrationsWeeklyLabels),  counts: @json($registrationsWeeklyCounts),  ranges: @json($registrationsWeeklyRanges) },
                monthly: { labels: @json($registrationsMonthlyLabels), counts: @json($registrationsMonthlyCounts), ranges: @json($registrationsMonthlyRanges) },
                yearly:  { labels: @json($registrationsYearlyLabels),  counts: @json($registrationsYearlyCounts),  ranges: @json($registrationsYearlyRanges) },
            };
            var toggleButtons = {
                weekly:  document.getElementById('reg-toggle-weekly'),
                monthly: document.getElementById('reg-toggle-monthly'),
                yearly:  document.getElementById('reg-toggle-yearly'),
            };
            var currentPeriodKey = 'weekly';

            // Safety net for the stuck-backdrop/unscrollable-page case: once no
            // modal is left open, force-clear anything Bootstrap might have
            // left behind (a duplicate .modal-backdrop, or the body's
            // scroll-lock state) instead of trusting a single hide() call to
            // have fully cleaned up.
            $('#registrationBreakdownModal').on('hidden.bs.modal', function() {
                if ($('.modal.show').length === 0) {
                    $('body').removeClass('modal-open').css({ overflow: '', 'padding-right': '' });
                    $('.modal-backdrop').remove();
                }
            });

            var registrationsChart = new Chart(document.getElementById('registrationsChart'), {
                type: 'bar',
                data: {
                    labels: periods.weekly.labels,
                    datasets: [{
                        label: 'New Registrations',
                        data: periods.weekly.counts,
                        backgroundColor: '#2a78d6',
                        borderRadius: 4,
                        maxBarThickness: 28,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    onClick: function(evt) {
                        var points = registrationsChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, false);
                        if (!points.length) return;
                        var index = points[0]._index !== undefined ? points[0]._index : points[0].index;
                        var range = periods[currentPeriodKey].ranges[index];
                        var label = periods[currentPeriodKey].labels[index];
                        if (range) showRegistrationBreakdown(label, range.from, range.to);
                    }
                }
            });

            function selectPeriod(key) {
                currentPeriodKey = key;
                Object.keys(toggleButtons).forEach(function(k) {
                    toggleButtons[k].classList.toggle('btn-primary', k === key);
                    toggleButtons[k].classList.toggle('btn-outline-primary', k !== key);
                });
                registrationsChart.data.labels = periods[key].labels;
                registrationsChart.data.datasets[0].data = periods[key].counts;
                registrationsChart.update();
            }

            Object.keys(toggleButtons).forEach(function(key) {
                toggleButtons[key].addEventListener('click', function() { selectPeriod(key); });
            });

            var regBreakdownMembers = []; // current period's member list, filtered client-side (no re-fetch)
            var regBreakdownTotal = 0;

            function escapeHtml(s) {
                return $('<div>').text(s || '').html();
            }

            // Same 4 buckets/colors as the "Verification Status" doughnut on
            // this same page — a status not explicitly active/declined/
            // deactivated is "waiting" (never verified one way or the other).
            var statusMeta = {
                active:      { label: 'Verified',    color: '#0ca30c' },
                waiting:     { label: 'Waiting',      color: '#fab219' },
                declined:    { label: 'Declined',     color: '#d03b3b' },
                deactivated: { label: 'Deactivated',  color: '#898781' },
            };
            function statusBucket(status) {
                status = (status || '').toLowerCase();
                return statusMeta[status] ? status : 'waiting';
            }

            function renderRegBreakdownTable() {
                var q = $('#reg-breakdown-search').val().trim().toLowerCase();
                var leadsOnly = $('#reg-breakdown-leads-only').is(':checked');
                var statusFilter = $('#reg-breakdown-status-filter').val();

                var filtered = regBreakdownMembers.filter(function(m) {
                    if (leadsOnly && !m.is_lead) return false;
                    if (statusFilter && statusBucket(m.status_member) !== statusFilter) return false;
                    if (!q) return true;
                    var haystack = [m.name, m.email, m.company_name, m.source].join(' ').toLowerCase();
                    return haystack.indexOf(q) !== -1;
                });

                var $tbody = $('#reg-breakdown-members-tbody').empty();

                if (filtered.length === 0) {
                    $('#reg-breakdown-no-match').show();
                } else {
                    $('#reg-breakdown-no-match').hide();
                    $.each(filtered, function(i, m) {
                        var leadBadge = m.is_lead
                            ? '<span class="badge badge-primary" title="Potential lead — Explore Marketing"><i class="fas fa-bullseye"></i></span>'
                            : '<span class="text-muted">-</span>';
                        var bucket = statusBucket(m.status_member);
                        var statusBadge = '<span class="reg-status-badge" style="background:' + statusMeta[bucket].color + ';">' + statusMeta[bucket].label + '</span>';
                        $tbody.append(
                            '<tr>' +
                                '<td><span class="cell-truncate font-weight-bold" title="' + escapeHtml(m.name) + '">' + escapeHtml(m.name || '-') + '</span><br>' +
                                    '<small class="cell-truncate text-muted" title="' + escapeHtml(m.email) + '">' + escapeHtml(m.email) + '</small></td>' +
                                '<td><span class="cell-truncate" title="' + escapeHtml(m.company_name) + '">' + escapeHtml(m.company_name || '-') + '</span></td>' +
                                '<td>' + escapeHtml(m.source) + '</td>' +
                                '<td>' + statusBadge + '</td>' +
                                '<td class="text-center">' + leadBadge + '</td>' +
                                '<td class="text-nowrap"><small>' + (m.created_at || '-') + '</small></td>' +
                            '</tr>'
                        );
                    });
                }

                var note = filtered.length !== regBreakdownMembers.length
                    ? filtered.length + ' dari ' + regBreakdownMembers.length + ' ditampilkan'
                    : (regBreakdownMembers.length + ' member' + (regBreakdownMembers.length !== regBreakdownTotal ? ' (dari ' + regBreakdownTotal + ' total, terbaru dulu)' : ''));
                $('#reg-breakdown-members-note').text(note);
            }

            $(document).on('input', '#reg-breakdown-search', renderRegBreakdownTable);
            $(document).on('change', '#reg-breakdown-leads-only', renderRegBreakdownTable);
            $(document).on('change', '#reg-breakdown-status-filter', renderRegBreakdownTable);

            function showRegistrationBreakdown(label, dateFrom, dateTo) {
                $('#reg-breakdown-period').text(label);
                $('#reg-breakdown-loading').show();
                $('#reg-breakdown-empty').hide();
                $('#reg-breakdown-content').hide();
                $('#reg-breakdown-chips').empty();
                $('#reg-breakdown-status-chips').empty();
                $('#reg-breakdown-leads-split').text('');
                $('#reg-breakdown-members-tbody').empty();
                $('#reg-breakdown-members-note').text('');
                $('#reg-breakdown-search').val('');
                $('#reg-breakdown-leads-only').prop('checked', false);
                $('#reg-breakdown-status-filter').val('');
                regBreakdownMembers = [];
                regBreakdownTotal = 0;

                // Guard against re-triggering show() on an already-open modal —
                // the chart stays clickable underneath while it's open/mid-
                // transition, so a second bar click here is easy to do by
                // accident. Bootstrap 4 doesn't dedupe that: it stacks a second
                // .modal-backdrop, and closing only tears down one of them,
                // leaving the other's opacity + the page's scroll-lock stuck
                // behind. If it's already open, just refresh the content in
                // place instead of calling show() again.
                if (!$('#registrationBreakdownModal').hasClass('show')) {
                    $('#registrationBreakdownModal').modal('show');
                }

                $.ajax({
                    url: '{{ route('users.registration_source_breakdown') }}',
                    method: 'GET',
                    data: { date_from: dateFrom, date_to: dateTo },
                    dataType: 'json'
                }).done(function(res) {
                    $('#reg-breakdown-loading').hide();

                    if (!res || !res.success || !res.breakdown || res.breakdown.length === 0) {
                        $('#reg-breakdown-empty').show();
                        return;
                    }

                    regBreakdownMembers = res.members || [];
                    regBreakdownTotal = res.members_total || regBreakdownMembers.length;

                    $('#reg-breakdown-total').text(res.total);
                    $('#reg-breakdown-leads').text(res.leads_count || 0);
                    var leadsPct = res.total > 0 ? Math.round((res.leads_count / res.total) * 100) : 0;
                    $('#reg-breakdown-leads-pct').text('(' + leadsPct + '%)');

                    // Still viable (active/waiting) vs already dead (declined) —
                    // a declined applicant can't realistically be pursued as a
                    // sponsorship lead anymore, so this splits the number above
                    // instead of leaving "Potential Leads" looking bigger than
                    // it actually, practically is. Wording kept report-ready
                    // (standard CRM terms), not casual shorthand.
                    if ((res.leads_count || 0) > 0) {
                        $('#reg-breakdown-leads-split').html(
                            '<span class="viable">' + (res.leads_viable || 0) + ' Active Prospects</span>' +
                            (res.leads_dead ? ' &nbsp;·&nbsp; <span class="dead">' + res.leads_dead + ' Disqualified</span>' : '')
                        );
                    }

                    $.each(res.breakdown, function(i, row) {
                        var pct = res.total > 0 ? Math.round((row.total / res.total) * 100) : 0;
                        $('#reg-breakdown-chips').append(
                            '<span class="reg-chip">' +
                                '<i class="' + row.icon + '" style="color:' + row.color + ';"></i>' +
                                row.label + ' <strong>' + row.total + '</strong> <span class="reg-chip-pct">' + pct + '%</span>' +
                            '</span>'
                        );
                    });

                    var statusOrder = ['active', 'waiting', 'declined', 'deactivated'];
                    $.each(statusOrder, function(i, key) {
                        var count = (res.status && res.status[key === 'active' ? 'verified' : key]) || 0;
                        if (count === 0) return;
                        var pct = res.total > 0 ? Math.round((count / res.total) * 100) : 0;
                        $('#reg-breakdown-status-chips').append(
                            '<span class="reg-chip">' +
                                '<span class="reg-chip-dot" style="background:' + statusMeta[key].color + ';"></span>' +
                                statusMeta[key].label + ' <strong>' + count + '</strong> <span class="reg-chip-pct">' + pct + '%</span>' +
                            '</span>'
                        );
                    });

                    renderRegBreakdownTable();
                    $('#reg-breakdown-content').show();
                }).fail(function() {
                    $('#reg-breakdown-loading').hide();
                    $('#reg-breakdown-empty').text('Gagal memuat breakdown.').show();
                });
            }
        })();

        // Member composition: Job Tier & Company Category × Job Tier.
        (function() {
            var composition = @json($memberComposition);
            var tiers = composition.tiers;
            var pct = function(n, of) { return (of ? Math.round(n * 1000 / of) / 10 : 0).toLocaleString('id-ID'); };
            var fmt = function(n) { return Number(n).toLocaleString('id-ID'); };

            // Satu angka di ujung tiap bar horizontal (total segmen yang sedang
            // tampil) — bukan angka di setiap segmen. Ikut berubah kalau tier
            // disembunyikan lewat legend.
            var endLabels = {
                id: 'endLabels',
                afterDatasetsDraw: function(chart, args, opts) {
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.font = '600 11px ' + Chart.defaults.font.family;
                    ctx.fillStyle = '#495057';
                    ctx.textBaseline = 'middle';
                    chart.data.labels.forEach(function(label, i) {
                        var sum = 0, x = chart.scales.x.getPixelForValue(0), y = null;
                        chart.data.datasets.forEach(function(ds, d) {
                            var meta = chart.getDatasetMeta(d);
                            if (meta.hidden || !meta.data[i]) return;
                            sum += ds.data[i];
                            x = Math.max(x, meta.data[i].x);
                            y = meta.data[i].y;
                        });
                        if (y !== null && sum > 0) ctx.fillText(opts.format(sum), x + 6, y);
                    });
                    ctx.restore();
                }
            };

            var axisStyle = {
                grid: { color: '#eef0f3', drawTicks: false },
                border: { display: false },
                ticks: { color: '#6c757d', font: { size: 10 }, precision: 0, callback: function(v) { return fmt(v); } }
            };

            new Chart(document.getElementById('jobTierChart'), {
                type: 'bar',
                data: {
                    labels: tiers.map(function(t) { return t.label; }),
                    datasets: [{
                        label: 'Member Active',
                        data: tiers.map(function(t) { return t.count; }),
                        backgroundColor: tiers.map(function(t) { return t.color; }),
                        borderRadius: 4,
                        maxBarThickness: 26,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { right: 45 } },
                    scales: {
                        x: axisStyle,
                        y: { grid: { display: false }, border: { display: false }, ticks: { color: '#343a40', font: { size: 11, weight: '600' } } }
                    },
                    plugins: {
                        legend: { display: false },
                        endLabels: { format: function(n) { return pct(n, composition.total) + '%'; } },
                        tooltip: {
                            callbacks: {
                                title: function(items) { var t = tiers[items[0].dataIndex]; return t.label + ' — ' + t.roles; },
                                label: function(item) { return ' ' + fmt(item.raw) + ' member (' + pct(item.raw, composition.total) + '%)'; },
                                footer: function() { return 'Klik untuk detail'; }
                            }
                        }
                    },
                    onClick: function(evt, elements) {
                        if (elements.length) window.openJobTierDetail({ tier: tiers[elements[0].index].tier });
                    }
                },
                plugins: [endLabels]
            });

            $('#job-tier-list').on('click', '.company-category-item', function() {
                window.openJobTierDetail({ tier: Number($(this).attr('data-tier')), role: $(this).attr('data-role') || null });
            });

            var categories = composition.categories;
            var categoryTotals = categories.map(function(c, i) {
                return tiers.reduce(function(sum, t) { return sum + t.byCategory[i]; }, 0);
            });

            // Card ini collapse by default — chart digambar saat pertama dibuka supaya
            // ukurannya benar (canvas di elemen tersembunyi lebarnya 0).
            var categoryTierChartDrawn = false;
            $('#company-category-tier-collapse')
                .on('shown.bs.collapse', function() {
                    $('#company-category-tier-toggle span').text('Hide');
                    $('#company-category-tier-toggle i').attr('class', 'fas fa-eye-slash mr-1');
                    if (!categoryTierChartDrawn) {
                        categoryTierChartDrawn = true;
                        drawCategoryTierChart();
                    }
                })
                .on('hidden.bs.collapse', function() {
                    $('#company-category-tier-toggle span').text('Show');
                    $('#company-category-tier-toggle i').attr('class', 'fas fa-eye mr-1');
                });

            function drawCategoryTierChart() {
                new Chart(document.getElementById('companyCategoryTierChart'), {
                    type: 'bar',
                    data: {
                        labels: categories,
                        datasets: tiers.map(function(t) {
                            return {
                                label: t.label,
                                data: t.byCategory,
                                backgroundColor: t.color,
                                // 2px celah putih antar segmen supaya tier bersebelahan tetap terpisah.
                                borderColor: '#ffffff',
                                borderWidth: { right: 2 },
                                borderSkipped: false,
                                maxBarThickness: 16,
                            };
                        })
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { right: 40 } },
                        interaction: { mode: 'index', axis: 'y', intersect: false },
                        scales: {
                            x: Object.assign({ stacked: true }, axisStyle),
                            y: {
                                stacked: true,
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    color: '#343a40',
                                    font: { size: 11 },
                                    callback: function(value) {
                                        var label = this.getLabelForValue(value);
                                        return label.length > 32 ? label.slice(0, 31) + '…' : label;
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: { position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, font: { size: 11 } } },
                            endLabels: { format: fmt },
                            tooltip: {
                                callbacks: {
                                    label: function(item) {
                                        var t = tiers[item.datasetIndex];
                                        return ' ' + t.label + ': ' + fmt(item.raw) + ' (' + pct(item.raw, categoryTotals[item.dataIndex]) + '%)';
                                    },
                                    footer: function(items) {
                                        return 'Total: ' + fmt(categoryTotals[items[0].dataIndex]) + ' member';
                                    }
                                }
                            }
                        }
                    },
                    plugins: [endLabels]
                });
            }
        })();

        // Company Category Tier (Company Database) — pie 5 grup besar (pola sama dengan
        // event detail IndonesiaMiner); daftar samping = grup + kategori di dalamnya.
        (function() {
            // Warna brand desain "Member Compositions" (hitam, merah, abu, marun) + tan
            // senada untuk grup ke-5. Urutan dipilih supaya slice bersebelahan (termasuk
            // slice terakhir ↔ pertama) tidak sama-sama gelap.
            var palette = ['#1d1d1f', '#8e0f15', '#4b5058', '#3c1016', '#a8896c'];
            var fmt = function(n) { return Number(n).toLocaleString('id-ID'); };
            var pct = function(n, of) { return (of ? Math.round(n * 1000 / of) / 10 : 0).toLocaleString('id-ID'); };
            var esc = function(s) { return $('<span>').text(s).html(); };

            // Label persentase di tengah tiap slice; teks putih/gelap dipilih dari
            // terang-gelapnya warna slice supaya tetap terbaca.
            var sliceLabels = {
                id: 'sliceLabels',
                afterDatasetsDraw: function(chart) {
                    var ds = chart.data.datasets[0];
                    var sum = ds.data.reduce(function(s, v) { return s + v; }, 0);
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.font = '700 12px ' + Chart.defaults.font.family;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    chart.getDatasetMeta(0).data.forEach(function(arc, i) {
                        if (!sum || ds.data[i] / sum < 0.03) return; // slice terlalu kecil untuk label
                        var hex = ds.backgroundColor[i].replace('#', '');
                        var r = parseInt(hex.substr(0, 2), 16), g = parseInt(hex.substr(2, 2), 16), b = parseInt(hex.substr(4, 2), 16);
                        ctx.fillStyle = (0.299 * r + 0.587 * g + 0.114 * b) > 150 ? '#1f2328' : '#ffffff';
                        var pos = arc.tooltipPosition();
                        ctx.fillText(pct(ds.data[i], sum) + '%', pos.x, pos.y);
                    });
                    ctx.restore();
                }
            };

            // Nama grup di luar pie dengan garis penunjuk (callout) — supaya chart
            // bisa langsung di-screenshot untuk management tanpa daftar samping.
            // Label di sisi yang sama digeser vertikal kalau bertumpuk.
            var calloutLabels = {
                id: 'calloutLabels',
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;
                    var ds = chart.data.datasets[0];
                    var sum = ds.data.reduce(function(s, v) { return s + v; }, 0);
                    var maxWidth = 165, lineHeight = 13, elbow = 14, run = 10;
                    var font = '11px ' + Chart.defaults.font.family;
                    var boldFont = '700 11px ' + Chart.defaults.font.family;

                    var wrap = function(text) {
                        ctx.font = boldFont;
                        var words = text.split(' '), lines = [], line = '';
                        words.forEach(function(w) {
                            var test = line ? line + ' ' + w : w;
                            if (ctx.measureText(test).width > maxWidth && line) { lines.push(line); line = w; }
                            else { line = test; }
                        });
                        if (line) lines.push(line);
                        return lines;
                    };

                    var labels = chart.getDatasetMeta(0).data.map(function(arc, i) {
                        var mid = (arc.startAngle + arc.endAngle) / 2;
                        var cos = Math.cos(mid), sin = Math.sin(mid);
                        var lines = wrap(chart.data.labels[i]);
                        var count = pct(ds.data[i], sum) + '%';
                        return {
                            right: cos >= 0,
                            color: ds.backgroundColor[i],
                            x0: arc.x + cos * arc.outerRadius, y0: arc.y + sin * arc.outerRadius,
                            x1: arc.x + cos * (arc.outerRadius + elbow), y1: arc.y + sin * (arc.outerRadius + elbow),
                            lines: lines, count: count,
                            height: (lines.length + 1) * lineHeight,
                        };
                    });

                    // Geser label per sisi supaya tidak saling menimpa.
                    [true, false].forEach(function(side) {
                        var group = labels.filter(function(l) { return l.right === side; })
                            .sort(function(a, b) { return a.y1 - b.y1; });
                        var bottom = -Infinity;
                        group.forEach(function(l) {
                            l.ty = Math.max(l.y1, bottom + l.height / 2 + 6);
                            bottom = l.ty + l.height / 2;
                        });
                    });

                    ctx.save();
                    labels.forEach(function(l) {
                        var x2 = l.x1 + (l.right ? run : -run);
                        ctx.strokeStyle = l.color;
                        ctx.lineWidth = 1.5;
                        ctx.beginPath();
                        ctx.moveTo(l.x0, l.y0);
                        ctx.lineTo(l.x1, l.ty);
                        ctx.lineTo(x2, l.ty);
                        ctx.stroke();

                        var tx = x2 + (l.right ? 4 : -4);
                        var top = l.ty - l.height / 2 + lineHeight / 2;
                        ctx.textAlign = l.right ? 'left' : 'right';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = '#343a40';
                        ctx.font = boldFont;
                        l.lines.forEach(function(line, j) { ctx.fillText(line, tx, top + j * lineHeight); });
                        ctx.fillStyle = '#6c757d';
                        ctx.font = font;
                        ctx.fillText(l.count, tx, top + l.lines.length * lineHeight);
                    });
                    ctx.restore();
                }
            };

            $.getJSON(@json(route('admin.company_database.category_breakdown')))
                .done(function(res) {
                    var tiers = res.tiers;
                    var total = res.total;

                    $('#company-category-total').text(fmt(total) + ' company');
                    $('#company-category-list').html(tiers.map(function(t, i) {
                        var categories = t.categories.map(function(c) {
                            return '<button type="button" class="company-category-item cc-sub" data-category="' + esc(c.name) + '"'
                                + ' title="' + esc(c.name) + ' — klik untuk detail">'
                                + '<span class="cc-name">' + esc(c.name)
                                + (c.has_subcategories ? ' <i class="fas fa-sitemap text-muted ml-1" title="Punya subcategory"></i>' : '') + '</span>'
                                + '<span class="cc-count">' + fmt(c.count) + '</span>'
                                + '<span class="cc-pct">' + pct(c.count, total) + '%</span>'
                                + '</button>';
                        }).join('');
                        return '<div class="cc-tier-group">'
                            + '<button type="button" class="company-category-item cc-tier" data-tier="' + esc(t.name) + '"'
                            + ' title="' + esc(t.name) + ' — klik untuk detail">'
                            + '<span class="cc-dot" style="background:' + palette[i] + ';"></span>'
                            + '<span class="cc-name">' + esc(t.name) + '</span>'
                            + '<span class="cc-count">' + fmt(t.count) + '</span>'
                            + '<span class="cc-pct">' + pct(t.count, total) + '%</span>'
                            + '</button>' + categories + '</div>';
                    }).join(''));

                    $('#company-category-loading').hide();
                    $('#company-category-content').show();

                    new Chart(document.getElementById('companyCategoryChart'), {
                        type: 'pie',
                        data: {
                            labels: tiers.map(function(t) { return t.name; }),
                            datasets: [{
                                data: tiers.map(function(t) { return t.count; }),
                                backgroundColor: palette.slice(0, tiers.length),
                                borderColor: '#ffffff',
                                borderWidth: 2,
                                hoverOffset: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            // Ruang kiri-kanan untuk label callout nama grup.
                            layout: { padding: { left: 190, right: 190, top: 24, bottom: 24 } },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(item) {
                                            return ' ' + fmt(item.raw) + ' company (' + pct(item.raw, total) + '%)';
                                        },
                                        footer: function() { return 'Klik untuk detail'; }
                                    }
                                }
                            },
                            onClick: function(evt, elements) {
                                if (elements.length) window.openCompanyCategoryDetail({ tier: tiers[elements[0].index].name });
                            }
                        },
                        plugins: [sliceLabels, calloutLabels]
                    });
                })
                .fail(function() {
                    $('#company-category-loading').text('Gagal memuat data company category.');
                });

            $('#company-category-list').on('click', '.company-category-item', function() {
                var tier = $(this).attr('data-tier');
                window.openCompanyCategoryDetail(tier ? { tier: tier } : { category: $(this).attr('data-category') });
            });
        })();

        new Chart(document.getElementById('statusBreakdownChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Pending', 'Declined', 'Deactivated'],
                datasets: [{
                    data: [
                        {{ (int) $countActiveMember }},
                        {{ (int) $countPendingMember }},
                        {{ (int) $countDeclined }},
                        {{ (int) $countDeactivated }}
                    ],
                    backgroundColor: ['#0ca30c', '#fab219', '#d03b3b', '#898781']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                }
            }
        });
    </script>
@endpush
