{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Users Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Users Management</div>
                </div>
            </div>

            <div class="section-body">

                {{-- Flash alerts --}}
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
                @if ($errors->any())
                    <div class="alert alert-warning alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            <strong>Whoops!</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Dynamic AJAX alert area --}}
                <div id="alert-area" class="mb-2"></div>

                {{-- ====== STATS CARDS ====== --}}
                <div class="row">

                    {{-- Active Members --}}
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="{{ url('admin/users?status_member=active') }}" class="text-decoration-none">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-success"><i class="fas fa-user-check"></i></div>
                                <div class="card-wrap">
                                    <div class="card-header"><h4>Active Members</h4></div>
                                    <div class="card-body">{{ $countActiveMember }}</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Pending Verification --}}
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
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

                    {{-- New This Month --}}
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="{{ url('admin/users?filter=this_month') }}" class="text-decoration-none">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-primary"><i class="fas fa-user-plus"></i></div>
                                <div class="card-wrap">
                                    <div class="card-header"><h4>New This Month</h4></div>
                                    <div class="card-body">{{ $countNewThisMonth }}</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Unregistered --}}
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="{{ url('admin/users?filter=unregist') }}" class="text-decoration-none">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-danger"><i class="fas fa-user-times"></i></div>
                                <div class="card-wrap">
                                    <div class="card-header"><h4>Unregistered</h4></div>
                                    <div class="card-body">{{ $countUnRegistered }}</div>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>{{-- /stats row --}}

                {{-- ====== MAIN TABLE CARD ====== --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            @if (request('status_member') === 'active')
                                <span class="text-success"><i class="fas fa-user-check mr-1"></i>Active Members</span>
                            @elseif (request('status_member') === 'pending')
                                <span class="text-warning"><i class="fas fa-user-clock mr-1"></i>Pending Verification</span>
                            @elseif (request('filter') === 'this_month')
                                <span class="text-primary"><i class="fas fa-user-plus mr-1"></i>New Members — {{ now()->format('F Y') }}</span>
                            @elseif (request('filter') === 'unregist')
                                <span class="text-danger"><i class="fas fa-user-times mr-1"></i>Unregistered</span>
                            @else
                                <i class="fas fa-users mr-1"></i>All Members
                            @endif
                        </h4>

                        {{-- Email/Phone verify summary (secondary info) --}}
                        @if (request('filter') !== 'unregist')
                            <div class="d-none d-md-flex align-items-center" style="gap:6px; font-size:12px;">
                                <span class="text-muted mr-1">Email/Phone verify:</span>
                                <span class="badge badge-primary" title="Email only verified">
                                    <i class="fas fa-envelope mr-1"></i>{{ $countVerifyEmail }}
                                </span>
                                <span class="badge badge-info" title="Phone only verified">
                                    <i class="fas fa-phone mr-1"></i>{{ $countVerifyPhone }}
                                </span>
                                <span class="badge badge-success" title="Both verified">
                                    <i class="fas fa-check-double mr-1"></i>{{ $countDoubleVerify }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">

                        {{-- Quick filter tabs --}}
                        <div class="mb-3">
                            <div class="d-flex flex-wrap" style="gap:6px;">
                                <a href="{{ url('admin/users') }}"
                                    class="btn btn-sm {{ !request('filter') && !request('status_member') ? 'btn-dark' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-list mr-1"></i> All
                                </a>
                                <a href="{{ url('admin/users?status_member=active') }}"
                                    class="btn btn-sm {{ request('status_member') === 'active' ? 'btn-success' : 'btn-outline-success' }}">
                                    <i class="fas fa-user-check mr-1"></i> Active
                                    <span class="badge badge-light ml-1">{{ $countActiveMember }}</span>
                                </a>
                                <a href="{{ url('admin/users?status_member=pending') }}"
                                    class="btn btn-sm {{ request('status_member') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    <i class="fas fa-user-clock mr-1"></i> Pending Verification
                                    <span class="badge badge-light ml-1">{{ $countPendingMember }}</span>
                                </a>
                                <a href="{{ url('admin/users?filter=this_month') }}"
                                    class="btn btn-sm {{ request('filter') === 'this_month' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    <i class="fas fa-calendar-alt mr-1"></i> New This Month
                                    <span class="badge badge-light ml-1">{{ $countNewThisMonth }}</span>
                                </a>
                                <a href="{{ url('admin/users?filter=unregist') }}"
                                    class="btn btn-sm {{ request('filter') === 'unregist' ? 'btn-danger' : 'btn-outline-danger' }}">
                                    <i class="fas fa-user-times mr-1"></i> Unregistered
                                    <span class="badge badge-light ml-1">{{ $countUnRegistered }}</span>
                                </a>
                            </div>
                        </div>

                        {{-- Date filter + actions --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 border-top pt-3">
                            <form action="{{ url('admin/users') }}" method="GET"
                                class="d-flex flex-wrap align-items-end" style="gap:0;">

                                {{-- Preserve active tab when filtering by date --}}
                                @if (request('status_member'))
                                    <input type="hidden" name="status_member" value="{{ request('status_member') }}">
                                @endif
                                @if (request('filter'))
                                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                                @endif

                                <div class="form-group mr-2 mb-2">
                                    <label class="mb-1 small text-muted">From Date</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm"
                                        value="{{ request('date_from') }}">
                                </div>

                                <div class="form-group mr-2 mb-2">
                                    <label class="mb-1 small text-muted">To Date</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm"
                                        value="{{ request('date_to') }}">
                                </div>

                                <div class="form-group mr-2 mb-2">
                                    <label class="mb-1 small text-muted">Month</label>
                                    <select name="month" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="form-group mr-2 mb-2">
                                    <label class="mb-1 small text-muted">Year</label>
                                    <select name="year" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        @for ($y = now()->year; $y >= 2025; $y--)
                                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="form-group mr-2 mb-2">
                                    <label class="mb-1 small text-muted">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-filter mr-1"></i> Filter
                                        </button>
                                        <a href="{{ url('admin/users') }}" class="btn btn-sm btn-outline-secondary ml-1">
                                            <i class="fas fa-times mr-1"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                    data-target="#example">
                                    <i class="fas fa-file-import mr-1"></i> Import Excel
                                </button>
                            </div>
                        </div>

                        {{-- Applied filter info --}}
                        @if (request('date_from') || request('date_to') || request('month') || request('year'))
                            <div class="alert alert-info alert-dismissible show fade py-2">
                                <div class="alert-body small">
                                    <button class="close" data-dismiss="alert"><span>×</span></button>
                                    <i class="fas fa-filter mr-1"></i>
                                    Filter aktif:
                                    @if (request('date_from')) From <strong>{{ request('date_from') }}</strong> @endif
                                    @if (request('date_to')) &nbsp;to <strong>{{ request('date_to') }}</strong> @endif
                                    @if (request('month')) &nbsp;| Month: <strong>{{ \Carbon\Carbon::create()->month((int) request('month'))->format('F') }}</strong> @endif
                                    @if (request('year')) &nbsp;| Year: <strong>{{ request('year') }}</strong> @endif
                                </div>
                            </div>
                        @endif

                        {{-- ====== TABLE ====== --}}
                        <div class="table-responsive">

                            @if (request('filter') === 'unregist')
                            {{-- ---- TABEL UNREGISTERED (MemberModel, tidak punya user_id/tier/verify) ---- --}}
                            <table id="laravel_crud" class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10px">No</th>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Job Title</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Category</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach ($list as $post)
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td class="text-nowrap">
                                                {{ date('d M Y', strtotime($post->created_at)) }}<br>
                                                <small class="text-muted">{{ date('H:i', strtotime($post->created_at)) }}</small>
                                            </td>
                                            <td>{{ $post->name }}</td>
                                            <td>{{ $post->company_name }}</td>
                                            <td>{{ $post->job_title }}</td>
                                            <td><a href="mailto:{{ $post->email }}">{{ $post->email }}</a></td>
                                            <td class="text-nowrap">{{ $post->fullphone ?? $post->phone }}</td>
                                            <td>{{ $post->address }}</td>
                                            <td>{{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}</td>
                                            <td>
                                                <a href="{{ route('admin.member.export', $post->id) }}"
                                                    class="btn btn-xs btn-success"
                                                    onclick="return confirm('Export member ini ke Users?')">
                                                    <i class="fas fa-file-export"></i> Export to Member
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @else
                            {{-- ---- TABEL MEMBER (User model, punya user_id / tier / status_member) ---- --}}
                            <table id="laravel_crud" class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10px">No</th>
                                        <th>Date Register</th>
                                        <th>Name</th>
                                        <th width="140px">Tier</th>
                                        <th width="150px">
                                            Status Member
                                            <i class="fas fa-info-circle text-muted ml-1"
                                                title="Active = sudah diverifikasi admin. Pending = belum diverifikasi."
                                                data-toggle="tooltip"></i>
                                        </th>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Office</th>
                                        <th>Address</th>
                                        <th>Website</th>
                                        <th>Category</th>
                                        <th width="180px">
                                            CCI &amp; Sponsorship
                                            <i class="fas fa-info-circle text-muted ml-1"
                                                title="CCI: anggota CCI. Open to Sponsorship: member bersedia menerima penawaran paket sponsorship."
                                                data-toggle="tooltip"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach ($list as $post)
                                        @php $isActive = strtolower($post->status_member ?? '') === 'active'; @endphp
                                        <tr id="row_{{ $post->user_id }}"
                                            style="{{ !$isActive ? 'background-color:#fffbee;' : '' }}">

                                            <td>{{ $no++ }}</td>

                                            <td class="text-nowrap">
                                                {{ date('d M Y', strtotime($post->user_created_at ?? $post->created_at)) }}<br>
                                                <small class="text-muted">{{ date('H:i', strtotime($post->user_created_at ?? $post->created_at)) }}</small>
                                            </td>

                                            <td>{{ $post->name }}</td>

                                            {{-- TIER --}}
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <select class="form-control form-control-sm user-tier-select"
                                                        data-url="{{ route('users.update.tier', $post->user_id) }}"
                                                        style="max-width:110px;">
                                                        @php
                                                            $tier = strtolower((string) ($post->tier ?? 'reguler'));
                                                            if (!in_array($tier, ['reguler', 'black'])) $tier = 'reguler';
                                                        @endphp
                                                        <option value="reguler" {{ $tier === 'reguler' ? 'selected' : '' }}>Reguler</option>
                                                        <option value="black"   {{ $tier === 'black'   ? 'selected' : '' }}>Black</option>
                                                    </select>
                                                    <span class="ml-1 badge badge-light tier-status" style="font-size:10px;">Saved</span>
                                                </div>
                                            </td>

                                            {{-- STATUS MEMBER --}}
                                            <td>
                                                <div class="d-flex flex-column align-items-start" style="gap:4px;">
                                                    @if ($isActive)
                                                        <span class="badge badge-success member-status-badge">
                                                            <i class="fas fa-check mr-1"></i>Active
                                                        </span>
                                                        <button type="button"
                                                            class="btn btn-xs btn-success btn-verify-member"
                                                            data-url="{{ route('users.verify', $post->user_id) }}"
                                                            disabled>
                                                            <i class="fas fa-check"></i> Verified
                                                        </button>
                                                    @else
                                                        <span class="badge badge-warning member-status-badge">
                                                            <i class="fas fa-clock mr-1"></i>Pending
                                                        </span>
                                                        <button type="button"
                                                            class="btn btn-xs btn-primary btn-verify-member"
                                                            data-url="{{ route('users.verify', $post->user_id) }}"
                                                            title="Verifikasi → status Active + auto import ke Mailchimp">
                                                            <i class="fas fa-shield-alt"></i> Verify
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>

                                            <td>{{ $post->job_title }}</td>
                                            <td>{{ $post->company_name }}</td>
                                            <td><a href="mailto:{{ $post->email }}">{{ $post->email }}</a></td>
                                            <td class="text-nowrap">{{ $post->fullphone ?? $post->phone }}</td>
                                            <td class="text-nowrap">{{ $post->office_number ?? $post->full_office_number }}</td>
                                            <td>{{ $post->address }}</td>
                                            <td>
                                                @if ($post->company_website)
                                                    <a href="{{ $post->company_website }}" target="_blank" rel="noopener">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}</td>

                                            {{-- CCI & Sponsorship --}}
                                            <td>
                                                <div class="d-flex flex-column align-items-start" style="gap:4px;">
                                                    @if ($post->cci)
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-building mr-1"></i>CCI
                                                        </span>
                                                    @else
                                                        <span class="badge badge-light text-muted" style="font-size:10px;">CCI: No</span>
                                                    @endif

                                                    @if ($post->explore)
                                                        <span class="badge badge-warning"
                                                            title="Member bersedia menerima penawaran paket sponsorship"
                                                            data-toggle="tooltip">
                                                            <i class="fas fa-star mr-1"></i>Open to Sponsorship
                                                        </span>
                                                    @else
                                                        <span class="badge badge-light text-muted" style="font-size:10px;">Sponsorship: No</span>
                                                    @endif

                                                    <button type="button"
                                                        class="btn btn-xs btn-outline-secondary mt-1 btn-import-mailchimp"
                                                        data-url="{{ route('users.import.mailchimp') }}"
                                                        data-user-id="{{ $post->user_id }}"
                                                        data-email="{{ $post->email }}"
                                                        data-tags='["Register of Membership {{ now()->format('d M Y') }}"]'
                                                        title="Re-sync data member ini ke Mailchimp">
                                                        <i class="fas fa-sync-alt"></i> Re-sync MC
                                                    </button>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif

                        </div>

                    </div>
                </div>{{-- /card --}}

            </div>{{-- /section-body --}}
        </section>
    </div>

    {{-- Modal Import Excel --}}
    <div class="modal fade" tabindex="-1" role="dialog" id="example">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Import Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ Route('users.import') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label class="small text-muted">Pilih file .xlsx / .xls</label>
                            <input type="file" name="uploaded_file" id="uploaded_file" class="form-control-file">
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-upload mr-1"></i> Upload &amp; Import
                        </button>
                    </form>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="{{ url('sample/sample.xlsx') }}" class="btn btn-outline-primary" download>
                        <i class="fas fa-download mr-1"></i> Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('bottom')
    <script>
        // CSRF untuk semua AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        // Aktifkan tooltip
        $('[data-toggle="tooltip"]').tooltip();

        // Helper: tampilkan alert di atas tabel
        function showAlert(type, message) {
            $('#alert-area').html(
                `<div class="alert alert-${type} alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>×</span></button>
                        ${message}
                    </div>
                </div>`
            );
            $('html, body').animate({ scrollTop: $('#alert-area').offset().top - 80 }, 300);
        }

        // Parse data-tags
        function parseTags(raw) {
            if (!raw) return [];
            if (Array.isArray(raw)) return raw;
            try {
                const j = JSON.parse(raw);
                return Array.isArray(j) ? j : [];
            } catch (e) {
                return String(raw).split(',').map(s => s.trim()).filter(Boolean);
            }
        }

        // ===== Verify Member =====
        $(document).on('click', '.btn-verify-member:not([disabled])', function() {
            const $btn   = $(this);
            const url    = $btn.data('url');
            const $td    = $btn.closest('td');
            const $badge = $td.find('.member-status-badge');
            const $row   = $btn.closest('tr');
            const original = $btn.html();

            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...'
            );

            $.ajax({ url, method: 'POST', dataType: 'json' })
                .done(function(res) {
                    if (res && res.success) {
                        // Update badge
                        $badge.removeClass('badge-warning').addClass('badge-success')
                            .html('<i class="fas fa-check mr-1"></i>Active');
                        // Update button
                        $btn.removeClass('btn-primary').addClass('btn-success')
                            .html('<i class="fas fa-check"></i> Verified');
                        // Hapus highlight row pending
                        $row.removeClass('table-warning').css('background-color', '');
                        showAlert('success', '<i class="fas fa-check-circle mr-1"></i>' + res.message);
                    } else {
                        $btn.prop('disabled', false).html(original);
                        showAlert('warning', (res && res.message) || 'Gagal verifikasi.');
                    }
                })
                .fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal menghubungi server.';
                    $btn.prop('disabled', false).html(original);
                    showAlert('danger', msg);
                });
        });

        // ===== Re-sync ke Mailchimp =====
        $(document).on('click', '.btn-import-mailchimp', function() {
            const $btn   = $(this);
            const url    = $btn.data('url');
            const userId = $btn.data('user-id');
            const email  = $btn.data('email');
            const tags   = parseTags($btn.attr('data-tags'));
            const original = $btn.html();

            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Syncing...'
            );

            $.ajax({
                url, method: 'POST', dataType: 'json',
                data: { user_id: userId, email, tags }
            })
            .done(function(res) {
                if (res && res.success) {
                    $btn.html('<i class="fas fa-check"></i> Synced').addClass('btn-success').removeClass('btn-outline-secondary');
                    showAlert('success', res.message);
                } else {
                    $btn.prop('disabled', false).html(original);
                    showAlert('warning', (res && res.message) || 'Sync gagal.');
                }
            })
            .fail(function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal menghubungi server.';
                $btn.prop('disabled', false).html(original);
                showAlert('danger', msg);
            });
        });

        // ===== Update Tier =====
        $(document).on('change', '.user-tier-select', function() {
            const $select = $(this);
            const url     = $select.data('url');
            const tier    = $select.val();
            const $badge  = $select.closest('td').find('.tier-status');

            $badge.removeClass('badge-light badge-success badge-danger').addClass('badge-warning').text('Saving...');

            $.ajax({ url, method: 'POST', dataType: 'json', data: { tier } })
                .done(function(res) {
                    if (res && res.success) {
                        $badge.removeClass('badge-warning').addClass('badge-success').text('Saved');
                    } else {
                        $badge.removeClass('badge-warning').addClass('badge-danger').text('Failed');
                        showAlert('warning', (res && res.message) || 'Gagal update tier.');
                    }
                })
                .fail(function(xhr) {
                    $badge.removeClass('badge-warning').addClass('badge-danger').text('Failed');
                    showAlert('danger', xhr.responseJSON?.message || 'Gagal menghubungi server.');
                });
        });

        // DataTable
        $(document).ready(function() {
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                pageLength: 25,
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5'],
                order: [[0, 'asc']],
            });
        });
    </script>
@endpush
