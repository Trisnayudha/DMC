@extends('layouts.inspire.master')

@section('content')
@php
    $sections = [
        'Member Information' => [
            'Full name' => $response->full_name, 'Company / Organization' => $response->company,
            'Position / Title' => $response->position, 'Email' => $response->email, 'Mobile number' => $response->phone,
            'LinkedIn / Professional Profile' => $response->linkedin,
        ],
        'Program Expectations & Input' => [
            'Event types' => $response->event_types, 'Other event type' => $response->event_types_other,
            'Topics of interest' => $response->topics_interest, 'Recommended speakers' => $response->speaker_wishlist,
            'Award nominee' => $response->nominee_name, 'Nominee company / institution' => $response->nominee_company,
            'Reason for nomination' => $response->nominee_reason, 'Event improvement aspects' => $response->event_improvement_aspects,
            'Other event improvement' => $response->event_improvement_other, 'Previous event improvement feedback' => $response->event_improvement,
        ],
        'Marketing & Communication' => [
            'Social media familiarity' => $response->social_familiarity, 'Platforms followed' => $response->platforms,
            'App & website awareness' => $response->app_awareness, 'App / website usage frequency' => $response->usage_frequency,
            'Preferred channels' => $response->preferred_channels, 'Email primary goal' => $response->email_primary_goal,
            'Other email goal' => $response->email_primary_goal_other, 'Preferred email frequency' => $response->email_frequency,
            'Previous preferred email day' => $response->email_best_day, 'Communication suggestions' => $response->communication_feedback,
        ],
        'Additional Feedback' => ['Comments / suggestions' => $response->additional_feedback],
    ];
@endphp
<div class="content-wrapper"><section class="section">
    <div class="section-header"><h1>Survey Response</h1><div class="section-header-breadcrumb"><div class="breadcrumb-item"><a href="{{ route('admin.member-surveys.index') }}">Member Survey</a></div><div class="breadcrumb-item active">Response #{{ $response->id }}</div></div></div>
    <div class="section-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:10px">
            <div><h4 class="mb-1">{{ $response->full_name }}</h4><div class="text-muted small">{{ $response->company }} &middot; Submitted {{ $response->created_at ? $response->created_at->format('d M Y H:i') : '—' }}</div></div>
            <a href="{{ route('admin.member-surveys.index') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left mr-1"></i>Back to responses</a>
        </div>
        @foreach($sections as $heading => $fields)
            <div class="card"><div class="card-header"><h4>{{ $heading }}</h4></div>
                <div class="card-body py-2"><dl class="row mb-0">
                    @foreach($fields as $label => $value)
                        @php($display = is_array($value) ? implode(', ', array_filter($value, function ($item) { return is_scalar($item) && trim((string) $item) !== ''; })) : $value)
                        <dt class="col-md-4 text-muted py-2" style="font-size:12px">{{ $label }}</dt>
                        <dd class="col-md-8 py-2 mb-0" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ is_scalar($display) && trim((string) $display) !== '' ? $display : '—' }}</dd>
                    @endforeach
                </dl></div>
            </div>
        @endforeach
    </div>
</section></div>
@endsection
