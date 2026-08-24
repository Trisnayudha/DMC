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
                    <div class="card-header"><h4>Approved This Month</h4></div>
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

{{-- Members by Source (Members Relation SOP §8) --}}
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    Members by Source
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                        title="Breakdown member, leads, dan konversi per channel pendaftaran."></i>
                </h4>
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

    @media (max-width: 575.98px) {
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
