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
                                title="Jumlah company (bukan member) dengan status is_verified = true."
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

{{-- New Member Validation (48h) --}}
<div class="row">
    <div class="col-lg-5 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    New Member Validation
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-html="true"
                        title="Mengukur kecepatan admin memvalidasi member baru sejak tanggal daftar (target: 48 jam). Data mulai tercatat sejak fitur ini aktif — member yang sudah aktif sebelumnya belum punya histori validasi."></i>
                </h4>
            </div>
            <div class="card-body">
                <canvas id="validate48hChart" height="180"></canvas>
                <div class="d-flex justify-content-around mt-3 text-center">
                    <div>
                        <h5 class="mb-0 text-success">{{ $countValidatedWithin48h }}</h5>
                        <small class="text-muted">Validated &le; 48h</small>
                    </div>
                    <div>
                        <h5 class="mb-0 text-danger">{{ $countValidatedAfter48h }}</h5>
                        <small class="text-muted">Validated &gt; 48h</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('bottom')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('validate48hChart'), {
            type: 'doughnut',
            data: {
                labels: ['Validated ≤ 48h', 'Validated > 48h'],
                datasets: [{
                    data: [{{ (int) $countValidatedWithin48h }}, {{ (int) $countValidatedAfter48h }}],
                    backgroundColor: ['#1cc88a', '#e74a3b']
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
@endpush
