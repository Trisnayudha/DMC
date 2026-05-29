{{-- Main table card: filters + tables --}}

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
            @elseif (request('filter') === 'self_edited')
                <span class="text-warning"><i class="fas fa-user-edit mr-1"></i>Self-Edited by User</span>
            @elseif (request('filter') === 'password_null')
                <span class="text-warning"><i class="fas fa-key mr-1"></i>Active Members Without Password</span>
            @elseif (request('filter') === 'declined')
                <span class="text-danger"><i class="fas fa-times-circle mr-1"></i>Declined Applications</span>
            @else
                <i class="fas fa-users mr-1"></i>All Members
            @endif
        </h4>

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
                <a href="{{ url('admin/users?filter=declined') }}"
                    class="btn btn-sm {{ request('filter') === 'declined' ? 'btn-danger' : 'btn-outline-danger' }}"
                    title="Applicant yang sudah di-decline">
                    <i class="fas fa-times-circle mr-1"></i> Declined
                    @if (($countDeclined ?? 0) > 0)
                        <span
                            class="badge {{ request('filter') === 'declined' ? 'badge-light' : 'badge-danger' }} ml-1">{{ $countDeclined }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Date filter + actions --}}
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 border-top pt-3">
            <form action="{{ url('admin/users') }}" method="GET" class="d-flex flex-wrap align-items-end"
                style="gap:0;">
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
                                {{ $y }}</option>
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

        {{-- Tables --}}
        <div class="table-responsive">

            @if (request('filter') === 'unregist')
                @include('admin.users.partials._table_unregistered')
            @else
                @include('admin.users.partials._table_members')
            @endif

        </div>

    </div>
</div>{{-- /card --}}
