@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Event Survey</h1>
            <div class="section-header-breadcrumb"><div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div><div class="breadcrumb-item active">Event Survey</div></div>
        </div>
        <div class="section-body">
            @include('admin.event_survey._partials.summary')
            <div class="card">
                <div class="card-header"><div><h4>Survey Responses</h4><p class="small text-muted mb-0">{{ number_format($responses->total()) }} response{{ $responses->total() === 1 ? '' : 's' }} found</p></div></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.event-surveys.index') }}" class="form-inline mb-3" style="gap:8px">
                        <label class="small mb-0" for="survey-event">Event code</label>
                        <select id="survey-event" name="event" class="form-control form-control-sm" style="min-width:320px" onchange="this.form.submit()">
                            <option value="">All events</option>
                            @foreach($events as $code => $label)
                                <option value="{{ $code }}" @if($event === (string) $code) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="survey-search">Search email</label>
                        <input id="survey-search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search email" style="min-width:220px" maxlength="255">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search mr-1"></i>Filter</button>
                        @if($search)<a class="btn btn-light btn-sm" href="{{ route('admin.event-surveys.index', ['event' => $event]) }}">Reset</a>@endif
                        <a class="btn btn-success btn-sm ml-auto" href="{{ route('admin.event-surveys.export', ['event' => $event, 'search' => $search]) }}" title="Exports all rows matching the current event code and search"><i class="fas fa-file-excel mr-1"></i>Export Excel ({{ number_format($responses->total()) }})</a>
                    </form>
                    <div class="table-responsive">
                        @if($legacy)
                            @include('admin.event_survey._partials.table_legacy')
                        @else
                            @include('admin.event_survey._partials.table_current')
                        @endif
                    </div>
                    {{ $responses->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
