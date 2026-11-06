@extends('layouts.inspire.master')
@section('content')
<div class="content-wrapper"><section class="section">
    <div class="section-header"><h1>Event Questions</h1></div>
    <div class="section-body"><div class="card">
        <div class="card-header"><div><h4>Submitted Questions</h4><p class="small text-muted mb-0">{{ number_format($questions->total()) }} question(s)</p></div></div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <label class="sr-only" for="search">Search questions</label>
                <input id="search" name="search" value="{{ $search }}" maxlength="255" class="form-control form-control-sm mr-2" placeholder="Search name, email or question">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>
            <div class="table-responsive"><table class="table table-striped">
                <thead><tr><th>Submitted</th><th>Event</th><th>Name</th><th>Email</th><th>Company</th><th>Question</th></tr></thead>
                <tbody>@forelse($questions as $question)
                    <tr><td>{{ $question->created_at->format('d M Y H:i') }}</td><td>{{ $question->event_slug === 'indonesian-energy-market-briefing-2026' ? 'Indonesian Energy Market Briefing 2026' : $question->event_slug }}</td><td>{{ $question->name }}</td><td>{{ $question->email }}</td><td>{{ $question->company ?: '—' }}</td><td style="white-space: pre-wrap; overflow-wrap: anywhere; min-width: 250px">{{ $question->question }}</td></tr>
                @empty <tr><td colspan="6" class="text-center text-muted">No questions submitted yet.</td></tr> @endforelse</tbody>
            </table></div>
            {{ $questions->links('pagination::bootstrap-4') }}
        </div>
    </div></div>
</section></div>
@endsection
