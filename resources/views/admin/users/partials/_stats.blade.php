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
                    <canvas id="registrationsChart"></canvas>
                </div>
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
                weekly:  { labels: @json($registrationsWeeklyLabels),  counts: @json($registrationsWeeklyCounts) },
                monthly: { labels: @json($registrationsMonthlyLabels), counts: @json($registrationsMonthlyCounts) },
                yearly:  { labels: @json($registrationsYearlyLabels),  counts: @json($registrationsYearlyCounts) },
            };
            var toggleButtons = {
                weekly:  document.getElementById('reg-toggle-weekly'),
                monthly: document.getElementById('reg-toggle-monthly'),
                yearly:  document.getElementById('reg-toggle-yearly'),
            };

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
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            function selectPeriod(key) {
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
