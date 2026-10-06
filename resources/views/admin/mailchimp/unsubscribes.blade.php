@extends('layouts.inspire.master')

@section('content')
<style>
    .mc-badge-active {
        background-color: #28a745;
        color: #ffffff;
        font-weight: 600;
        font-size: 11px;
    }
    .mc-badge-pending {
        background-color: #ffc107;
        color: #212529;
        font-weight: 600;
        font-size: 11px;
    }
    .mc-badge-other {
        background-color: #6c757d;
        color: #ffffff;
        font-weight: 600;
        font-size: 11px;
    }
    .mc-badge-nonmember {
        background-color: #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 11px;
    }
    .mc-rating-star {
        color: #f59e0b;
        font-size: 11px;
    }
    .mc-rating-star-empty {
        color: #cbd5e1;
        font-size: 11px;
    }
    .kpi-unsub-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 3px 12px rgba(0,0,0,0.04);
        transition: transform 0.2s;
    }
    .kpi-unsub-card:hover {
        transform: translateY(-2px);
    }
</style>

<div class="content-wrapper">
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
            <div>
                <h1><i class="fab fa-mailchimp text-warning mr-2"></i>Mailchimp Unsubscribe List</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('users') }}">Members DMC</a></div>
                    <div class="breadcrumb-item active">Mailchimp Unsubscribes</div>
                </div>
            </div>

            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->query(), ['refresh' => 1])) }}"
                   class="btn btn-sm btn-outline-primary"
                   title="Tarik data terbaru langsung dari Mailchimp API">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Data
                </a>

                @if ($configured && empty($errorMessage))
                    <a href="{{ route('admin.mailchimp_unsubscribes.export', request()->query()) }}"
                       class="btn btn-sm btn-success"
                       title="Download data unsubscribed ke format Excel">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>

                    @if (!empty($server) && !empty($listId))
                        <a href="https://{{ $server }}.admin.mailchimp.com/lists/members?id={{ $listId }}"
                           target="_blank"
                           class="btn btn-sm btn-light"
                           title="Buka Audience di Dashboard Mailchimp">
                            <i class="fas fa-external-link-alt mr-1"></i> Mailchimp Web
                        </a>
                    @endif
                @endif
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

            @if (!empty($errorMessage))
                <div class="alert alert-warning alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>×</span></button>
                        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errorMessage }}
                    </div>
                </div>
            @endif

            <!-- 1. STATISTIC CARDS -->
            @if ($configured && empty($errorMessage))
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 col-12 mb-3">
                        <div class="card kpi-unsub-card mb-0">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small text-uppercase font-weight-bold">Total Unsubscribed</div>
                                        <div class="h3 font-weight-bold mb-0 text-danger">{{ number_format($stats['total_unsubscribed'] ?? 0) }}</div>
                                    </div>
                                    <div class="rounded-circle bg-danger text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    @if ($hasDateFilter)
                                        <span class="badge badge-warning text-dark"><i class="fas fa-filter mr-1"></i>Periode Terfilter</span>
                                        <span class="ml-1">dari {{ number_format($stats['all_time_unsubscribed'] ?? 0) }} all-time</span>
                                    @else
                                        Dari {{ number_format(($stats['member_count'] ?? 0) + ($stats['total_unsubscribed'] ?? 0)) }} total kontak
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12 mb-3">
                        <div class="card kpi-unsub-card mb-0 border-left border-warning" style="border-left-width: 4px !important;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small text-uppercase font-weight-bold">Member Aktif Unsubscribed</div>
                                        <div class="h3 font-weight-bold mb-0 text-warning">{{ number_format($stats['active_members_count'] ?? 0) }}</div>
                                    </div>
                                    <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    Member aktif yang tidak menerima email
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12 mb-3">
                        <div class="card kpi-unsub-card mb-0">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small text-uppercase font-weight-bold">User DMC Lainnya</div>
                                        <div class="h3 font-weight-bold mb-0 text-secondary">{{ number_format($stats['other_dmc_count'] ?? 0) }}</div>
                                    </div>
                                    <div class="rounded-circle bg-secondary text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    Pending, inactive, atau non-aktif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12 mb-3">
                        <div class="card kpi-unsub-card mb-0">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small text-uppercase font-weight-bold">Non-Member / Eksternal</div>
                                        <div class="h3 font-weight-bold mb-0 text-info">{{ number_format($stats['non_member_count'] ?? 0) }}</div>
                                    </div>
                                    <div class="rounded-circle bg-info text-white p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    Kontak luar / newsletter publik
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 2. MAIN TABLE CARD -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <div>
                        <h4 class="mb-0">Daftar Kontak Berhenti Berlangganan</h4>
                        <small class="text-muted">Data otomatis sinkron dengan audience Mailchimp dan database member DMC</small>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->query(), ['filter' => 'all', 'page' => 1])) }}"
                           class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Semua ({{ $stats['total_unsubscribed'] ?? 0 }})
                        </a>
                        <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->query(), ['filter' => 'active_member', 'page' => 1])) }}"
                           class="btn btn-sm {{ $filter === 'active_member' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-warning' }}">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Member Aktif DMC ({{ $stats['active_members_count'] ?? 0 }})
                        </a>
                        <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->query(), ['filter' => 'other_dmc', 'page' => 1])) }}"
                           class="btn btn-sm {{ $filter === 'other_dmc' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            User DMC Lainnya ({{ $stats['other_dmc_count'] ?? 0 }})
                        </a>
                        <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->query(), ['filter' => 'non_member', 'page' => 1])) }}"
                           class="btn btn-sm {{ $filter === 'non_member' ? 'btn-primary' : 'btn-outline-info' }}">
                            Non-Member ({{ $stats['non_member_count'] ?? 0 }})
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- FILTER PANEL: DATE RANGE, PRESETS & SEARCH -->
                    <div class="bg-light p-3 rounded mb-3 border">
                        <form method="GET" action="{{ route('admin.mailchimp_unsubscribes.index') }}">
                            <input type="hidden" name="filter" value="{{ $filter }}">

                            <!-- Quick Presets -->
                            <div class="d-flex align-items-center flex-wrap mb-3 pb-2 border-bottom" style="gap: 6px;">
                                <span class="small font-weight-bold text-muted mr-2">
                                    <i class="fas fa-calendar-alt text-primary mr-1"></i> Pilihan Cepat:
                                </span>
                                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->except(['date_from', 'date_to', 'month', 'year', 'preset', 'page']), ['filter' => $filter])) }}"
                                   class="btn btn-xs {{ (!$hasDateFilter) ? 'btn-dark font-weight-bold' : 'btn-outline-secondary' }}" style="padding: 3px 8px; font-size: 11.5px;">
                                    Semua Waktu
                                </a>
                                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->except(['date_from', 'date_to', 'month', 'year', 'page']), ['preset' => 'today', 'filter' => $filter])) }}"
                                   class="btn btn-xs {{ $preset === 'today' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}" style="padding: 3px 8px; font-size: 11.5px;">
                                    Hari Ini
                                </a>
                                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->except(['date_from', 'date_to', 'month', 'year', 'page']), ['preset' => 'last_7_days', 'filter' => $filter])) }}"
                                   class="btn btn-xs {{ $preset === 'last_7_days' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}" style="padding: 3px 8px; font-size: 11.5px;">
                                    7 Hari Terakhir
                                </a>
                                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->except(['date_from', 'date_to', 'month', 'year', 'page']), ['preset' => 'last_30_days', 'filter' => $filter])) }}"
                                   class="btn btn-xs {{ $preset === 'last_30_days' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}" style="padding: 3px 8px; font-size: 11.5px;">
                                    30 Hari Terakhir
                                </a>
                                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->except(['date_from', 'date_to', 'month', 'year', 'page']), ['preset' => 'this_month', 'filter' => $filter])) }}"
                                   class="btn btn-xs {{ $preset === 'this_month' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}" style="padding: 3px 8px; font-size: 11.5px;">
                                    Bulan Ini
                                </a>
                                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->except(['date_from', 'date_to', 'month', 'year', 'page']), ['preset' => 'last_month', 'filter' => $filter])) }}"
                                   class="btn btn-xs {{ $preset === 'last_month' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}" style="padding: 3px 8px; font-size: 11.5px;">
                                    Bulan Lalu
                                </a>
                                <a href="{{ route('admin.mailchimp_unsubscribes.index', array_merge(request()->except(['date_from', 'date_to', 'month', 'year', 'page']), ['preset' => 'this_year', 'filter' => $filter])) }}"
                                   class="btn btn-xs {{ $preset === 'this_year' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}" style="padding: 3px 8px; font-size: 11.5px;">
                                    Tahun Ini
                                </a>
                            </div>

                            <!-- Form Controls: Range, Month, Year, Campaign, Search -->
                            <div class="form-row align-items-end">
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                                    <label class="mb-1 small text-muted font-weight-bold">Dari Tanggal</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm"
                                           value="{{ $dateFrom }}">
                                </div>

                                <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                                    <label class="mb-1 small text-muted font-weight-bold">Sampai Tanggal</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm"
                                           value="{{ $dateTo }}">
                                </div>

                                <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                                    <label class="mb-1 small text-muted font-weight-bold">Bulan</label>
                                    <select name="month" class="form-control form-control-sm">
                                        <option value="">Semua Bulan</option>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ (string) $month === (string) $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-lg-1 col-md-3 col-sm-6 mb-2">
                                    <label class="mb-1 small text-muted font-weight-bold">Tahun</label>
                                    <select name="year" class="form-control form-control-sm">
                                        <option value="">Semua</option>
                                        @foreach ($availableYears as $y)
                                            <option value="{{ $y }}" {{ (string) $year === (string) $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                                    <label class="mb-1 small text-muted font-weight-bold">Email / Campaign Asal</label>
                                    <select name="campaign" class="form-control form-control-sm">
                                        <option value="">Semua Email / Campaign</option>
                                        <option value="__admin__" {{ $campaign === '__admin__' ? 'selected' : '' }}>[Admin] Unsubscribed by Admin</option>
                                        @foreach ($availableCampaigns as $cName)
                                            <option value="{{ $cName }}" {{ $campaign === $cName ? 'selected' : '' }}>
                                                {{ $cName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-6 col-sm-12 mb-2">
                                    <label class="mb-1 small text-muted font-weight-bold">Kata Kunci</label>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                           placeholder="Email, nama, PT..."
                                           value="{{ $search }}">
                                </div>

                                <div class="col-lg-12 col-12 mb-1 text-right">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Terapkan Filter
                                    </button>
                                    @if ($hasDateFilter || $search !== '' || $campaign !== '')
                                        <a href="{{ route('admin.mailchimp_unsubscribes.index', ['filter' => $filter, 'per_page' => $perPage]) }}"
                                           class="btn btn-sm btn-outline-secondary ml-1" title="Reset filter">
                                            <i class="fas fa-times mr-1"></i> Reset
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- ACTIVE FILTER BANNER -->
                    @if ($hasDateFilter || $search !== '' || $campaign !== '')
                        <div class="alert alert-info alert-dismissible show fade py-2 mb-3">
                            <div class="alert-body small d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                                <div>
                                    <i class="fas fa-filter mr-1"></i>
                                    <strong>Filter Aktif:</strong>
                                    @if (!empty($dateFrom))
                                        Dari <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}</strong>
                                    @endif
                                    @if (!empty($dateTo))
                                        sampai <strong>{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</strong>
                                    @endif
                                    @if (!empty($month))
                                        &bull; Bulan: <strong>{{ \Carbon\Carbon::create()->month((int) $month)->format('F') }}</strong>
                                    @endif
                                    @if (!empty($year))
                                        &bull; Tahun: <strong>{{ $year }}</strong>
                                    @endif
                                    @if ($preset && empty($dateFrom) && empty($dateTo) && empty($month) && empty($year))
                                        &bull; Preset: <strong>{{ ucfirst(str_replace('_', ' ', $preset)) }}</strong>
                                    @endif
                                    @if ($campaign !== '')
                                        &bull; Email/Campaign: <strong>{{ $campaign === '__admin__' ? 'Unsubscribed by Admin' : $campaign }}</strong>
                                    @endif
                                    @if ($search !== '')
                                        &bull; Kata Kunci: "<strong>{{ $search }}</strong>"
                                    @endif
                                    <span class="badge badge-light ml-2 font-weight-bold">{{ $contacts->total() }} kontak ditemukan</span>
                                </div>
                                <div>
                                    <a href="{{ route('admin.mailchimp_unsubscribes.index', ['filter' => $filter, 'per_page' => $perPage]) }}"
                                       class="btn btn-xs btn-outline-dark" style="font-size: 11px;">
                                        <i class="fas fa-times mr-1"></i> Reset Semua Filter
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TABLE ACTION BAR (PER PAGE & INFO) -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $contacts->total() }}</strong> kontak unsubscribed
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <span class="text-muted small">Tampilkan:</span>
                            <form method="GET" action="{{ route('admin.mailchimp_unsubscribes.index') }}" class="m-0">
                                @foreach (request()->except(['per_page', 'page']) as $k => $v)
                                    @if (!empty($v))
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endif
                                @endforeach
                                <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()" style="width: 80px;">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" style="font-size: 12.5px;">
                            <thead class="thead-light">
                                <tr>
                                    <th width="30px">No</th>
                                    <th>Email &amp; Nama</th>
                                    <th>Status di Database DMC</th>
                                    <th>Perusahaan &amp; Jabatan</th>
                                    <th width="140px">Waktu Unsubscribe</th>
                                    <th width="230px">Email / Campaign Saat Unsubscribe</th>
                                    <th width="150px">Alasan (Reason)</th>
                                    <th width="75px" class="text-center">Rating</th>
                                    <th width="120px" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contacts as $idx => $c)
                                    <tr>
                                        <td>{{ $contacts->firstItem() + $idx }}</td>
                                        <td>
                                            <div class="font-weight-bold" style="color: #0e273c; font-size: 13px;">
                                                {{ $c->email }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $c->full_name }}
                                                @if (!empty($c->phone) && $c->phone !== '-')
                                                    &bull; <i class="fas fa-phone-alt mr-1"></i>{{ $c->phone }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($c->is_dmc_user)
                                                @if ($c->dmc_status === 'active')
                                                    <span class="badge mc-badge-active px-2 py-1">
                                                        <i class="fas fa-check-circle mr-1"></i>Active Member
                                                    </span>
                                                @elseif ($c->dmc_status === 'pending')
                                                    <span class="badge mc-badge-pending px-2 py-1">
                                                        <i class="fas fa-clock mr-1"></i>Pending Review
                                                    </span>
                                                @else
                                                    <span class="badge mc-badge-other px-2 py-1">
                                                        {{ ucfirst($c->dmc_status ?: 'Registered') }}
                                                    </span>
                                                @endif

                                                @if (!empty($c->dmc_uname))
                                                    <div class="small font-weight-bold text-muted mt-1">
                                                        ID: {{ $c->dmc_uname }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge mc-badge-nonmember px-2 py-1">
                                                    <i class="fas fa-minus-circle mr-1"></i>Non-Member
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ $c->company }}</div>
                                            <div class="text-muted small">{{ $c->job_title }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $c->unsubscribed_at }}</div>
                                            <small class="text-muted">{{ $c->unsubscribed_human }}</small>
                                        </td>
                                        <td>
                                            @if (!empty($c->unsub_campaign_title))
                                                <div class="font-weight-bold" style="color: #0e273c; font-size: 12px; line-height: 1.35;">
                                                    <i class="fas fa-envelope-open-text text-danger mr-1"></i>{{ $c->unsub_campaign_title }}
                                                </div>
                                                <div class="mt-1 d-flex align-items-center flex-wrap" style="gap: 4px;">
                                                    @if (!empty($c->unsub_campaign_id))
                                                        <span class="badge badge-light border text-muted" style="font-size: 10px;">
                                                            CID: {{ $c->unsub_campaign_id }}
                                                        </span>
                                                    @endif
                                                    <button type="button" class="btn btn-xs btn-outline-info btn-view-activity"
                                                            data-hash="{{ $c->hash }}" data-email="{{ $c->email }}"
                                                            title="Lihat riwayat aktivitas email kontak ini"
                                                            style="padding: 1px 6px; font-size: 10.5px;">
                                                        <i class="fas fa-history mr-1"></i>Log ({{ $c->activity_count }})
                                                    </button>
                                                </div>
                                            @elseif ($c->is_admin_unsub)
                                                <span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">
                                                    <i class="fas fa-user-shield mr-1"></i>Unsubscribed by Admin
                                                </span>
                                                <div class="mt-1">
                                                    <button type="button" class="btn btn-xs btn-outline-info btn-view-activity"
                                                            data-hash="{{ $c->hash }}" data-email="{{ $c->email }}"
                                                            style="padding: 1px 6px; font-size: 10.5px;">
                                                        <i class="fas fa-history mr-1"></i>Lihat Log
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted small">&ndash; Direct / Web Portal &ndash;</span>
                                                @if ($c->activity_count > 0)
                                                    <div class="mt-1">
                                                        <button type="button" class="btn btn-xs btn-outline-info btn-view-activity"
                                                                data-hash="{{ $c->hash }}" data-email="{{ $c->email }}"
                                                                style="padding: 1px 6px; font-size: 10.5px;">
                                                            <i class="fas fa-history mr-1"></i>Log ({{ $c->activity_count }})
                                                        </button>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($c->reason !== 'None given')
                                                <span class="badge badge-light border text-wrap text-left p-1" style="font-size: 11px; max-width: 170px;">
                                                    <i class="fas fa-quote-left text-muted mr-1"></i>{{ $c->reason }}
                                                </span>
                                            @else
                                                <span class="text-muted small">&ndash; Tidak ada alasan &ndash;</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $c->rating ? 'mc-rating-star' : 'mc-rating-star-empty' }}"></i>
                                            @endfor
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                @if ($c->is_dmc_user)
                                                    <a href="{{ route('users') }}?search={{ urlencode($c->email) }}"
                                                       class="btn btn-sm btn-info"
                                                       title="Lihat profil member di DMC"
                                                       target="_blank">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                @endif

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info btn-view-activity"
                                                        data-hash="{{ $c->hash }}"
                                                        data-email="{{ $c->email }}"
                                                        title="Lihat riwayat aktivitas email">
                                                    <i class="fas fa-history"></i>
                                                </button>

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success btn-resubscribe"
                                                        data-email="{{ $c->email }}"
                                                        data-name="{{ $c->full_name }}"
                                                        title="Subscribe ulang ke Mailchimp">
                                                    <i class="fas fa-envelope-open-text"></i>
                                                </button>

                                                @if (!empty($c->web_id))
                                                    <a href="https://{{ $server }}.admin.mailchimp.com/lists/members/view?id={{ $c->web_id }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-light"
                                                       title="Buka kontak di Mailchimp">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                                            Tidak ada kontak unsubscribed yang cocok dengan filter atau pencarian.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($contacts->hasPages())
                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                            <div class="text-muted small mb-2 mb-md-0">
                                Menampilkan {{ $contacts->firstItem() }} sampai {{ $contacts->lastItem() }} dari {{ $contacts->total() }} kontak
                            </div>
                            <div>
                                {{ $contacts->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </section>
</div>

<!-- MODAL RIWAYAT AKTIVITAS EMAIL -->
<div class="modal fade" id="modalActivity" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title"><i class="fas fa-history text-info mr-2"></i>Riwayat Aktivitas Email Mailchimp</h5>
                    <small class="text-muted" id="activityModalSubtitle">Memuat data...</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div id="activityLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                    <p class="text-muted small mb-0">Mengambil log aktivitas dari server Mailchimp...</p>
                </div>

                <div id="activityError" class="alert alert-danger d-none"></div>

                <div id="activityTimeline" class="d-none">
                    <div class="alert alert-light border small py-2 mb-3">
                        <i class="fas fa-info-circle mr-1 text-primary"></i>
                        Urutan aktivitas interaksi email kontak dari yang terbaru hingga terlama.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover border mb-0" style="font-size: 12px;">
                            <thead class="thead-light">
                                <tr>
                                    <th width="125px">Aktivitas</th>
                                    <th>Nama Email / Campaign</th>
                                    <th width="165px">Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="activityTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RESUBSCRIBE CONFIRMATION -->
<div class="modal fade" id="modalResubscribe" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.mailchimp_unsubscribes.resubscribe') }}">
            @csrf
            <input type="hidden" name="email" id="resubscribeEmail">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-envelope-open-text text-success mr-2"></i>Re-subscribe ke Mailchimp</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin men-subscribe kembali kontak ini ke audience Mailchimp DMC?</p>
                    <div class="alert alert-light border">
                        <div class="font-weight-bold" id="resubscribeTargetEmail"></div>
                        <small class="text-muted" id="resubscribeTargetName"></small>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pastikan kontak telah memberikan izin atau meminta untuk menerima email newsletter/undangan kembali.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Re-subscribe Sekarang</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('bottom')
<script>
$(document).ready(function() {
    // Resubscribe confirmation modal
    $('.btn-resubscribe').on('click', function() {
        var email = $(this).data('email');
        var name = $(this).data('name');

        $('#resubscribeEmail').val(email);
        $('#resubscribeTargetEmail').text(email);
        $('#resubscribeTargetName').text(name || '-');

        $('#modalResubscribe').modal('show');
    });

    // View email activity log modal
    $(document).on('click', '.btn-view-activity', function() {
        var hash = $(this).data('hash');
        var email = $(this).data('email');

        $('#activityModalSubtitle').text('Email: ' + email);
        $('#activityLoading').removeClass('d-none');
        $('#activityTimeline').addClass('d-none');
        $('#activityError').addClass('d-none').text('');
        $('#activityTableBody').empty();

        $('#modalActivity').modal('show');

        var url = "{{ url('admin/mailchimp/unsubscribes') }}/" + hash + "/activity?email=" + encodeURIComponent(email);

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                $('#activityLoading').addClass('d-none');

                if (!res.success || !res.activities || res.activities.length === 0) {
                    $('#activityTableBody').html('<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada catatan aktivitas email untuk kontak ini.</td></tr>');
                    $('#activityTimeline').removeClass('d-none');
                    return;
                }

                var html = '';
                $.each(res.activities, function(i, act) {
                    var badge = '';
                    var action = (act.action || '').toLowerCase();

                    if (action === 'unsub') {
                        badge = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-user-slash mr-1"></i>Unsubscribed</span>';
                    } else if (action === 'open') {
                        badge = '<span class="badge badge-primary px-2 py-1"><i class="fas fa-envelope-open mr-1"></i>Opened</span>';
                    } else if (action === 'click') {
                        badge = '<span class="badge badge-success px-2 py-1"><i class="fas fa-mouse-pointer mr-1"></i>Clicked</span>';
                    } else if (action === 'sent') {
                        badge = '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-paper-plane mr-1"></i>Sent</span>';
                    } else if (action === 'bounce') {
                        badge = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i>Bounced</span>';
                    } else {
                        badge = '<span class="badge badge-light border px-2 py-1">' + (act.action || '-') + '</span>';
                    }

                    var title = act.title || '-';
                    var extra = '';
                    if (act.campaign_id) {
                        extra += ' <span class="badge badge-light border text-muted" style="font-size:10px;">ID: ' + act.campaign_id + '</span>';
                    }
                    if (act.url) {
                        extra += '<div class="small text-truncate mt-1" style="max-width:350px;"><a href="' + act.url + '" target="_blank" class="text-muted"><i class="fas fa-link mr-1"></i>' + act.url + '</a></div>';
                    }

                    var rowClass = (action === 'unsub') ? 'table-danger' : '';

                    html += '<tr class="' + rowClass + '">';
                    html += '  <td>' + badge + '</td>';
                    html += '  <td><div class="font-weight-bold">' + title + '</div>' + extra + '</td>';
                    html += '  <td><div>' + (act.time || '-') + '</div><small class="text-muted">' + (act.time_human || '') + '</small></td>';
                    html += '</tr>';
                });

                $('#activityTableBody').html(html);
                $('#activityTimeline').removeClass('d-none');
            },
            error: function(xhr) {
                $('#activityLoading').addClass('d-none');
                var msg = 'Gagal memuat aktivitas.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#activityError').removeClass('d-none').text(msg);
            }
        });
    });
});
</script>
@endpush
