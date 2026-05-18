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
                                    <div class="card-header">
                                        <h4>Active Members</h4>
                                    </div>
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
                                    <div class="card-header">
                                        <h4>Pending Verification</h4>
                                    </div>
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
                                    <div class="card-header">
                                        <h4>New This Month</h4>
                                    </div>
                                    <div class="card-body">{{ $countNewThisMonth }}</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Unregistered --}}
                    {{-- <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="{{ url('admin/users?filter=unregist') }}" class="text-decoration-none">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-danger"><i class="fas fa-user-times"></i></div>
                                <div class="card-wrap">
                                    <div class="card-header"><h4>Unregistered</h4></div>
                                    <div class="card-body">{{ $countUnRegistered }}</div>
                                </div>
                            </div>
                        </a>
                    </div> --}}

                    {{-- Mailchimp Contacts --}}
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fab fa-mailchimp"></i></div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Mailchimp Contacts</h4>
                                </div>
                                <div class="card-body" id="mc-contact-count">
                                    <span class="spinner-border spinner-border-sm text-info" role="status"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /stats row --}}

                {{-- Self-edit alert banner (only shown when there are self-edits) --}}
                @if ($countSelfEdited > 0)
                    <div class="alert alert-warning alert-dismissible show fade d-flex align-items-center py-2 mb-3"
                        style="gap:10px;">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>{{ $countSelfEdited }} user</strong> telah mengubah data mereka sendiri melalui
                            apps/website.
                            <a href="{{ url('admin/users?filter=self_edited') }}" class="font-weight-bold ml-2">Lihat
                                daftar →</a>
                        </div>
                        <button type="button" class="close ml-2" data-dismiss="alert"><span>×</span></button>
                    </div>
                @endif

                {{-- ====== MAIN TABLE CARD ====== --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            @if (request('status_member') === 'active')
                                <span class="text-success"><i class="fas fa-user-check mr-1"></i>Active Members</span>
                            @elseif (request('status_member') === 'pending')
                                <span class="text-warning"><i class="fas fa-user-clock mr-1"></i>Pending Verification</span>
                            @elseif (request('filter') === 'this_month')
                                <span class="text-primary"><i class="fas fa-user-plus mr-1"></i>New Members —
                                    {{ now()->format('F Y') }}</span>
                            @elseif (request('filter') === 'unregist')
                                {{-- <span class="text-danger"><i class="fas fa-user-times mr-1"></i>Unregistered</span> --}}
                            @elseif (request('filter') === 'self_edited')
                                <span class="text-warning"><i class="fas fa-user-edit mr-1"></i>Self-Edited by User</span>
                            @elseif (request('filter') === 'password_null')
                                <span class="text-warning"><i class="fas fa-key mr-1"></i>Active Members Without
                                    Password</span>
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
                                <a href="{{ url('admin/users?filter=self_edited') }}"
                                    class="btn btn-sm {{ request('filter') === 'self_edited' ? 'btn-warning' : 'btn-outline-warning' }}"
                                    title="User yang mengubah data sendiri via apps/web">
                                    <i class="fas fa-user-edit mr-1"></i> Self-Edited
                                    @if ($countSelfEdited > 0)
                                        <span
                                            class="badge {{ request('filter') === 'self_edited' ? 'badge-light' : 'badge-warning' }} ml-1">{{ $countSelfEdited }}</span>
                                    @endif
                                </a>
                                <a href="{{ url('admin/users?filter=password_null') }}"
                                    class="btn btn-sm {{ request('filter') === 'password_null' ? 'btn-warning' : 'btn-outline-warning' }}"
                                    title="Member aktif yang password-nya masih kosong">
                                    <i class="fas fa-key mr-1"></i> Password NULL
                                    @if (($countActiveWithoutPassword ?? 0) > 0)
                                        <span
                                            class="badge {{ request('filter') === 'password_null' ? 'badge-light' : 'badge-warning' }} ml-1">{{ $countActiveWithoutPassword }}</span>
                                    @endif
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
                                            <option value="{{ $m }}"
                                                {{ request('month') == $m ? 'selected' : '' }}>
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
                                            <option value="{{ $y }}"
                                                {{ request('year') == $y ? 'selected' : '' }}>
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
                                    @if (request('date_from'))
                                        From <strong>{{ request('date_from') }}</strong>
                                    @endif
                                    @if (request('date_to'))
                                        &nbsp;to <strong>{{ request('date_to') }}</strong>
                                    @endif
                                    @if (request('month'))
                                        &nbsp;| Month:
                                        <strong>{{ \Carbon\Carbon::create()->month((int) request('month'))->format('F') }}</strong>
                                    @endif
                                    @if (request('year'))
                                        &nbsp;| Year: <strong>{{ request('year') }}</strong>
                                    @endif
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
                                                    <small
                                                        class="text-muted">{{ date('H:i', strtotime($post->created_at)) }}</small>
                                                </td>
                                                <td>{{ $post->name }}</td>
                                                <td>{{ $post->company_name }}</td>
                                                <td>{{ $post->job_title }}</td>
                                                <td><a href="mailto:{{ $post->email }}">{{ $post->email }}</a></td>
                                                <td class="text-nowrap">{{ $post->fullphone ?? $post->phone }}</td>
                                                <td>{{ $post->address }}</td>
                                                <td>{{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}
                                                </td>
                                                <td>
                                                    @if ($post->exported_at)
                                                        <span class="btn btn-xs btn-secondary disabled"
                                                            title="Exported on {{ \Carbon\Carbon::parse($post->exported_at)->format('d M Y H:i') }}">
                                                            <i class="fas fa-check-circle"></i> Exported
                                                        </span>
                                                    @else
                                                        <a href="{{ route('admin.member.export', $post->id) }}"
                                                            class="btn btn-xs btn-success"
                                                            onclick="return confirm('Export member ini ke Users?')">
                                                            <i class="fas fa-file-export"></i> Export to Member
                                                        </a>
                                                    @endif
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
                                            <th width="100px">Password</th>
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
                                                    <small
                                                        class="text-muted">{{ date('H:i', strtotime($post->user_created_at ?? $post->created_at)) }}</small>
                                                </td>

                                                <td>
                                                    {{ $post->name }}
                                                    @if (isset($selfEditMap[$post->user_id]))
                                                        <br>
                                                        <span class="badge badge-warning"
                                                            style="font-size:10px; cursor:default;"
                                                            title="User mengubah data sendiri — {{ \Carbon\Carbon::parse($selfEditMap[$post->user_id])->format('d M Y H:i') }}"
                                                            data-toggle="tooltip">
                                                            <i class="fas fa-user-edit"></i> Self-edited
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- TIER --}}
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <select class="form-control form-control-sm user-tier-select"
                                                            data-url="{{ route('users.update.tier', $post->user_id) }}"
                                                            style="max-width:110px;">
                                                            @php
                                                                $tier = strtolower((string) ($post->tier ?? 'reguler'));
                                                                if (!in_array($tier, ['reguler', 'black'])) {
                                                                    $tier = 'reguler';
                                                                }
                                                            @endphp
                                                            <option value="reguler"
                                                                {{ $tier === 'reguler' ? 'selected' : '' }}>Reguler
                                                            </option>
                                                            <option value="black"
                                                                {{ $tier === 'black' ? 'selected' : '' }}>Black</option>
                                                        </select>
                                                        <span class="ml-1 badge badge-light tier-status"
                                                            style="font-size:10px;">Saved</span>
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
                                                            @php
                                                                $companyVerified = !empty($post->is_verified) || !empty($post->has_verified_company_name);
                                                                $companyPayload = [
                                                                    'company_name' => $post->company_name,
                                                                    'prefix' => $post->prefix,
                                                                    'company_website' => $post->company_website,
                                                                    'company_category' => $post->company_category,
                                                                    'company_other' => $post->company_other,
                                                                    'address' => $post->address,
                                                                    'city' => $post->city,
                                                                    'portal_code' => $post->portal_code,
                                                                    'prefix_office_number' =>
                                                                        $post->prefix_office_number,
                                                                    'office_number' => $post->office_number,
                                                                    'full_office_number' => $post->full_office_number,
                                                                    'country' => $post->country,
                                                                ];
                                                            @endphp
                                                            <button type="button"
                                                                class="btn btn-xs {{ $companyVerified ? 'btn-primary' : 'btn-warning' }} btn-verify-member"
                                                                data-url="{{ route('users.verify', $post->user_id) }}"
                                                                data-company-verified="{{ $companyVerified ? '1' : '0' }}"
                                                                data-company-name="{{ $post->company_name }}"
                                                                data-normalized-name="{{ strtolower(trim((string) $post->company_name)) }}"
                                                                data-member-name="{{ $post->name }}"
                                                                data-payload='@json($companyPayload)'
                                                                title="{{ $companyVerified ? 'Verifikasi member' : 'Company belum verified — klik untuk selesaikan dulu' }}">
                                                                @if (!$companyVerified)
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                @else
                                                                    <i class="fas fa-shield-alt"></i>
                                                                @endif
                                                                Verify
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td>{{ $post->job_title }}</td>
                                                <td>{{ $post->company_name }}</td>
                                                <td><a href="mailto:{{ $post->email }}">{{ $post->email }}</a></td>
                                                <td class="text-nowrap">{{ $post->fullphone ?? $post->phone }}</td>
                                                <td class="text-nowrap">
                                                    {{ $post->office_number ?? $post->full_office_number }}</td>
                                                <td>{{ $post->address }}</td>
                                                <td>
                                                    @if ($post->company_website)
                                                        <a href="{{ $post->company_website }}" target="_blank"
                                                            rel="noopener">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>{{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}
                                                </td>

                                                {{-- CCI & Sponsorship --}}
                                                <td>
                                                    <div class="d-flex flex-column align-items-start" style="gap:4px;">
                                                        @if ($post->cci)
                                                            <span class="badge badge-info">
                                                                <i class="fas fa-building mr-1"></i>CCI
                                                            </span>
                                                        @else
                                                            <span class="badge badge-light text-muted"
                                                                style="font-size:10px;">CCI: No</span>
                                                        @endif

                                                        @if ($post->explore)
                                                            <span class="badge badge-warning"
                                                                title="Member bersedia menerima penawaran paket sponsorship"
                                                                data-toggle="tooltip">
                                                                <i class="fas fa-star mr-1"></i>Open to Sponsorship
                                                            </span>
                                                        @else
                                                            <span class="badge badge-light text-muted"
                                                                style="font-size:10px;">Sponsorship: No</span>
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

                                                {{-- PASSWORD STATUS --}}
                                                <td class="text-center">
                                                    @if ($post->password)
                                                        <span class="badge badge-success" title="Password has been set"
                                                            data-toggle="tooltip">
                                                            <i class="fas fa-lock"></i> Set
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger" title="Password not set yet"
                                                            data-toggle="tooltip">
                                                            <i class="fas fa-lock-open"></i> Not Set
                                                        </span>
                                                    @endif
                                                    <div class="mt-1 d-flex flex-column" style="gap:3px;">
                                                        <button type="button"
                                                            class="btn btn-xs btn-outline-primary btn-edit-user"
                                                            data-user-id="{{ $post->user_id }}"
                                                            data-name="{{ $post->name }}"
                                                            data-email="{{ $post->email }}"
                                                            data-job-title="{{ $post->job_title }}"
                                                            data-phone="{{ $post->fullphone ?? $post->phone }}"
                                                            data-status-member="{{ $post->status_member }}"
                                                            data-tier="{{ strtolower((string) ($post->tier ?? 'reguler')) }}"
                                                            data-update-url="{{ route('users.update', $post->user_id) }}"
                                                            title="Edit data user">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-xs btn-outline-secondary btn-view-logs"
                                                            data-user-id="{{ $post->user_id }}"
                                                            data-name="{{ $post->name }}"
                                                            data-logs-url="{{ route('users.logs', $post->user_id) }}"
                                                            title="Lihat riwayat perubahan">
                                                            <i class="fas fa-history"></i> Log
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

    {{-- Modal: Verify Member (2-step: company check → member confirm) --}}
    <div class="modal fade" id="verifyMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">

                {{-- Step indicator --}}
                <div class="modal-header pb-2" style="border-bottom:none;">
                    <div class="w-100">
                        <div class="d-flex align-items-center mb-2" style="gap:8px;">
                            <span id="vm-step-indicator-1" class="badge badge-primary" style="font-size:12px;">Step
                                1</span>
                            <span style="font-size:11px; color:#adb5bd;">Verifikasi Company</span>
                            <span style="color:#dee2e6; font-size:14px;">›</span>
                            <span id="vm-step-indicator-2" class="badge badge-light" style="font-size:12px;">Step
                                2</span>
                            <span style="font-size:11px; color:#adb5bd;">Verifikasi Member</span>
                        </div>
                        <h5 class="modal-title mb-0" id="vm-modal-title">Verifikasi Member</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="margin-top:-28px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                {{-- ===== STEP 1: Company Verification ===== --}}
                <div id="vm-step-1">
                    <div class="modal-body pt-2">
                        <div class="alert alert-warning d-flex align-items-start mb-3" style="gap:10px;">
                            <i class="fas fa-exclamation-triangle mt-1"></i>
                            <div>
                                <strong>Company belum terverifikasi.</strong><br>
                                <small>Pastikan data company sudah benar sebelum memverifikasi member ini. Perubahan akan
                                    diterapkan ke semua user dengan company yang sama.</small>
                            </div>
                        </div>

                        <div class="alert alert-light mb-3 py-2">
                            Member: <strong id="vm-member-name">-</strong> &nbsp;|&nbsp;
                            Company saat ini: <strong id="vm-company-label">-</strong>
                        </div>

                        <input type="hidden" id="vm-normalized-name">

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Prefix</label>
                                <select id="vm-prefix" class="form-control vm-prefix-select2">
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
                                    <input type="text" id="vm-company-name" class="form-control" autocomplete="off"
                                        placeholder="Ketik nama company atau pilih dari verified...">
                                    <div id="vm-company-suggestions" class="list-group position-absolute w-100"
                                        style="z-index:9999; display:none; max-height:180px; overflow-y:auto; top:100%; left:0; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                                    </div>
                                </div>
                                <small class="text-muted">Ketik untuk saran dari company yang sudah verified.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Website</label>
                                <input type="text" id="vm-company-website" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Company Category</label>
                                <select id="vm-company-category" class="form-control">
                                    <option value="">--Select--</option>
                                    <option value="Coal Mining">Coal Mining</option>
                                    <option value="Minerals Producer">Minerals Producer</option>
                                    <option value="Supplier/Distributor/Manufacturer">Supplier/Distributor/Manufacturer
                                    </option>
                                    <option value="Contrator">Contrator</option>
                                    <option value="Association / Organization / Government">Association / Organization /
                                        Government</option>
                                    <option value="Financial Services">Financial Services</option>
                                    <option value="Technology">Technology</option>
                                    <option value="Investors">Investors</option>
                                    <option value="Logistics and Shipping">Logistics and Shipping</option>
                                    <option value="Media">Media</option>
                                    <option value="Consultant">Consultant</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 vm-company-other-wrap" style="display:none;">
                                <label>Company Other</label>
                                <input type="text" id="vm-company-other" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea id="vm-address" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>City</label>
                                <input type="text" id="vm-city" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Postal Code</label>
                                <input type="text" id="vm-portal-code" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Country</label>
                                <input type="text" id="vm-country" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Prefix Office Number</label>
                                <input type="text" id="vm-prefix-office-number" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Office Number</label>
                                <input type="text" id="vm-office-number" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Full Office Number</label>
                                <input type="text" id="vm-full-office-number" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" id="vm-btn-verify-company">
                            <i class="fas fa-check-circle mr-1"></i> Verifikasi Company & Lanjut
                        </button>
                    </div>
                </div>

                {{-- ===== STEP 2: Confirm Member Verification ===== --}}
                <div id="vm-step-2" style="display:none;">
                    <div class="modal-body py-4 text-center">
                        <div class="mb-3">
                            <span style="font-size:48px; color:#28a745;"><i class="fas fa-check-circle"></i></span>
                        </div>
                        <h5 class="mb-1">Company berhasil diverifikasi!</h5>
                        <p class="text-muted mb-4" id="vm-step2-company-label">-</p>
                        <div class="alert alert-light d-inline-block px-4">
                            Lanjut verifikasi member <strong id="vm-step2-member-name">-</strong>?<br>
                            <small class="text-muted">Status member akan berubah jadi <span
                                    class="badge badge-success">Active</span> dan data dikirim ke Mailchimp.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Nanti dulu</button>
                        <button type="button" class="btn btn-primary px-4" id="vm-btn-verify-member">
                            <i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal: Edit User --}}
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Data User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="eu-user-id">
                    <input type="hidden" id="eu-update-url">
                    <div class="form-group">
                        <label class="small font-weight-bold">Nama</label>
                        <input type="text" id="eu-name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Email</label>
                        <input type="email" id="eu-email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Job Title</label>
                        <input type="text" id="eu-job-title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Phone</label>
                        <input type="text" id="eu-phone" class="form-control">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Status Member</label>
                            <select id="eu-status-member" class="form-control">
                                <option value="">-- tidak diubah --</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Tier</label>
                            <select id="eu-tier" class="form-control">
                                <option value="reguler">Reguler</option>
                                <option value="black">Black</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="eu-btn-save">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Edit Log --}}
    <div class="modal fade" id="userLogsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-history mr-2"></i>Riwayat Perubahan — <span
                            id="logs-user-name">-</span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="logs-loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat log...</p>
                    </div>
                    <div id="logs-empty" class="text-center py-4 text-muted" style="display:none;">
                        Belum ada riwayat perubahan.
                    </div>
                    <div id="logs-content" style="display:none;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Admin</th>
                                    <th>Field</th>
                                    <th>Sebelum</th>
                                    <th>Sesudah</th>
                                </tr>
                            </thead>
                            <tbody id="logs-tbody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
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
            $('html, body').animate({
                scrollTop: $('#alert-area').offset().top - 80
            }, 300);
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

        // ===== Verify Member (2-step flow) =====
        var $vmSourceBtn = null; // tombol yang memicu modal

        function vmFillCompanyFields(p) {
            $('#vm-prefix').val(p.prefix || '');
            if ($.fn.select2) $('#vm-prefix').trigger('change');
            $('#vm-company-name').val(p.company_name || '');
            $('#vm-company-website').val(p.company_website || '');
            $('#vm-company-category').val(p.company_category || '');
            $('#vm-company-other').val(p.company_other || '');
            $('#vm-address').val(p.address || '');
            $('#vm-city').val(p.city || '');
            $('#vm-portal-code').val(p.portal_code || '');
            $('#vm-country').val(p.country || '');
            $('#vm-prefix-office-number').val(p.prefix_office_number || '');
            $('#vm-office-number').val(p.office_number || '');
            $('#vm-full-office-number').val(p.full_office_number || '');
            if ((p.company_category || '') === 'other') {
                $('.vm-company-other-wrap').show();
            } else {
                $('.vm-company-other-wrap').hide();
                $('#vm-company-other').val('');
            }
        }

        function vmDoVerifyMember(url, $btn) {
            $('#vm-btn-verify-member').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm mr-1"></span> Memverifikasi...');

            $.ajax({
                    url,
                    method: 'POST',
                    dataType: 'json'
                })
                .done(function(res) {
                    if (res && res.success) {
                        if ($btn) {
                            const $td = $btn.closest('td');
                            const $badge = $td.find('.member-status-badge');
                            const $row = $btn.closest('tr');
                            $badge.removeClass('badge-warning').addClass('badge-success')
                                .html('<i class="fas fa-check mr-1"></i>Active');
                            $btn.removeClass('btn-primary btn-warning').addClass('btn-success')
                                .attr('disabled', true)
                                .html('<i class="fas fa-check"></i> Verified');
                            $row.removeClass('table-warning').css('background-color', '');
                        }
                        $('#verifyMemberModal').modal('hide');
                        showAlert('success', '<i class="fas fa-check-circle mr-1"></i>' + res.message);
                    } else {
                        $('#vm-btn-verify-member').prop('disabled', false)
                            .html('<i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang');
                        showAlert('warning', (res && res.message) || 'Gagal verifikasi member.');
                    }
                })
                .fail(function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghubungi server.';
                    $('#vm-btn-verify-member').prop('disabled', false)
                        .html('<i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang');
                    showAlert('danger', msg);
                });
        }

        // Klik tombol Verify di tabel
        $(document).on('click', '.btn-verify-member:not([disabled])', function() {
            $vmSourceBtn = $(this);
            const companyVerified = $vmSourceBtn.attr('data-company-verified');
            const url = $vmSourceBtn.attr('data-url');

            // Company sudah verified → langsung verify member (no modal)
            if (String(companyVerified) === '1') {
                if (!confirm('Verifikasi member ' + ($vmSourceBtn.attr('data-member-name') || '') +
                        '?\nStatus akan berubah jadi Active.')) return;
                const original = $vmSourceBtn.html();
                $vmSourceBtn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Verifying...');
                vmDoVerifyMember(url, $vmSourceBtn);
                return;
            }

            // Company belum verified → buka modal 2-step
            let payload = {};
            try {
                payload = JSON.parse($vmSourceBtn.attr('data-payload') || '{}');
            } catch (e) {}

            $('#vm-modal-title').text('Verifikasi Member — ' + ($vmSourceBtn.attr('data-member-name') || ''));
            $('#vm-member-name').text($vmSourceBtn.attr('data-member-name') || '-');
            $('#vm-company-label').text($vmSourceBtn.attr('data-company-name') || '-');
            $('#vm-normalized-name').val($vmSourceBtn.attr('data-normalized-name') || '');
            $('#vm-step2-member-name').text($vmSourceBtn.attr('data-member-name') || '-');
            $('#vm-step2-company-label').text('');
            $('#vm-btn-verify-member').data('verify-url', url);

            // Reset step indicator
            $('#vm-step-indicator-1').removeClass('badge-light').addClass('badge-primary');
            $('#vm-step-indicator-2').removeClass('badge-primary').addClass('badge-light');
            $('#vm-step-1').show();
            $('#vm-step-2').hide();
            $('#vm-company-suggestions').hide().empty();
            $('#vm-btn-verify-company').prop('disabled', false)
                .html('<i class="fas fa-check-circle mr-1"></i> Verifikasi Company & Lanjut');

            vmFillCompanyFields(payload);

            if ($.fn.select2) {
                $('.vm-prefix-select2').select2({
                    dropdownParent: $('#verifyMemberModal'),
                    width: '100%',
                    placeholder: 'Select Prefix',
                    allowClear: true
                });
                $('#vm-prefix').val(payload.prefix || '').trigger('change');
            }

            $('#verifyMemberModal').modal('show');
        });

        // Autocomplete company name dalam modal
        var vmSuggestTimeout = null;
        $(document).on('input', '#vm-company-name', function() {
            var q = $(this).val().trim();
            clearTimeout(vmSuggestTimeout);
            $('#vm-company-suggestions').hide().empty();
            if (q.length < 2) return;

            vmSuggestTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route('admin.company_database.verified_companies') }}',
                    data: {
                        q: q
                    },
                    success: function(data) {
                        var $box = $('#vm-company-suggestions');
                        $box.empty();
                        if (!data || data.length === 0) {
                            $box.hide();
                            return;
                        }
                        $.each(data, function(i, c) {
                            var $item = $(
                                '<button type="button" class="list-group-item list-group-item-action"></button>'
                            );
                            $item.html(
                                '<span class="badge badge-success badge-sm mr-1"><i class="fas fa-check-circle"></i> Verified</span> <strong>' +
                                $('<span>').text(c.company_name).html() +
                                '</strong>');
                            $item.on('click', function() {
                                vmFillCompanyFields(c);
                                $box.hide().empty();
                            });
                            $box.append($item);
                        });
                        $box.show();
                    }
                });
            }, 300);
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#vm-company-name, #vm-company-suggestions').length) {
                $('#vm-company-suggestions').hide().empty();
            }
        });

        // Toggle company other
        $(document).on('change', '#vm-company-category', function() {
            if ($(this).val() === 'other') {
                $('.vm-company-other-wrap').show();
            } else {
                $('.vm-company-other-wrap').hide();
                $('#vm-company-other').val('');
            }
        });

        // Step 1: Verifikasi Company & Lanjut
        $('#vm-btn-verify-company').on('click', function() {
            const companyName = $('#vm-company-name').val().trim();
            if (!companyName) {
                showAlert('warning', 'Company name wajib diisi.');
                return;
            }

            $(this).prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

            const data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                normalized_name: $('#vm-normalized-name').val(),
                company_name: companyName,
                prefix: $('#vm-prefix').val(),
                company_website: $('#vm-company-website').val(),
                company_category: $('#vm-company-category').val(),
                company_other: $('#vm-company-other').val(),
                address: $('#vm-address').val(),
                city: $('#vm-city').val(),
                portal_code: $('#vm-portal-code').val(),
                country: $('#vm-country').val(),
                prefix_office_number: $('#vm-prefix-office-number').val(),
                office_number: $('#vm-office-number').val(),
                full_office_number: $('#vm-full-office-number').val(),
            };

            $.ajax({
                    url: '{{ route('admin.company_database.update') }}',
                    method: 'POST',
                    data: data,
                })
                .done(function() {
                    // Maju ke step 2
                    $('#vm-step-indicator-1').removeClass('badge-primary').addClass('badge-light');
                    $('#vm-step-indicator-2').removeClass('badge-light').addClass('badge-primary');
                    $('#vm-step2-company-label').text('Company "' + companyName + '" sudah diverifikasi.');
                    $('#vm-step-1').hide();
                    $('#vm-step-2').show();
                    $('#vm-btn-verify-member').prop('disabled', false)
                        .html('<i class="fas fa-shield-alt mr-1"></i> Verify Member Sekarang');

                    // Update badge di tabel supaya company_name terbaru
                    if ($vmSourceBtn) {
                        $vmSourceBtn.attr('data-company-verified', '1')
                            .removeClass('btn-warning').addClass('btn-primary')
                            .find('i').removeClass('fa-exclamation-triangle').addClass('fa-shield-alt');
                    }
                })
                .fail(function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ||
                        'Gagal menyimpan data company.';
                    $('#vm-btn-verify-company').prop('disabled', false)
                        .html('<i class="fas fa-check-circle mr-1"></i> Verifikasi Company & Lanjut');
                    showAlert('danger', msg);
                });
        });

        // Step 2: Verify Member
        $(document).on('click', '#vm-btn-verify-member', function() {
            const url = $(this).data('verify-url');
            vmDoVerifyMember(url, $vmSourceBtn);
        });

        // ===== Re-sync ke Mailchimp =====
        $(document).on('click', '.btn-import-mailchimp', function() {
            const $btn = $(this);
            const url = $btn.data('url');
            const userId = $btn.data('user-id');
            const email = $btn.data('email');
            const tags = parseTags($btn.attr('data-tags'));
            const original = $btn.html();

            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Syncing...'
            );

            $.ajax({
                    url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: userId,
                        email,
                        tags
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        $btn.html('<i class="fas fa-check"></i> Synced').addClass('btn-success').removeClass(
                            'btn-outline-secondary');
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
            const url = $select.data('url');
            const tier = $select.val();
            const $badge = $select.closest('td').find('.tier-status');

            $badge.removeClass('badge-light badge-success badge-danger').addClass('badge-warning').text(
                'Saving...');

            $.ajax({
                    url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        tier
                    }
                })
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

        // ===== Edit User =====
        $(document).on('click', '.btn-edit-user', function() {
            const $btn = $(this);
            $('#eu-user-id').val($btn.attr('data-user-id'));
            $('#eu-update-url').val($btn.attr('data-update-url'));
            $('#eu-name').val($btn.attr('data-name'));
            $('#eu-email').val($btn.attr('data-email'));
            $('#eu-job-title').val($btn.attr('data-job-title'));
            $('#eu-phone').val($btn.attr('data-phone'));
            $('#eu-status-member').val($btn.attr('data-status-member') || '');
            $('#eu-tier').val($btn.attr('data-tier') || 'reguler');
            $('#eu-btn-save').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
            $('#editUserModal').modal('show');
        });

        $('#eu-btn-save').on('click', function() {
            const url = $('#eu-update-url').val();
            if (!url) return;

            const name = $('#eu-name').val().trim();
            const email = $('#eu-email').val().trim();
            if (!name || !email) {
                showAlert('warning', 'Nama dan email wajib diisi.');
                return;
            }

            $(this).prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

            $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        name: name,
                        email: email,
                        job_title: $('#eu-job-title').val(),
                        phone: $('#eu-phone').val(),
                        status_member: $('#eu-status-member').val(),
                        tier: $('#eu-tier').val(),
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        $('#editUserModal').modal('hide');
                        showAlert('success', '<i class="fas fa-check-circle mr-1"></i>' + res.message);
                        if (res.changes && Object.keys(res.changes).length > 0) {
                            const changed = Object.keys(res.changes).join(', ');
                            showAlert('success', '<i class="fas fa-check-circle mr-1"></i>' + res.message +
                                ' (field diubah: ' + changed + ')');
                        }
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#eu-btn-save').prop('disabled', false).html(
                            '<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                        showAlert('warning', (res && res.message) || 'Gagal menyimpan.');
                    }
                })
                .fail(function(xhr) {
                    $('#eu-btn-save').prop('disabled', false).html(
                        '<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                    const msg = xhr.responseJSON?.message || 'Gagal menghubungi server.';
                    showAlert('danger', msg);
                });
        });

        // ===== View Edit Logs =====
        $(document).on('click', '.btn-view-logs', function() {
            const $btn = $(this);
            const url = $btn.attr('data-logs-url');
            const name = $btn.attr('data-name');

            $('#logs-user-name').text(name);
            $('#logs-loading').show();
            $('#logs-empty').hide();
            $('#logs-content').hide();
            $('#logs-tbody').empty();
            $('#userLogsModal').modal('show');

            $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                })
                .done(function(data) {
                    $('#logs-loading').hide();

                    if (!data || data.length === 0) {
                        $('#logs-empty').show();
                        return;
                    }

                    const fieldLabels = {
                        name: 'Nama',
                        email: 'Email',
                        job_title: 'Job Title',
                        phone: 'Phone',
                        status_member: 'Status Member',
                        tier: 'Tier'
                    };

                    $.each(data, function(i, log) {
                        const time = log.created_at || '-';
                        const admin = $('<span>').text(log.admin_name || '-').html();
                        const changes = log.changes || {};

                        $.each(changes, function(field, diff) {
                            const label = fieldLabels[field] || field;
                            const oldVal = $('<span>').text(diff.old || '-').html();
                            const newVal = $('<span>').text(diff.new || '-').html();
                            $('#logs-tbody').append(
                                `<tr>
                                <td class="text-nowrap"><small>${time}</small></td>
                                <td><small>${admin}</small></td>
                                <td><span class="badge badge-light">${label}</span></td>
                                <td><small class="text-danger">${oldVal}</small></td>
                                <td><small class="text-success">${newVal}</small></td>
                            </tr>`
                            );
                        });
                    });

                    $('#logs-content').show();
                })
                .fail(function() {
                    $('#logs-loading').hide();
                    $('#logs-empty').text('Gagal memuat log.').show();
                });
        });

        // ===== Mailchimp Contact Count =====
        (function fetchMcCount() {
            $.ajax({
                    url: '{{ route('users.mailchimp.count') }}',
                    method: 'GET',
                    dataType: 'json',
                    timeout: 12000,
                })
                .done(function(res) {
                    if (res && res.success && res.count !== null) {
                        $('#mc-contact-count').text(Number(res.count).toLocaleString());
                    } else {
                        $('#mc-contact-count').html('<span class="text-muted small">N/A</span>');
                    }
                })
                .fail(function() {
                    $('#mc-contact-count').html('<span class="text-muted small">N/A</span>');
                });
        })();

        // DataTable
        $(document).ready(function() {
            $('#laravel_crud').DataTable({
                dom: 'Bfrtip',
                pageLength: 25,
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5'],
                order: [
                    [0, 'asc']
                ],
            });
        });
    </script>
@endpush
