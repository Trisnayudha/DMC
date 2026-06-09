@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Activity Log — Company Database</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.company_database.index') }}">Company Database</a></div>
                    <div class="breadcrumb-item active">Activity Log</div>
                </div>
                <div class="section-header-button ml-auto">
                    <a href="{{ route('admin.company_database.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="section-body">

                {{-- Filter bar --}}
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <form method="GET" action="{{ route('admin.company_database.logs') }}" class="form-inline flex-wrap" style="gap:8px">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama company…" value="{{ $search }}" style="min-width:200px">
                            <select name="action" class="form-control form-control-sm">
                                <option value="">Semua action</option>
                                <option value="update"   {{ $action === 'update'   ? 'selected' : '' }}>update</option>
                                <option value="sync"     {{ $action === 'sync'     ? 'selected' : '' }}>sync</option>
                                <option value="sync_all" {{ $action === 'sync_all' ? 'selected' : '' }}>sync all</option>
                            </select>
                            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i> Filter</button>
                            @if($search || $action)
                                <a href="{{ route('admin.company_database.logs') }}" class="btn btn-light btn-sm">Reset</a>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size:12px">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:120px">Waktu</th>
                                        <th style="width:140px">Admin</th>
                                        <th style="width:75px; text-align:center">Action</th>
                                        <th style="width:160px">Company</th>
                                        <th>Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $log)
                                        @php
                                            $props   = $log->properties->toArray();
                                            $changes = $props['changes'] ?? [];
                                        @endphp
                                        <tr style="vertical-align:middle">
                                            <td class="text-muted" style="white-space:nowrap; line-height:1.4">
                                                {{ $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}<br>
                                                <span style="font-size:11px">{{ $log->created_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                                            </td>
                                            <td style="line-height:1.4">
                                                <span class="font-weight-bold">{{ $log->causer ? $log->causer->name : '—' }}</span>
                                                @if($log->causer && $log->causer->email)
                                                    <br><span class="text-muted" style="font-size:11px">{{ $log->causer->email }}</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center">
                                                @if($log->description === 'update')
                                                    <span class="badge badge-success">update</span>
                                                @elseif($log->description === 'sync')
                                                    <span class="badge badge-primary">sync</span>
                                                @elseif($log->description === 'sync_all')
                                                    <span class="badge badge-warning text-dark">sync all</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $log->description }}</span>
                                                @endif
                                            </td>
                                            <td style="font-weight:600; line-height:1.4">
                                                {{ $props['company_name'] ?? '—' }}
                                                @if($log->description === 'sync')
                                                    <br><span class="text-muted font-weight-normal" style="font-size:11px">{{ $props['updated_records'] ?? 0 }}/{{ $props['total_records'] ?? 0 }} record</span>
                                                @elseif($log->description === 'sync_all')
                                                    <br><span class="text-muted font-weight-normal" style="font-size:11px">{{ $props['synced_companies'] ?? 0 }} company · {{ $props['updated_rows'] ?? 0 }} row</span>
                                                @elseif($log->description === 'update' && !empty($changes))
                                                    <br><span class="text-muted font-weight-normal" style="font-size:11px">{{ count($changes) }} field berubah · {{ $props['updated_records'] ?? 0 }} record</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->description === 'sync_all')
                                                    <span class="text-muted">Scope: </span><code style="font-size:11px">{{ $props['scope'] ?? '—' }}</code>
                                                    @if(!empty($props['search']))
                                                        <span class="text-muted"> · search: <em>"{{ $props['search'] }}"</em></span>
                                                    @endif

                                                @elseif($log->description === 'update' && !empty($changes))
                                                    <table style="font-size:11px; border-collapse:collapse; width:100%">
                                                        @foreach($changes as $field => $diff)
                                                            <tr>
                                                                <td style="padding:1px 8px 1px 0; white-space:nowrap; width:130px">
                                                                    <code style="background:#f0f0f0; padding:1px 4px; border-radius:3px; font-size:10px">{{ $field }}</code>
                                                                </td>
                                                                <td style="padding:1px 4px; color:#c0392b; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap" title="{{ $diff['before'] ?? '' }}">
                                                                    @if($diff['before'] !== null && $diff['before'] !== '')
                                                                        {{ $diff['before'] }}
                                                                    @else
                                                                        <span class="text-muted font-italic">kosong</span>
                                                                    @endif
                                                                </td>
                                                                <td style="padding:1px 4px; color:#aaa; white-space:nowrap">&rarr;</td>
                                                                <td style="padding:1px 0; color:#27ae60; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap" title="{{ $diff['after'] ?? '' }}">
                                                                    @if($diff['after'] !== null && $diff['after'] !== '')
                                                                        {{ $diff['after'] }}
                                                                    @else
                                                                        <span class="text-muted font-italic">kosong</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>

                                                @elseif($log->description === 'sync')
                                                    <span class="text-muted" style="font-size:11px">—</span>

                                                @else
                                                    <pre class="mb-0 text-muted" style="font-size:10px">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                                Belum ada aktivitas tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($logs->hasPages())
                        <div class="card-footer d-flex justify-content-between align-items-center py-2">
                            <small class="text-muted">
                                Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} aktivitas
                            </small>
                            {{ $logs->links() }}
                        </div>
                    @else
                        <div class="card-footer py-2">
                            <small class="text-muted">{{ $logs->total() }} aktivitas</small>
                        </div>
                    @endif
                </div>

            </div>
        </section>
    </div>
@endsection
