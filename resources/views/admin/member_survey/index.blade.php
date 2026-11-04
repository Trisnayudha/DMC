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
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search mr-1"></i>Filter</button>
                        @if($search || $year)<a class="btn btn-light btn-sm" href="{{ route('admin.member-surveys.index') }}">Reset</a>@endif
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead><tr><th>Submitted</th><th>Name</th><th>Company</th><th>Position</th><th>Email</th><th></th></tr></thead>
                            <tbody>
                                @forelse($responses as $response)
                                    <tr>
                                        <td style="white-space:nowrap">{{ $response->created_at ? $response->created_at->format('d M Y H:i') : '—' }}</td>
                                        <td>{{ $response->full_name }}</td><td>{{ $response->company }}</td><td>{{ $response->position ?: '—' }}</td>
                                        <td>{{ $response->email }}</td>
                                        <td><a href="{{ route('admin.member-surveys.show', $response->id) }}" class="btn btn-sm btn-outline-primary">View response</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No survey responses found.</td></tr>
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
