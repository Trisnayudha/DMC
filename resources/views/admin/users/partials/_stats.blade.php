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

{{-- Statistik & Chart — satu card, collapse by default. Isinya (SLA, Leads, chart
     registrasi/validasi/status, Job Tier, Company Category Tier, Members by Source)
     TIDAK dihitung saat halaman dimuat: query berat + Chart.js baru jalan saat
     panel dibuka pertama kali (UsersController@stats → _stats_panel). --}}
<div class="card member-stats-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-chart-bar mr-1"></i>Statistik &amp; Chart
            <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip"
                title="Verification SLA, Leads, chart registrasi & validasi, Job Tier, Company Category Tier, Members by Source. Data baru dimuat saat dibuka."></i>
        </h4>
        <button type="button" class="btn btn-sm btn-outline-primary" id="member-stats-toggle"
            data-toggle="collapse" data-target="#member-stats-collapse"
            aria-expanded="false" aria-controls="member-stats-collapse">
            <i class="fas fa-eye mr-1"></i><span>Show</span>
        </button>
    </div>
    <div class="collapse" id="member-stats-collapse">
        <div class="card-body" id="member-stats-body">
            <div class="text-center text-muted py-5" id="member-stats-loading">
                <span class="spinner-border spinner-border-sm mr-2"></span>Memuat statistik...
            </div>
        </div>
    </div>
</div>

@include('admin.company_database.partials._category_tier_styles')
<style>
    /* Panel Statistik & Chart: card di dalamnya dibuat flat (border tipis, tanpa
       shadow) supaya tidak terlihat seperti card bertumpuk. */
    .member-stats-panel .card { box-shadow: none; border: 1px solid #eef0f4; }
    .member-stats-panel .card .card-header { min-height: 56px; }

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
</style>

@push('bottom')
    @include('admin.company_database.partials._category_tier_script')
    @include('admin.users.partials._stats_scripts')
    <script>
        // Panel Statistik & Chart: data (UsersController@stats) dan Chart.js v4 baru
        // diambil saat panel dibuka pertama kali; buka-tutup berikutnya tidak memuat ulang.
        (function() {
            var statsUrl = @json(route('users.stats'));
            var chartJsUrl = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
            var state = 'idle'; // idle | loading | loaded

            // Layout sudah memuat Chart.js v2 (stisla) — chart di panel ini butuh v4.
            function loadChartJs() {
                if (window.Chart && /^4\./.test(window.Chart.version || '')) {
                    return $.Deferred().resolve().promise();
                }
                return $.ajax({ url: chartJsUrl, dataType: 'script', cache: true });
            }

            function loadStats() {
                state = 'loading';
                $('#member-stats-body').html(
                    '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm mr-2"></span>Memuat statistik...</div>'
                );

                // Query string halaman ikut dikirim: Members by Source mengikuti filter tabel.
                $.when($.getJSON(statsUrl + window.location.search), loadChartJs())
                    .done(function(statsArgs) {
                        var res = statsArgs[0];
                        $('#member-stats-body').html(res.html);
                        $('#member-stats-body [data-toggle="tooltip"]').tooltip();
                        window.initMemberStats(res.data);
                        state = 'loaded';
                    })
                    .fail(function() {
                        state = 'idle';
                        $('#member-stats-body').html(
                            '<div class="text-center text-muted py-4">Gagal memuat statistik. '
                            + '<a href="#" id="member-stats-retry">Coba lagi</a></div>'
                        );
                    });
            }

            $('#member-stats-collapse')
                .on('shown.bs.collapse', function(e) {
                    if (e.target !== this) return; // collapse lain di dalam panel ikut bubble ke sini
                    $('#member-stats-toggle span').text('Hide');
                    $('#member-stats-toggle i').attr('class', 'fas fa-eye-slash mr-1');
                    if (state === 'idle') loadStats();
                })
                .on('hidden.bs.collapse', function(e) {
                    if (e.target !== this) return;
                    $('#member-stats-toggle span').text('Show');
                    $('#member-stats-toggle i').attr('class', 'fas fa-eye mr-1');
                });

            $(document).on('click', '#member-stats-retry', function(e) {
                e.preventDefault();
                loadStats();
            });
        })();
    </script>
@endpush
