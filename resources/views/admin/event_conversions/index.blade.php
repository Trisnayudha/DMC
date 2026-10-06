@extends('layouts.inspire.master')

@section('content')
<style>
    /* Styling khusus diagram Event Database & Member Conversion */
    .conversion-diagram-container {
        background: #fbfbfb;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 30px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    .diagram-header-title {
        color: #0e273c;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .diagram-header-subtitle {
        color: #5a6a80;
        font-size: 14.5px;
        margin-top: 4px;
    }

    .diagram-card {
        border-radius: 14px;
        padding: 20px;
        transition: all 0.25s ease;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .diagram-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    /* Left Column: Event Types */
    .card-owned-events {
        background: linear-gradient(145deg, #0e273c 0%, #173752 100%);
        color: #ffffff;
        border-left: 5px solid #d89f4b;
    }

    .card-supporting-events {
        background: linear-gradient(145deg, #1b354d 0%, #294764 100%);
        color: #ffffff;
        border-left: 5px solid #38bdf8;
    }

    .card-type-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .card-type-freq {
        color: #d89f4b;
        font-size: 13.5px;
        font-weight: 600;
    }

    .card-type-meta {
        font-size: 11.5px;
        color: #cbd5e1;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid rgba(255,255,255,0.15);
    }

    /* Center Column: Event Participation */
    .card-participation {
        background: #ffffff;
        border: 2px solid #c8923a;
        border-radius: 14px;
        padding: 24px 20px;
        box-shadow: 0 4px 15px rgba(200, 146, 58, 0.08);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .participation-title {
        color: #0e273c;
        font-size: 19px;
        font-weight: 800;
        text-align: center;
        margin-bottom: 16px;
    }

    .participation-stage-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .participation-stage-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        margin-bottom: 8px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #edf2f7;
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
    }

    .participation-stage-item:last-child {
        margin-bottom: 0;
    }

    .stage-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    /* Right Column: Outcomes */
    .card-attendance-outcome {
        background: linear-gradient(145deg, #0e273c 0%, #152f47 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 18px 20px;
    }

    .card-conversion-outcome {
        background: linear-gradient(145deg, #b57a2b 0%, #cf923a 100%);
        color: #ffffff;
        border-radius: 14px;
        padding: 18px 20px;
        margin-top: 16px;
    }

    .outcome-title {
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .outcome-metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        text-align: center;
    }

    .outcome-metric-box {
        background: rgba(0, 0, 0, 0.16);
        border-radius: 8px;
        padding: 8px 6px;
    }

    .outcome-metric-value {
        font-size: 18px;
        font-weight: 800;
        line-height: 1.2;
    }

    .outcome-metric-label {
        font-size: 10.5px;
        opacity: 0.9;
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .conversion-explainer {
        font-size: 11.5px;
        color: #fef08a;
        margin-top: 8px;
        line-height: 1.35;
    }

    /* Connecting connectors */
    .connector-col {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .connector-line {
        height: 2px;
        background: #cbd5e1;
        width: 100%;
        position: relative;
    }

    /* Bottom Guideline Quote */
    .guideline-banner {
        margin-top: 26px;
        padding: 16px 20px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        text-align: center;
    }

    .guideline-quote {
        font-size: 14.5px;
        font-weight: 700;
        color: #0e273c;
        margin-bottom: 6px;
    }

    .guideline-sub {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

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

            <!-- 1. EXECUTIVE DIAGRAM SECTION (Sesuai Slide Internal Operating Guideline) -->
            <div class="conversion-diagram-container mb-4">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <div class="diagram-header-title">
                            <i class="fas fa-project-diagram mr-2 text-warning"></i>Event database and member conversion
                        </div>
                        <div class="diagram-header-subtitle">
                            DMC records all participation while controlling when an event contact becomes a member.
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <span class="badge badge-dark px-3 py-2" style="font-size: 12px; letter-spacing: 0.5px;">
                            <i class="fas fa-filter mr-1 text-warning"></i> Periode: {{ $selectedYear === 'all' ? 'Semua Tahun' : 'Tahun ' . $selectedYear }}
                        </span>
                    </div>
                </div>

                <!-- Three Column Visual Funnel -->
                <div class="row align-items-stretch">
                    <!-- Column 1: Event Sources (Left) -->
                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0 d-flex flex-column justify-content-between" style="gap: 16px;">
                        <!-- DMC owned events card -->
                        <div class="diagram-card card-owned-events">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="card-type-title">DMC owned events</div>
                                    <div class="card-type-freq">4 to 5 per year</div>
                                </div>
                                <span class="badge badge-warning font-weight-bold">{{ $funnelOwned['events'] }} Events</span>
                            </div>
                            <div class="card-type-meta">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Entries (Check-in):</span>
                                    <strong>{{ number_format($funnelOwned['attendance']) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Converted Members:</span>
                                    <strong class="text-warning">{{ number_format($funnelOwned['converted']) }} ({{ $funnelOwned['rate'] }}%)</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Supporting events card -->
                        <div class="diagram-card card-supporting-events">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="card-type-title">Supporting events</div>
                                    <div class="card-type-freq" style="color: #38bdf8;">2 to 3 per year</div>
                                </div>
                                <span class="badge badge-info font-weight-bold">{{ $funnelSupporting['events'] }} Events</span>
                            </div>
                            <div class="card-type-meta">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Booth Visitors:</span>
                                    <strong>{{ number_format($funnelSupporting['attendance']) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Converted Members:</span>
                                    <strong class="text-info">{{ number_format($funnelSupporting['converted']) }} ({{ $funnelSupporting['rate'] }}%)</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Event Participation (Center Funnel) -->
                    <div class="col-lg-4 col-md-4 mb-3 mb-md-0">
                        <div class="card-participation">
                            <div class="participation-title">
                                <i class="fas fa-tasks mr-2 text-warning"></i>Event Participation
                            </div>
                            <ul class="participation-stage-list">
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-user-plus text-primary mr-2"></i>Registration</span>
                                    <span class="stage-badge bg-primary text-white">{{ number_format($eventParticipation['registration']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-check-circle text-info mr-2"></i>Confirmation</span>
                                    <span class="stage-badge bg-info text-white">{{ number_format($eventParticipation['confirmation']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-id-badge text-success mr-2"></i>Check in &amp; attendance</span>
                                    <span class="stage-badge bg-success text-white">{{ number_format($eventParticipation['attendance']) }}</span>
                                </li>
                                <li class="participation-stage-item">
                                    <span><i class="fas fa-star text-warning mr-2"></i>Follow up interest</span>
                                    <span class="stage-badge bg-warning text-dark font-weight-bold">{{ number_format($eventParticipation['interest']) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 3: Outcomes (Right) -->
                    <div class="col-lg-5 col-md-4 d-flex flex-column justify-content-between">
                        <!-- Annual attendance Box -->
                        <div class="card-attendance-outcome">
                            <div class="d-flex justify-content-between align-items-center outcome-title mb-2">
                                <span><i class="fas fa-calendar-check mr-2 text-info"></i>Annual attendance</span>
                                <small class="text-muted" style="color: #94a3b8 !important;">Total Aktivitas</small>
                            </div>
                            <div class="outcome-metrics-grid">
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-info">{{ number_format($annualAttendance['entries']) }}</div>
                                    <div class="outcome-metric-label">Entries</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-white">{{ number_format($annualAttendance['unique_people']) }}</div>
                                    <div class="outcome-metric-label">Unique people</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-success">{{ number_format($annualAttendance['verified_members']) }}</div>
                                    <div class="outcome-metric-label">Verified members</div>
                                </div>
                            </div>
                        </div>

                        <!-- Member conversion Box -->
                        <div class="card-conversion-outcome">
                            <div class="d-flex justify-content-between align-items-center outcome-title mb-2">
                                <span><i class="fas fa-user-check mr-2"></i>Member conversion</span>
                                <span class="badge badge-light font-weight-bold text-dark">{{ $annualConversion['rate'] }}% Rate</span>
                            </div>
                            <div class="outcome-metrics-grid">
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-warning">{{ number_format($annualConversion['interest']) }}</div>
                                    <div class="outcome-metric-label">Stated Interest</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value text-white">{{ number_format($annualConversion['pending']) }}</div>
                                    <div class="outcome-metric-label">Pending Review</div>
                                </div>
                                <div class="outcome-metric-box">
                                    <div class="outcome-metric-value" style="color: #ffffff;">{{ number_format($annualConversion['active']) }}</div>
                                    <div class="outcome-metric-label">Active Member</div>
                                </div>
                            </div>
                            <div class="conversion-explainer">
                                <i class="fas fa-check mr-1"></i> Consent plus direct DMC engagement or stated interest.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Internal Operating Guideline Rule Banner -->
                <div class="guideline-banner">
                    <div class="guideline-quote">
                        <i class="fas fa-shield-alt text-warning mr-2"></i>
                        Owned events may create a pending member when consent and minimum data are present. Supporting events convert only after opt in or direct engagement.
                    </div>
                    <div class="guideline-sub">
                        DJAKARTA MINING CLUB &bull; INTERNAL OPERATING GUIDELINE &bull; {{ $selectedYear === 'all' ? '2026' : $selectedYear }}
                    </div>
                </div>
            </div>

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
