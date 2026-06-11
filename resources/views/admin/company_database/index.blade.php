@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Company Database</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Company Database</div>
                </div>
                <div class="section-header-button ml-auto">
                    <a href="{{ route('admin.company_database.logs') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history"></i> Activity Log
                    </a>
                </div>
            </div>

            <div class="section-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="row mb-3">

                    {{-- 1. Trend chart --}}
                    <div class="col-xl-5 col-lg-6 col-12 mb-3 mb-xl-0">
                        <div class="card mb-0 h-100" style="overflow:hidden;">
                            <div class="card-body p-0">
                                <div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2">
                                    <div>
                                        <div style="font-size:10px; text-transform:uppercase; letter-spacing:.8px; color:#adb5bd; font-weight:600;">Verified Trend</div>
                                        <div class="d-flex align-items-baseline" style="gap:6px;">
                                            <span style="font-size:30px; font-weight:700; color:#28a745; line-height:1;" id="chart-total-badge">—</span>
                                            <span style="font-size:12px; color:#6c757d;" id="chart-range-label">30 hari terakhir</span>
                                        </div>
                                    </div>
                                    <div class="d-flex" style="gap:6px;">
                                        <select id="chart-period" class="form-control form-control-sm" style="width:88px; font-size:11px;">
                                            <option value="daily">Harian</option>
                                            <option value="weekly">Mingguan</option>
                                        </select>
                                        <select id="chart-range" class="form-control form-control-sm" style="width:80px; font-size:11px;">
                                            <option value="14">14 hari</option>
                                            <option value="30" selected>30 hari</option>
                                            <option value="60">60 hari</option>
                                            <option value="90">90 hari</option>
                                        </select>
                                    </div>
                                </div>
                                <div style="position:relative; height:120px; margin-bottom:-4px;">
                                    <canvas id="verifiedTrendChart"></canvas>
                                    <div id="chart-empty" style="display:none; position:absolute; inset:0; align-items:center; justify-content:center;">
                                        <span class="text-muted" style="font-size:12px;">Belum ada data verified.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Verified progress --}}
                    <div class="col-xl-3 col-lg-6 col-12 mb-3 mb-xl-0">
                        <div class="card mb-0 h-100">
                            <div class="card-body d-flex flex-column justify-content-between p-3">
                                <div>
                                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:.8px; color:#adb5bd; font-weight:600; margin-bottom:6px;">Verification Progress</div>
                                    <div class="d-flex align-items-baseline" style="gap:4px; margin-bottom:4px;">
                                        <span style="font-size:36px; font-weight:700; line-height:1; color:{{ $verifiedPct >= 75 ? '#28a745' : ($verifiedPct >= 40 ? '#ffc107' : '#dc3545') }};">{{ $verifiedPct }}%</span>
                                    </div>
                                    <div style="font-size:12px; color:#6c757d; margin-bottom:14px;">
                                        {{ $totalVerified }} dari {{ $totalCompanies }} company
                                    </div>
                                </div>

                                {{-- Progress bar --}}
                                <div>
                                    <div style="background:#e9ecef; border-radius:99px; height:10px; overflow:hidden;">
                                        <div style="
                                            width: {{ $verifiedPct }}%;
                                            height:100%;
                                            border-radius:99px;
                                            background: {{ $verifiedPct >= 75 ? 'linear-gradient(90deg,#28a745,#6fcf97)' : ($verifiedPct >= 40 ? 'linear-gradient(90deg,#fd7e14,#ffc107)' : 'linear-gradient(90deg,#dc3545,#f77066)') }};
                                            transition: width .6s ease;
                                        "></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1" style="font-size:10px; color:#adb5bd;">
                                        <span>0%</span>
                                        <span>50%</span>
                                        <span>100%</span>
                                    </div>
                                </div>

                                {{-- Mini stat row --}}
                                <div class="d-flex justify-content-between mt-3 pt-2" style="border-top:1px solid #f1f3f5;">
                                    <div class="text-center">
                                        <div style="font-size:16px; font-weight:700; color:#28a745;">{{ $totalVerified }}</div>
                                        <div style="font-size:10px; color:#adb5bd;">Verified</div>
                                    </div>
                                    <div class="text-center">
                                        <div style="font-size:16px; font-weight:700; color:#6c757d;">{{ $totalUnverified }}</div>
                                        <div style="font-size:10px; color:#adb5bd;">Unverified</div>
                                    </div>
                                    <div class="text-center">
                                        <div style="font-size:16px; font-weight:700; color:#fd7e14;">{{ $totalNeedSync }}</div>
                                        <div style="font-size:10px; color:#adb5bd;">Need Sync</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Completeness distribution --}}
                    <div class="col-xl-4 col-lg-12 col-12">
                        <div class="card mb-0 h-100">
                            <div class="card-body p-3">
                                <div style="font-size:10px; text-transform:uppercase; letter-spacing:.8px; color:#adb5bd; font-weight:600; margin-bottom:12px;">Completeness Distribution</div>
                                @php
                                    $maxBucket = max(array_values($completenessDistribution)) ?: 1;
                                    $bucketColors = ['≤3' => '#dc3545', '4–6' => '#fd7e14', '7–9' => '#ffc107', '10' => '#20c997', '11' => '#28a745'];
                                    $bucketLabels = ['≤3' => '≤3 field', '4–6' => '4–6 field', '7–9' => '7–9 field', '10' => '10 field', '11' => 'Lengkap (11)'];
                                @endphp
                                @foreach ($completenessDistribution as $key => $count)
                                    <div class="d-flex align-items-center mb-2" style="gap:8px;">
                                        <div style="width:74px; font-size:11px; color:#6c757d; flex-shrink:0;">{{ $bucketLabels[$key] }}</div>
                                        <div style="flex:1; background:#f1f3f5; border-radius:99px; height:8px; overflow:hidden;">
                                            <div style="
                                                width:{{ $maxBucket > 0 ? round($count / $maxBucket * 100) : 0 }}%;
                                                height:100%;
                                                border-radius:99px;
                                                background:{{ $bucketColors[$key] }};
                                                transition:width .5s ease;
                                            "></div>
                                        </div>
                                        <div style="width:28px; font-size:11px; font-weight:600; color:#495057; text-align:right; flex-shrink:0;">{{ $count }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="gap:12px;">
                        <h4 class="mb-0">Company Sync Center</h4>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <span class="badge badge-light">Total Company: {{ $totalCompanies }}</span>
                            <span class="badge badge-warning">Need Sync: {{ $totalNeedSync }}</span>
                            <span class="badge badge-primary">Duplicates: {{ $totalDuplicates }}</span>
                            <span class="badge badge-success">Verified: {{ $totalVerified }}</span>
                            <span class="badge badge-secondary">Unverified: {{ $totalUnverified }}</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.company_database.index') }}" class="mb-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-5 mb-2">
                                    <label class="mb-1">Search Company</label>
                                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                                        placeholder="contoh: PT ABC">
                                </div>
                                <div class="form-group col-md-4 mb-2">
                                    <label class="mb-1">Scope</label>
                                    <select name="scope" class="form-control">
                                        <option value="need_sync" {{ $scope === 'need_sync' ? 'selected' : '' }}>Need Sync (Belum Verified)</option>
                                        <option value="duplicates" {{ $scope === 'duplicates' ? 'selected' : '' }}>Duplicates Only</option>
                                        <option value="verified" {{ $scope === 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="unverified" {{ $scope === 'unverified' ? 'selected' : '' }}>Unverified</option>
                                        <option value="all" {{ $scope === 'all' ? 'selected' : '' }}>All Companies</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 mb-2" style="display:flex; gap:8px;">
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                    <a href="{{ route('admin.company_database.index') }}" class="btn btn-outline-secondary btn-block">Reset</a>
                                </div>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.company_database.sync_all') }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="scope" value="{{ $scope }}">
                            <button type="submit" class="btn btn-warning"
                                onclick="return confirm('Sync semua company sesuai filter saat ini?')">
                                <i class="fas fa-sync"></i> Sync Semua Sesuai Filter
                            </button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="company-database-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Company Name</th>
                                        <th>Status</th>
                                        <th>Total Record</th>
                                        <th>Incomplete Record</th>
                                        <th>Best Data</th>
                                        <th>Users</th>
                                        <th>Last Update</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($list as $idx => $item)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $item->company_name }}</td>
                                            <td>
                                                @if ($item->is_verified)
                                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Verified</span>
                                                @else
                                                    <span class="badge badge-secondary">Unverified</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->total_records }}</td>
                                            <td>
                                                @if ($item->incomplete_records > 0)
                                                    <span class="badge badge-warning">{{ $item->incomplete_records }}</span>
                                                @else
                                                    <span class="badge badge-success">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                ID {{ $item->best_record_id }}
                                                <span class="badge badge-info">{{ $item->best_score }}/{{ $item->max_score }} field</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-secondary js-view-users"
                                                    data-normalized-name="{{ $item->normalized_name }}"
                                                    data-company-name="{{ $item->company_name }}"
                                                    data-total="{{ $item->total_records }}">
                                                    <i class="fas fa-users"></i> {{ $item->total_records }} user
                                                </button>
                                            </td>
                                            <td>{{ $item->updated_at ? \Carbon\Carbon::parse($item->updated_at)->format('d M Y H:i') : '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info js-edit-company"
                                                    data-toggle="modal" data-target="#editCompanyModal"
                                                    data-company-name="{{ $item->company_name }}"
                                                    data-normalized-name="{{ $item->normalized_name }}"
                                                    data-payload='@json($item->best_values)'>
                                                    Edit & Sync
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Data company tidak ditemukan untuk filter saat ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal: User List per Company --}}
    <div class="modal fade" id="userListModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Users — <span id="user_list_company_name">-</span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="user_list_loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat data users...</p>
                    </div>
                    <div id="user_list_empty" class="text-center py-4 text-muted" style="display:none;">
                        Tidak ada user ditemukan untuk company ini.
                    </div>
                    <div id="user_list_content" style="display:none;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Job Title</th>
                                    <th>Phone</th>
                                    <th>Tier</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="user_list_tbody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCompanyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.company_database.update') }}">
                    @csrf
                    <input type="hidden" name="normalized_name" id="edit_normalized_name">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Company Data & Auto Sync</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light mb-3">
                            Company: <strong id="edit_company_label">-</strong><br>
                            Perubahan akan diterapkan ke semua record company dengan nama yang sama.
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Prefix</label>
                                <select name="prefix" id="edit_prefix" class="form-control js-prefix-select2">
                                    <option value="">Other</option>
                                    <option value="PT">PT</option>
                                    <option value="CV">CV</option>
                                    <option value="Ltd">Ltd</option>
                                    <option value="GmbH">GmbH</option>
                                    <option value="Limited">Limited</option>
                                    <option value="Llc">Llc</option>
                                    <option value="Corp">Corp</option>
                                    <option value="Pte Ltd">Pte Ltd</option>
                                    <option value="Assosiation">Assosiation</option>
                                    <option value="Government">Government</option>
                                    <option value="Pty Ltd">Pty Ltd</option>
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label>Company Name</label>
                                <div class="position-relative">
                                    <input type="text" name="company_name" id="edit_company_name_input"
                                        class="form-control" required autocomplete="off"
                                        placeholder="Ketik nama company atau pilih dari verified...">
                                    <div id="company-suggestions" class="list-group position-absolute w-100"
                                        style="z-index:9999; display:none; max-height:200px; overflow-y:auto; top:100%; left:0; box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
                                </div>
                                <small class="text-muted">Ketik untuk melihat saran dari company yang sudah verified.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Website</label>
                                <input type="text" name="company_website" id="edit_company_website" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Company Category</label>
                                <select name="company_category" id="edit_company_category"
                                    class="form-control company_category_edit">
                                    <option value="">--Select--</option>
                                    <option value="Coal Mining">Coal Mining</option>
                                    <option value="Minerals Producer">Minerals Producer</option>
                                    <option value="Supplier/Distributor/Manufacturer">Supplier/Distributor/Manufacturer</option>
                                    <option value="Contrator">Contrator</option>
                                    <option value="Association / Organization / Government">Association / Organization / Government</option>
                                    <option value="Financial Services">Financial Services</option>
                                    <option value="Technology">Technology</option>
                                    <option value="Investors">Investors</option>
                                    <option value="Logistics and Shipping">Logistics and Shipping</option>
                                    <option value="Media">Media</option>
                                    <option value="Consultant">Consultant</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 company_other_edit" style="display:none;">
                                <label>Company Other</label>
                                <input type="text" name="company_other" id="edit_company_other" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="edit_address" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>City</label>
                                <input type="text" name="city" id="edit_city" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Postal Code</label>
                                <input type="text" name="portal_code" id="edit_portal_code" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Country</label>
                                <input type="text" name="country" id="edit_country" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Full Office Number</label>
                                <input type="text" name="full_office_number" id="edit_full_office_number" class="form-control" placeholder="e.g. +62 21 12345678">
                                <small class="text-muted">Awali dengan kode negara (mis. <code>+62</code>) agar prefix terdeteksi otomatis.</small>
                            </div>
                            <input type="hidden" name="prefix_office_number" id="edit_prefix_office_number">
                            <input type="hidden" name="office_number" id="edit_office_number">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Simpan perubahan dan sync ke semua record company ini?')">
                            Save & Sync
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('bottom')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.js-prefix-select2').select2({
                    dropdownParent: $('#editCompanyModal'),
                    width: '100%',
                    placeholder: 'Select Prefix',
                    allowClear: true
                });
            }

            if ($.fn.DataTable.isDataTable('#company-database-table')) {
                $('#company-database-table').DataTable().destroy();
            }

            $('#company-database-table').DataTable({
                pageLength: 25,
                order: [
                    [3, 'desc']
                ]
            });

            // ---- Verified Trend Chart ----
            var trendChart = null;

            function loadChart() {
                var period = $('#chart-period').val();
                var range  = $('#chart-range').val();
                var params = { period: period };
                if (period === 'weekly') {
                    params.weeks = Math.ceil(parseInt(range) / 7);
                } else {
                    params.days = range;
                }

                var rangeText = period === 'weekly'
                    ? 'dalam ' + Math.ceil(parseInt(range) / 7) + ' minggu'
                    : 'dalam ' + range + ' hari';
                $('#chart-range-label').text(rangeText);

                $.ajax({
                    url: '{{ route('admin.company_database.chart_data') }}',
                    data: params,
                    success: function(res) {
                        $('#chart-total-badge').text(res.total || 0);

                        var hasData = res.data && res.data.some(function(v) { return v > 0; });

                        if (!res.labels || !hasData) {
                            $('#chart-empty').css('display', 'flex');
                            $('#verifiedTrendChart').css('opacity', 0);
                            if (trendChart) { trendChart.destroy(); trendChart = null; }
                            return;
                        }
                        $('#chart-empty').hide();
                        $('#verifiedTrendChart').css('opacity', 1);

                        if (trendChart) { trendChart.destroy(); }

                        var ctx = document.getElementById('verifiedTrendChart').getContext('2d');

                        // Gradient fill
                        var grad = ctx.createLinearGradient(0, 0, 0, 120);
                        grad.addColorStop(0, 'rgba(40, 167, 69, 0.35)');
                        grad.addColorStop(1, 'rgba(40, 167, 69, 0.02)');

                        trendChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: res.labels,
                                datasets: [{
                                    label: 'Verified',
                                    data: res.data,
                                    backgroundColor: grad,
                                    borderColor: '#28a745',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#28a745',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 1.5,
                                    pointHoverRadius: 5,
                                    fill: true,
                                    lineTension: 0.4,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 400 },
                                legend: { display: false },
                                tooltips: {
                                    backgroundColor: 'rgba(0,0,0,0.75)',
                                    titleFontSize: 11,
                                    bodyFontSize: 12,
                                    callbacks: {
                                        label: function(item) { return ' ' + item.yLabel + ' company verified'; }
                                    }
                                },
                                scales: {
                                    xAxes: [{
                                        gridLines: { display: false, drawBorder: false },
                                        ticks: {
                                            fontColor: '#adb5bd',
                                            fontSize: 10,
                                            maxTicksLimit: 7,
                                            maxRotation: 0,
                                        }
                                    }],
                                    yAxes: [{
                                        display: false,
                                        ticks: { beginAtZero: true, stepSize: 1 }
                                    }]
                                },
                                layout: { padding: { left: 0, right: 0, top: 4, bottom: 0 } }
                            }
                        });
                    }
                });
            }

            loadChart();
            $('#chart-period, #chart-range').on('change', function() {
                if ($('#chart-period').val() === 'weekly') {
                    $('#chart-range option').each(function() {
                        $(this).text(Math.ceil(parseInt($(this).val()) / 7) + ' minggu');
                    });
                } else {
                    var map = { '14': '14 hari', '30': '30 hari', '60': '60 hari', '90': '90 hari' };
                    $('#chart-range option').each(function() {
                        $(this).text(map[$(this).val()] || $(this).val());
                    });
                }
                loadChart();
            });

            // ---- View Users modal ----
            $(document).on('click', '.js-view-users', function() {
                var normalizedName = $(this).data('normalized-name');
                var companyName    = $(this).data('company-name');

                $('#user_list_company_name').text(companyName);
                $('#user_list_loading').show();
                $('#user_list_content').hide();
                $('#user_list_empty').hide();
                $('#user_list_tbody').empty();
                $('#userListModal').modal('show');

                $.ajax({
                    url: '{{ route('admin.company_database.company_users') }}',
                    data: { normalized_name: normalizedName },
                    success: function(data) {
                        $('#user_list_loading').hide();

                        if (!data || data.length === 0) {
                            $('#user_list_empty').show();
                            return;
                        }

                        var tierLabel = { '1': 'Free', '2': 'Basic', '3': 'Premium', '4': 'VIP' };
                        var statusLabel = { '0': '<span class="badge badge-secondary">Inactive</span>',
                                            '1': '<span class="badge badge-success">Active</span>' };

                        $.each(data, function(i, u) {
                            var tier   = tierLabel[String(u.tier)]   || u.tier   || '-';
                            var status = statusLabel[String(u.status_member)] || u.status_member || '-';
                            var row = '<tr>'
                                + '<td>' + (i + 1) + '</td>'
                                + '<td>' + $('<span>').text(u.name || '-').html() + '</td>'
                                + '<td><small>' + $('<span>').text(u.email || '-').html() + '</small></td>'
                                + '<td>' + $('<span>').text(u.job_title || '-').html() + '</td>'
                                + '<td>' + $('<span>').text(u.fullphone || '-').html() + '</td>'
                                + '<td>' + tier + '</td>'
                                + '<td>' + status + '</td>'
                                + '</tr>';
                            $('#user_list_tbody').append(row);
                        });

                        $('#user_list_content').show();
                    },
                    error: function() {
                        $('#user_list_loading').hide();
                        $('#user_list_empty').text('Gagal memuat data.').show();
                    }
                });
            });

            // ---- Edit & Sync modal populate ----
            $(document).on('click', '.js-edit-company', function() {
                var payload = $(this).attr('data-payload') || '{}';
                var parsed = {};

                try {
                    parsed = JSON.parse(payload);
                } catch (e) {
                    parsed = {};
                }

                $('#edit_company_label').text($(this).data('company-name') || '-');
                $('#edit_normalized_name').val($(this).data('normalized-name') || '');
                $('#edit_company_name_input').val(parsed.company_name || $(this).data('company-name') || '');
                $('#edit_prefix').val(parsed.prefix || '').trigger('change');
                $('#edit_company_website').val(parsed.company_website || '');
                $('#edit_company_category').val(parsed.company_category || '');
                $('#edit_company_other').val(parsed.company_other || '');
                $('#edit_address').val(parsed.address || '');
                $('#edit_city').val(parsed.city || '');
                $('#edit_portal_code').val(parsed.portal_code || '');
                $('#edit_country').val(parsed.country || '');
                var fullOffice = parsed.full_office_number || '';
                $('#edit_full_office_number').val(fullOffice);
                var officeParsed = parseOfficeNumber(fullOffice);
                $('#edit_prefix_office_number').val(parsed.prefix_office_number || officeParsed.prefix);
                $('#edit_office_number').val(parsed.office_number || officeParsed.office);
                $('#edit_company_category').trigger('change');
                $('#company-suggestions').hide().empty();
            });

            // ---- Company name autocomplete from verified companies ----
            var suggestionTimeout = null;

            $('#edit_company_name_input').on('input', function() {
                var q = $(this).val().trim();
                clearTimeout(suggestionTimeout);
                $('#company-suggestions').hide().empty();

                if (q.length < 2) {
                    return;
                }

                suggestionTimeout = setTimeout(function() {
                    $.ajax({
                        url: '{{ route('admin.company_database.verified_companies') }}',
                        data: { q: q },
                        success: function(data) {
                            var $box = $('#company-suggestions');
                            $box.empty();

                            if (!data || data.length === 0) {
                                $box.hide();
                                return;
                            }

                            $.each(data, function(i, company) {
                                var $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
                                $item.html(
                                    '<span class="badge badge-success badge-sm mr-1"><i class="fas fa-check-circle"></i> Verified</span> ' +
                                    '<strong>' + $('<span>').text(company.company_name).html() + '</strong>'
                                );
                                $item.on('click', function() {
                                    fillFromVerified(company);
                                    $box.hide().empty();
                                });
                                $box.append($item);
                            });

                            $box.show();
                        },
                        error: function() {
                            $('#company-suggestions').hide().empty();
                        }
                    });
                }, 300);
            });

            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#edit_company_name_input, #company-suggestions').length) {
                    $('#company-suggestions').hide().empty();
                }
            });

            function parseOfficeNumber(full) {
                var trimmed = (full || '').trim();
                var match = trimmed.match(/^(\+\d+)\s*(.*)$/);
                if (match) {
                    return { prefix: match[1], office: match[2].trim() };
                }
                return { prefix: '', office: trimmed };
            }

            $('#edit_full_office_number').on('input', function() {
                var parsed = parseOfficeNumber($(this).val());
                $('#edit_prefix_office_number').val(parsed.prefix);
                $('#edit_office_number').val(parsed.office);
            });

            function fillFromVerified(company) {
                $('#edit_company_name_input').val(company.company_name || '');
                $('#edit_prefix').val(company.prefix || '').trigger('change');
                $('#edit_company_website').val(company.company_website || '');
                $('#edit_company_category').val(company.company_category || '').trigger('change');
                $('#edit_company_other').val(company.company_other || '');
                $('#edit_address').val(company.address || '');
                $('#edit_city').val(company.city || '');
                $('#edit_portal_code').val(company.portal_code || '');
                $('#edit_country').val(company.country || '');
                var fullOfficeV = company.full_office_number || '';
                $('#edit_full_office_number').val(fullOfficeV);
                var officeParsedV = parseOfficeNumber(fullOfficeV);
                $('#edit_prefix_office_number').val(company.prefix_office_number || officeParsedV.prefix);
                $('#edit_office_number').val(company.office_number || officeParsedV.office);
            }

            // ---- Category other toggle ----
            $('#edit_company_category').on('change', function() {
                if ($(this).val() === 'other') {
                    $('.company_other_edit').css('display', 'block');
                } else {
                    $('.company_other_edit').css('display', 'none');
                    $('#edit_company_other').val('');
                }
            });
        });
    </script>
@endpush
