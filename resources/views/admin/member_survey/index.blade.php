@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Member Survey</h1>
            <div class="section-header-breadcrumb"><div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div><div class="breadcrumb-item active">Member Survey</div></div>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-header"><div><h4>Survey Responses</h4><p class="small text-muted mb-0">{{ number_format($responses->total()) }} response{{ $responses->total() === 1 ? '' : 's' }} found</p></div></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.member-surveys.index') }}" class="form-inline mb-3" style="gap:8px">
                        <label class="sr-only" for="survey-search">Search name, company, or email</label>
                        <input id="survey-search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name, company, or email" style="min-width:240px" maxlength="255">
                        <label class="sr-only" for="survey-year">Submission year</label>
                        <input id="survey-year" type="number" name="year" value="{{ $year }}" min="2000" max="2100" class="form-control form-control-sm" placeholder="Submission year" style="width:145px">
                        <label class="sr-only" for="survey-status">Status</label>
                        <select id="survey-status" name="status" class="form-control form-control-sm">
                            <option value="">All statuses</option>
                            <option value="completed" @if($status === 'completed') selected @endif>Completed ({{ number_format($statusCounts['completed'] ?? 0) }})</option>
                            <option value="in_progress" @if($status === 'in_progress') selected @endif>In progress ({{ number_format($statusCounts['in_progress'] ?? 0) }})</option>
                        </select>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search mr-1"></i>Filter</button>
                        @if($search || $year || $status)<a class="btn btn-light btn-sm" href="{{ route('admin.member-surveys.index') }}">Reset</a>@endif
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead><tr><th>Last Activity</th><th>Status</th><th>Name</th><th>Company</th><th>Position</th><th>Email</th><th></th></tr></thead>
                            <tbody>
                                @forelse($responses as $response)
                                    <tr>
                                        <td style="white-space:nowrap">{{ $response->updated_at ? $response->updated_at->format('d M Y H:i') : '—' }}</td>
                                        <td style="white-space:nowrap">
                                            @if($response->isCompleted())
                                                <span class="badge badge-success">Completed</span>
                                            @else
                                                <span class="badge badge-warning">In progress</span>
                                                <div class="small text-muted mt-1">Reached step {{ $response->last_step ?: 1 }} of {{ \App\Models\DmcMemberSurvey::TOTAL_STEPS }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $response->full_name ?: '—' }}</td><td>{{ $response->company ?: '—' }}</td><td>{{ $response->position ?: '—' }}</td>
                                        <td>{{ $response->email ?: '—' }}</td>
                                        <td><a href="{{ route('admin.member-surveys.show', $response->id) }}" class="btn btn-sm btn-outline-primary">View response</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No survey responses found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $responses->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
