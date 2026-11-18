@extends('layouts.inspire.master')

@section('content')
    @php
        $timestampFormat = 'j F Y H:i T';
        $listUrl = $detail ? route('users_log.show', [$adminId, $menuKey]) : route('users_log.index');
        $tableFilters = $detail ? \Illuminate\Support\Arr::except($filters, ['admin_id', 'menu']) : $filters;
        $reportFilters = \Illuminate\Support\Arr::except($tableFilters, ['per_page']);
        $exportUrl = $detail
            ? route('users_log.detail.export', ['admin' => $adminId, 'menu' => $menuKey] + $reportFilters)
            : route('users_log.export', $reportFilters);
    @endphp
<section class="section">
    <div class="section-header">
        <h1>Users Log</h1>
        @if ($detail)
            <div class="section-header-breadcrumb">
                <a href="{{ route('users_log.index', ['admin_id' => $adminId, 'menu' => $menuKey] + $tableFilters) }}">Back to main log</a>
            </div>
        @endif
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h2 class="h5 mb-0">{{ $detail ? 'Activity details' : 'Main log' }}</h2>
            <a class="btn btn-outline-success" href="{{ $exportUrl }}"><i class="fas fa-file-excel mr-1" aria-hidden="true"></i> Export to Excel</a>
        </div>
        <div class="card-body">
            @if ($detail)
                <p><strong>{{ $admins[$adminId] ?? 'Admin' }} #{{ $adminId }}</strong> &middot; {{ $menus[$menuKey] ?? $menuKey }}</p>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="GET" action="{{ $listUrl }}">
                <input type="hidden" name="per_page" value="{{ $logs->perPage() }}">
                <div class="form-row align-items-end">
                    @unless ($detail)
                        <div class="form-group col-md">
                            <label for="admin_id">CMS admin</label>
                            <select id="admin_id" name="admin_id" class="form-control">
                                <option value="">All admins</option>
                                @foreach ($admins as $id => $name)
                                    <option value="{{ $id }}" {{ (string) $id === (string) $adminId ? 'selected' : '' }}>{{ $name }} #{{ $id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md">
                            <label for="menu">Menu</label>
                            <select id="menu" name="menu" class="form-control">
                                <option value="">All menus</option>
                                @foreach ($menus as $key => $label)
                                    <option value="{{ $key }}" {{ $key === $menuKey ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless
                    <div class="form-group col-md">
                        <label for="action">Activity</label>
                        <select id="action" name="action" class="form-control">
                            <option value="">All activities</option>
                            @foreach (\App\Models\CmsUserLog::ACTIONS as $key => $label)
                                <option value="{{ $key }}" {{ $key === ($filters['action'] ?? '') ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md">
                        <label for="from">From (WIB)</label>
                        <input id="from" name="from" type="date" class="form-control" value="{{ $filters['from'] ?? '' }}">
                    </div>
                    <div class="form-group col-md">
                        <label for="to">To (WIB)</label>
                        <input id="to" name="to" type="date" class="form-control" value="{{ $filters['to'] ?? '' }}">
                    </div>
                    <div class="form-group col-auto">
                        <button class="btn btn-primary" type="submit">Apply filters</button>
                        <a class="btn btn-light" href="{{ $listUrl }}">Reset</a>
                    </div>
                </div>
            </form>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <form method="GET" action="{{ $listUrl }}" class="form-inline">
                    @foreach ($reportFilters as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="per_page" class="mr-2">Rows per page</label>
                    <select id="per_page" name="per_page" class="custom-select custom-select-sm w-auto" onchange="this.form.submit()">
                        @foreach ($pageSizes as $size)
                            <option value="{{ $size }}" {{ $size === $logs->perPage() ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="btn btn-light btn-sm ml-2">Apply</button></noscript>
                </form>
                <small class="text-muted my-2">
                    @if ($logs->count())Showing {{ number_format($logs->firstItem()) }}–{{ number_format($logs->lastItem()) }} of @endif
                    {{ number_format($logs->total()) }} {{ \Illuminate\Support\Str::plural($detail ? 'entry' : 'group', $logs->total()) }}
                </small>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    @if ($detail)
                        <thead><tr><th>Time</th><th>Activity</th><th>Details</th><th>Outcome</th></tr></thead>
                        <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-nowrap">{{ $log->occurred_at->format($timestampFormat) }}<br><small>{{ $log->admin_name }} &middot; #{{ $log->id }}</small></td>
                                <td>{{ \App\Models\CmsUserLog::ACTIONS[$log->action] ?? $log->action }}
                                    @if ($log->control)<br><strong>{{ $log->control }}</strong>@endif
                                </td>
                                <td>
                                    <code>{{ $log->method }} {{ $log->route }}</code>
                                    @foreach ($log->record_ids ?? [] as $key => $id)<span class="badge badge-light">{{ $key }} #{{ $id }}</span>@endforeach
                                    @if ($log->fields)<div>Submitted fields &middot; {{ implode(', ', $log->fields) }}</div>@endif
                                </td>
                                <td>
                                    {{ $log->outcomeLabel() }}
                                    @if ($log->method)
                                        @if ($log->http_status)<small class="d-block">HTTP {{ $log->http_status }}</small>@endif
                                    @else
                                        <small class="d-block">Page log #{{ $log->page_log_id }}</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No activity matches these filters.</td></tr>
                        @endforelse
                        </tbody>
                    @else
                        <thead><tr><th>CMS admin</th><th>Menu</th><th>Server requests</th><th>Browser interactions</th><th>Total</th><th>Last activity</th><th><span class="sr-only">Details</span></th></tr></thead>
                        <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $admins[$log->cms_user_id] ?? 'Admin' }} #{{ $log->cms_user_id }}</td>
                                <td>{{ $menus[$log->menu] ?? $log->menu }}</td>
                                <td>{{ $log->server_total }}</td><td>{{ $log->total - $log->server_total }}</td><td><strong>{{ $log->total }}</strong></td>
                                <td class="text-nowrap">{{ $log->last_activity->format($timestampFormat) }}</td>
                                <td><a href="{{ route('users_log.show', ['admin' => $log->cms_user_id, 'menu' => $log->menu] + \Illuminate\Support\Arr::except($filters, ['admin_id', 'menu'])) }}">View activity</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No activity matches these filters.</td></tr>
                        @endforelse
                        </tbody>
                    @endif
                </table>
            </div>
            {{ $logs->onEachSide(1)->links() }}
        </div>
    </div>
</section>
@endsection
