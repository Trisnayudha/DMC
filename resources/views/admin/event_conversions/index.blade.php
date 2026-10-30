@extends('layouts.inspire.master')

@section('content')
@include('admin.event_conversions.partials._diagram_styles')
<style>

    /* Quick KPI Cards */
    .kpi-card-metric {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: transform 0.2s;
    }

    .kpi-card-metric:hover {
        transform: translateY(-2px);
    }

    .kpi-val {
        font-size: 24px;
        font-weight: 800;
        color: #0e273c;
    }

    .kpi-lbl {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
    }

    /* Progress bar in table */
    .conversion-pill-bar {
        height: 6px;
        border-radius: 10px;
        background: #e2e8f0;
        overflow: hidden;
        margin-top: 4px;
    }

    .conversion-pill-fill {
        height: 100%;
        border-radius: 10px;
        background: linear-gradient(90deg, #b57a2b, #10b981);
    }
</style>

<div class="content-wrapper">
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h1>Event &amp; Member Conversion</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('events') }}">Events</a></div>
                    <div class="breadcrumb-item active">Member Conversion</div>
                </div>
            </div>

            <!-- Controls: Year filter & Export -->
            <div class="d-flex align-items-center" style="gap: 10px;">
                <form method="GET" action="{{ route('admin.event_conversions.index') }}" id="filterYearForm" class="d-inline-flex align-items-center">
                    <input type="hidden" name="type" value="{{ $selectedType }}">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                        </div>
                        <select name="year" class="form-control form-control-sm border-left-0 font-weight-bold" onchange="document.getElementById('filterYearForm').submit()">
                            <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>All Years</option>
                            @foreach ($years as $y)
                                <option value="{{ $y }}" {{ (string)$selectedYear === (string)$y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <a href="{{ route('admin.event_conversions.export', ['year' => $selectedYear, 'type' => $selectedType, 'search' => $search]) }}"
                   class="btn btn-sm btn-success" title="Download Excel Laporan Konversi Event">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
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

            @include('admin.event_conversions.partials._diagram')

            <!-- 2. SUMMARY KPI ROW -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card kpi-card-metric mb-0">
                        <div class="card-body p-3 text-center">
                            <div class="kpi-lbl">Total Events</div>
                            <div class="kpi-val">{{ count($events) }}</div>
                            <small class="text-muted">{{ $ownedCount }} Owned &bull; {{ $supportingCount }} Part.</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card kpi-card-metric mb-0">
                        <div class="card-body p-3 text-center">
                            <div class="kpi-lbl">Total Entries</div>
                            <div class="kpi-val text-primary">{{ number_format($annualAttendance['entries']) }}</div>
                            <small class="text-muted">Attendance &amp; Visits</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card kpi-card-metric mb-0">
                        <div class="card-body p-3 text-center">
                            <div class="kpi-lbl">Unique People</div>
                            <div class="kpi-val text-info">{{ number_format($annualAttendance['unique_people']) }}</div>
                            <small class="text-muted">Distinct Contacts</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card kpi-card-metric mb-0">
                        <div class="card-body p-3 text-center">
                            <div class="kpi-lbl">Verified Members</div>
                            <div class="kpi-val text-success">{{ number_format($annualAttendance['verified_members']) }}</div>
                            <small class="text-muted">Attended as Member</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card kpi-card-metric mb-0">
                        <div class="card-body p-3 text-center">
                            <div class="kpi-lbl">Total Converted</div>
                            <div class="kpi-val text-warning">{{ number_format($annualConversion['total_converted']) }}</div>
                            <small class="text-muted">{{ $annualConversion['pending'] }} Pend &bull; {{ $annualConversion['active'] }} Act</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="card kpi-card-metric mb-0">
                        <div class="card-body p-3 text-center">
                            <div class="kpi-lbl">Avg Conv. Rate</div>
                            <div class="kpi-val text-danger">{{ $annualConversion['rate'] }}%</div>
                            <small class="text-muted">Overall Conversion</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. VISUAL CHARTS ROW -->
            <div class="row mb-4">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                            <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 15px;">
                                <i class="fas fa-filter text-primary mr-2"></i>Funnel Comparison: Owned Events vs Supporting Events
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 280px; width: 100%;">
                                <canvas id="funnelCompareChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                            <h4 class="mb-0 text-dark font-weight-bold" style="font-size: 15px;">
                                <i class="fas fa-chart-pie text-warning mr-2"></i>Conversion Mix by Event Type
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 280px; width: 100%;">
                                <canvas id="conversionMixChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. DETAILED BREAKDOWN TABLE PER EVENT -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div>
                        <h4 class="mb-0">Rincian Konversi Member per Event</h4>
                        <small class="text-muted">Pantau efektivitas setiap event dalam mengonversi kontak/peserta menjadi member DMC</small>
                    </div>

                    <!-- Filter Tabs: All, Owned, Supporting -->
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.event_conversions.index', ['year' => $selectedYear, 'type' => 'all', 'search' => $search]) }}"
                           class="btn btn-sm {{ $selectedType === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                            All Events ({{ count($events) }})
                        </a>
                        <a href="{{ route('admin.event_conversions.index', ['year' => $selectedYear, 'type' => 'owned', 'search' => $search]) }}"
                           class="btn btn-sm {{ $selectedType === 'owned' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-calendar-alt mr-1"></i> DMC Owned ({{ $ownedCount }})
                        </a>
                        <a href="{{ route('admin.event_conversions.index', ['year' => $selectedYear, 'type' => 'supporting', 'search' => $search]) }}"
                           class="btn btn-sm {{ $selectedType === 'supporting' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-handshake mr-1"></i> Supporting ({{ $supportingCount }})
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search input -->
                    <form method="GET" action="{{ route('admin.event_conversions.index') }}" class="mb-3">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <input type="hidden" name="type" value="{{ $selectedType }}">
                        <div class="input-group" style="max-width: 400px;">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama event atau lokasi..." value="{{ $search }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Cari</button>
                                @if ($search !== '')
                                    <a href="{{ route('admin.event_conversions.index', ['year' => $selectedYear, 'type' => $selectedType]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="eventConversionTable" style="font-size: 12.5px;">
                            <thead class="thead-light">
                                <tr>
                                    <th width="30px">No</th>
                                    <th>Event &amp; Kategori</th>
                                    <th>Tanggal &amp; Lokasi</th>
                                    <th class="text-center" title="Total registrasi tiket atau visitor booth">Reg / Visitor</th>
                                    <th class="text-center" title="Registrasi lunas/terkonfirmasi atau kontak valid">Confirmed</th>
                                    <th class="text-center" title="Hadir fisik / badge check-in / booth visit">Attendance</th>
                                    <th class="text-center" title="Member aktif DMC yang hadir">Verified Member</th>
                                    <th class="text-center" title="Follow up interest / Prospek member">Interest</th>
                                    <th class="text-center" title="Calon member (Pending) & Member Aktif baru">Converted Member</th>
                                    <th class="text-center" width="130px">Conv. Rate</th>
                                    <th class="text-center" width="120px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($events as $idx => $ev)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold" style="color: #0e273c; font-size: 13.5px;">{{ $ev->name }}</div>
                                            @if ($ev->is_supporting)
                                                <span class="badge badge-info" style="font-size: 10px;"><i class="fas fa-handshake mr-1"></i> Supporting Event</span>
                                            @else
                                                <span class="badge badge-dark" style="font-size: 10px; background-color: #0e273c;"><i class="fas fa-calendar-check mr-1 text-warning"></i> DMC Owned</span>
                                            @endif
                                            <span class="text-muted ml-1" style="font-size: 11px;">({{ $ev->event_type }})</span>
                                        </td>
                                        <td>
                                            <div><i class="far fa-calendar-alt text-muted mr-1"></i>{{ $ev->start_date ? \Carbon\Carbon::parse($ev->start_date)->format('d M Y') : '-' }}</div>
                                            <div class="text-muted small"><i class="fas fa-map-marker-alt text-muted mr-1"></i>{{ \Illuminate\Support\Str::limit($ev->location, 26) }}</div>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ number_format($ev->registration_count) }}</td>
                                        <td class="text-center">{{ number_format($ev->confirmed_count) }}</td>
                                        <td class="text-center font-weight-bold text-primary">{{ number_format($ev->attendance_count) }}</td>
                                        <td class="text-center text-success"><i class="fas fa-check-circle mr-1"></i>{{ number_format($ev->verified_members_count) }}</td>
                                        <td class="text-center text-warning font-weight-bold">{{ number_format($ev->interest_count) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $ev->total_converted > 0 ? 'badge-success' : 'badge-light' }} font-weight-bold px-2 py-1" style="font-size: 12px;">
                                                {{ number_format($ev->total_converted) }}
                                            </span>
                                            <div class="text-muted" style="font-size: 10px; margin-top: 2px;">
                                                {{ $ev->pending_converted }} pend &bull; {{ $ev->active_converted }} act
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="font-weight-bold {{ $ev->conversion_rate > 0 ? 'text-success' : 'text-muted' }}">
                                                    {{ $ev->conversion_rate }}%
                                                </span>
                                            </div>
                                            <div class="conversion-pill-bar">
                                                <div class="conversion-pill-fill" style="width: {{ min(100, $ev->conversion_rate) }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-view-members"
                                                        data-id="{{ $ev->id }}"
                                                        data-name="{{ $ev->name }}"
                                                        title="Lihat daftar member yang terkonversi dari event ini">
                                                    <i class="fas fa-users"></i> Member
                                                </button>
                                                <a href="{{ $ev->detail_url }}" target="_blank" class="btn btn-sm btn-light" title="Buka detail event">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="fas fa-folder-open fa-2x mb-2 d-block text-muted"></i>
                                            Tidak ada event yang ditemukan untuk filter ini.
                                        </td>
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

<!-- 5. MODAL DRILLDOWN: DAFTAR MEMBER TERKONVERSI -->
<div class="modal fade" id="modalConvertedMembers" tabindex="-1" role="dialog" aria-labelledby="modalConvertedMembersTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white" style="background: linear-gradient(145deg, #0e273c, #1a3c5a) !important;">
                <div>
                    <h5 class="modal-title text-white" id="modalConvertedMembersTitle">
                        <i class="fas fa-user-check text-warning mr-2"></i>Member Terkonversi dari Event
                    </h5>
                    <div id="modalEventSubtitle" class="text-light small mt-1">Memuat data...</div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div id="modalLoadingSpinner" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted small">Mengambil rincian peserta &amp; member terkonversi...</div>
                </div>

                <div id="modalContentContainer" style="display: none;">
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3 py-2 px-3">
                        <div>
                            <span class="badge badge-dark mr-2" id="modalBadgeType">Owned Event</span>
                            <span class="text-muted" id="modalTotalSummary">0 kontak terkonversi</span>
                        </div>
                        <div class="small">
                            <span class="badge badge-warning mr-1"><i class="fas fa-clock mr-1"></i>Pending Verification</span>
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Active Member</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover" id="tableConvertedList" style="font-size: 12px;">
                            <thead class="thead-light">
                                <tr>
                                    <th width="30px">No</th>
                                    <th>Nama Member / Kontak</th>
                                    <th>Email</th>
                                    <th>Perusahaan &amp; Job Title</th>
                                    <th>Nomor Telepon</th>
                                    <th class="text-center">Status Keanggotaan</th>
                                    <th class="text-center">Source</th>
                                    <th class="text-center">Tgl Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyConvertedList">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="modalEmptyState" style="display: none;" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle fa-2x mb-2 text-muted"></i>
                    <div>Belum ada kontak dari event ini yang terkonversi menjadi calon member atau member aktif.</div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <a href="{{ route('members') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-users mr-1"></i> Buka Manajemen Member DMC
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('bottom')
<script>
$(document).ready(function() {
    // 1. Inisialisasi DataTable untuk breakdown per event
    if ($('#eventConversionTable tbody tr').length > 1 && !$('#eventConversionTable tbody tr td[colspan]').length) {
        $('#eventConversionTable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Filter cepat tabel..."
            }
        });
    }

    // 2. Render Chart 1: Funnel Drop-off Comparison (Owned vs Supporting)
    var ctxFunnel = document.getElementById('funnelCompareChart');
    if (ctxFunnel && typeof Chart !== 'undefined') {
        new Chart(ctxFunnel.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Registration / Visitors', 'Confirmation', 'Attendance / Entries', 'Follow-up Interest', 'Converted to Member'],
                datasets: [
                    {
                        label: 'DMC Owned Events',
                        backgroundColor: '#0e273c',
                        borderColor: '#0e273c',
                        borderWidth: 1,
                        data: [
                            {{ (int) $funnelOwned['registrations'] }},
                            {{ (int) $funnelOwned['confirmed'] }},
                            {{ (int) $funnelOwned['attendance'] }},
                            {{ (int) $funnelOwned['interest'] }},
                            {{ (int) $funnelOwned['converted'] }}
                        ]
                    },
                    {
                        label: 'Supporting Events',
                        backgroundColor: '#b57a2b',
                        borderColor: '#b57a2b',
                        borderWidth: 1,
                        data: [
                            {{ (int) $funnelSupporting['registrations'] }},
                            {{ (int) $funnelSupporting['confirmed'] }},
                            {{ (int) $funnelSupporting['attendance'] }},
                            {{ (int) $funnelSupporting['interest'] }},
                            {{ (int) $funnelSupporting['converted'] }}
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                }
            }
        });
    }

    // 3. Render Chart 2: Conversion Mix Doughnut
    var ctxMix = document.getElementById('conversionMixChart');
    if (ctxMix && typeof Chart !== 'undefined') {
        var ownedConv = {{ (int) $funnelOwned['converted'] }};
        var suppConv = {{ (int) $funnelSupporting['converted'] }};
        var totalConv = ownedConv + suppConv;

        var mixData = totalConv > 0 ? [ownedConv, suppConv] : [1];
        var mixLabels = totalConv > 0 ? ['Owned Events (' + ownedConv + ')', 'Supporting Events (' + suppConv + ')'] : ['Belum Ada Konversi (0)'];
        var mixColors = totalConv > 0 ? ['#0e273c', '#b57a2b'] : ['#e2e8f0'];

        new Chart(ctxMix.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: mixLabels,
                datasets: [{
                    data: mixData,
                    backgroundColor: mixColors,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                tooltips: {
                    enabled: totalConv > 0
                }
            }
        });
    }

    // 4. Modal Drilldown Member Terkonversi via AJAX
    $('.btn-view-members').on('click', function() {
        var eventId = $(this).data('id');
        var eventName = $(this).data('name');

        $('#modalConvertedMembersTitle').html('<i class="fas fa-user-check text-warning mr-2"></i>' + eventName);
        $('#modalEventSubtitle').text('Memuat kontak member yang terkonversi...');
        $('#modalLoadingSpinner').show();
        $('#modalContentContainer').hide();
        $('#modalEmptyState').hide();
        $('#tbodyConvertedList').empty();

        $('#modalConvertedMembers').modal('show');

        $.ajax({
            url: "{{ url('admin/event-conversions') }}/" + eventId + "/members",
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                $('#modalLoadingSpinner').hide();
                if (res.status && res.members && res.members.length > 0) {
                    $('#modalEventSubtitle').text(res.event_type + ' • ' + res.count + ' Kontak Terkonversi');
                    $('#modalBadgeType').text(res.is_supporting ? 'Supporting Event (Partnership)' : 'DMC Owned Event');
                    $('#modalBadgeType').attr('class', 'badge mr-2 ' + (res.is_supporting ? 'badge-info' : 'badge-dark'));
                    $('#modalTotalSummary').text(res.count + ' peserta terdaftar / terkonversi menjadi member');

                    var rowsHtml = '';
                    $.each(res.members, function(idx, m) {
                        var statusBadge = m.status_member === 'active'
                            ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Active</span>'
                            : '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Pending</span>';

                        rowsHtml += '<tr>' +
                            '<td>' + (idx + 1) + '</td>' +
                            '<td class="font-weight-bold">' + (m.name || '-') + '</td>' +
                            '<td>' + (m.email || '-') + '</td>' +
                            '<td><div>' + (m.company || '-') + '</div><small class="text-muted">' + (m.job_title || '-') + '</small></td>' +
                            '<td>' + (m.phone || '-') + '</td>' +
                            '<td class="text-center">' + statusBadge + '</td>' +
                            '<td class="text-center"><span class="badge badge-light">' + (m.source || '-') + '</span></td>' +
                            '<td class="text-center">' + (m.converted_at || '-') + '</td>' +
                            '</tr>';
                    });

                    $('#tbodyConvertedList').html(rowsHtml);
                    $('#modalContentContainer').show();
                } else {
                    $('#modalEventSubtitle').text(res.event_type + ' • 0 Kontak');
                    $('#modalEmptyState').show();
                }
            },
            error: function() {
                $('#modalLoadingSpinner').hide();
                $('#modalEventSubtitle').text('Gagal memuat data');
                $('#tbodyConvertedList').html('<tr><td colspan="8" class="text-center text-danger py-3">Terjadi kesalahan saat memuat data.</td></tr>');
                $('#modalContentContainer').show();
            }
        });
    });
});
</script>
@endpush
