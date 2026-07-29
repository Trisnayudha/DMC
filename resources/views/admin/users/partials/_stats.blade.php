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
            var weeklyLabels  = @json($registrationsWeeklyLabels);
            var weeklyCounts  = @json($registrationsWeeklyCounts);
            var monthlyLabels = @json($registrationsMonthlyLabels);
            var monthlyCounts = @json($registrationsMonthlyCounts);

            var registrationsChart = new Chart(document.getElementById('registrationsChart'), {
                type: 'bar',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: 'New Registrations',
                        data: weeklyCounts,
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

            document.getElementById('reg-toggle-weekly').addEventListener('click', function() {
                this.classList.remove('btn-outline-primary'); this.classList.add('btn-primary');
                var monthlyBtn = document.getElementById('reg-toggle-monthly');
                monthlyBtn.classList.remove('btn-primary'); monthlyBtn.classList.add('btn-outline-primary');
                registrationsChart.data.labels = weeklyLabels;
                registrationsChart.data.datasets[0].data = weeklyCounts;
                registrationsChart.update();
            });
            document.getElementById('reg-toggle-monthly').addEventListener('click', function() {
                this.classList.remove('btn-outline-primary'); this.classList.add('btn-primary');
                var weeklyBtn = document.getElementById('reg-toggle-weekly');
                weeklyBtn.classList.remove('btn-primary'); weeklyBtn.classList.add('btn-outline-primary');
                registrationsChart.data.labels = monthlyLabels;
                registrationsChart.data.datasets[0].data = monthlyCounts;
                registrationsChart.update();
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
