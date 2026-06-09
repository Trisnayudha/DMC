@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>User Edit Logs</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ Route('users') }}">Users Management</a></div>
                    <div class="breadcrumb-item active">Edit Logs</div>
                </div>
            </div>

            <div class="section-body">

                {{-- Stats row --}}
                <div class="row mb-3">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-user-edit"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Self-edited (User)</h4></div>
                                <div class="card-body">{{ $countSelfEdit }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="fas fa-user-shield"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Admin Edits</h4></div>
                                <div class="card-body">{{ $countAdminEdit }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Critical Field Changed</h4></div>
                                <div class="card-body">{{ $countCritical }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-users"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Unique Users Affected</h4></div>
                                <div class="card-body">{{ $countUniqueUsers }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-history mr-2"></i>Riwayat Perubahan Data User</h4>
                    </div>
                    <div class="card-body">

                        {{-- Filter form --}}
                        <form method="GET" action="{{ route('admin.user_edit_logs') }}" class="mb-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-3 mb-2">
                                    <label class="small text-muted mb-1">Sumber</label>
                                    <select name="source" class="form-control form-control-sm">
                                        <option value="">Semua</option>
                                        <option value="self" {{ request('source') === 'self' ? 'selected' : '' }}>Self-edit (User)</option>
                                        <option value="admin" {{ request('source') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 mb-2">
                                    <label class="small text-muted mb-1">Field Kritis Saja</label>
                                    <select name="critical" class="form-control form-control-sm">
                                        <option value="">Semua field</option>
                                        <option value="1" {{ request('critical') === '1' ? 'selected' : '' }}>Critical only</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 mb-2">
                                    <label class="small text-muted mb-1">Dari</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                                </div>
                                <div class="form-group col-md-2 mb-2">
                                    <label class="small text-muted mb-1">Sampai</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                                </div>
                                <div class="form-group col-md-2 mb-2">
                                    <label class="small text-muted mb-1">&nbsp;</label>
                                    <div class="d-flex" style="gap:6px;">
                                        <button type="submit" class="btn btn-sm btn-primary btn-block">Filter</button>
                                        <a href="{{ route('admin.user_edit_logs') }}" class="btn btn-sm btn-outline-secondary btn-block">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" id="edit-logs-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="140">Waktu</th>
                                        <th>User</th>
                                        <th width="160">Sumber / Admin</th>
                                        <th>Field</th>
                                        <th>Sebelum</th>
                                        <th>Sesudah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $log)
                                        @php
                                            $isSelfEdit = is_null($log->admin_id);
                                            $criticalFields = ['company_name', 'company_category', 'company_other', 'prefix'];
                                            $changes = is_array($log->changes) ? $log->changes : (json_decode($log->changes, true) ?: []);
                                        @endphp
                                        @foreach ($changes as $field => $diff)
                                            @php $isCritical = in_array($field, $criticalFields); @endphp
                                            <tr class="{{ $isCritical && $isSelfEdit ? 'table-warning' : '' }}">
                                                <td class="text-nowrap">
                                                    <small>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</small><br>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</small>
                                                </td>
                                                <td>
                                                    @if ($log->user)
                                                        <a href="{{ url('admin/users') }}#row_{{ $log->user_id }}" target="_blank">
                                                            {{ $log->user->name ?? '-' }}
                                                        </a><br>
                                                        <small class="text-muted">{{ $log->user->email ?? '' }}</small>
                                                    @else
                                                        <span class="text-muted">ID #{{ $log->user_id }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($isSelfEdit)
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-user-edit"></i> Self-edit
                                                        </span><br>
                                                        <small class="text-muted">{{ Str::after($log->admin_name ?? '', 'User self-edit via ') }}</small>
                                                    @else
                                                        <span class="badge badge-primary">
                                                            <i class="fas fa-user-shield"></i> Admin
                                                        </span><br>
                                                        <small>{{ $log->admin_name ?? '-' }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $isCritical ? 'badge-danger' : 'badge-secondary' }}">
                                                        {{ ucwords(str_replace('_', ' ', $field)) }}
                                                    </span>
                                                    @if ($isCritical)
                                                        <i class="fas fa-exclamation-circle text-danger ml-1" title="Critical field" data-toggle="tooltip"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-danger" style="font-size:12px;">
                                                        {{ $diff['old'] ?: '<em class="text-muted">(kosong)</em>' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-success" style="font-size:12px;">
                                                        {{ $diff['new'] ?: '<em class="text-muted">(kosong)</em>' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Tidak ada log perubahan ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} entri log
                            </small>
                            {{ $logs->appends(request()->query())->links() }}
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('bottom')
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
@endpush
