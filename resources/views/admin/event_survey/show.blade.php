@extends('layouts.inspire.master')

@section('content')
@php
    $yesNo = function ($value) { return $value === null ? null : ($value ? 'Yes' : 'No'); };
    $rating = function ($value) { return $value !== null ? $value . ' / 5' : null; };
    if ($response->isLegacy()) {
        $fields = [
            'Informative score' => $rating($response->informative_score),
            'Most relevant presentations' => is_array($response->most_relevant_presentations) ? implode("\n", $response->most_relevant_presentations) : null,
            'DMC member' => $yesNo($response->is_member),
            'Wants more information' => $yesNo($response->wants_more_info),
            'Feedback' => $response->feedback,
            'Topics for 2026' => $response->topics_2026,
        ];
    } else {
        $fields = [
            'Overall event rating' => $rating($response->event_rating),
            'Content rating' => $rating($response->rating_content),
            'Networking rating' => $rating($response->rating_networking),
            'What they liked most' => $response->liked_most,
            'What could be improved' => $response->improvement_feedback,
            'Topic recommendation' => $response->topic_recommendation,
            'Wants to speak with McCloskey experts' => $yesNo($response->wants_expert_contact),
            'App activated' => $response->app_activated,
        ];
    }
@endphp
<div class="content-wrapper"><section class="section">
    <div class="section-header"><h1>Survey Response</h1><div class="section-header-breadcrumb"><div class="breadcrumb-item"><a href="{{ route('admin.event-surveys.index', ['event' => $response->event_code ?: \App\Models\SurveyResponse::LEGACY_FILTER]) }}">Event Survey</a></div><div class="breadcrumb-item active">Response #{{ $response->id }}</div></div></div>
    <div class="section-body">
        <div class="mb-3">
            <h4 class="mb-1">{{ $response->email }}</h4>
            <div class="text-muted small">
                {{ \App\Models\SurveyResponse::eventLabel($response->event_code) }}
                @if($response->event_code) &middot; <code>{{ $response->event_code }}</code>@endif
                &middot; Submitted {{ $response->created_at ? $response->created_at->format('d M Y H:i') : '—' }}
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4>Answers</h4></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    @foreach($fields as $label => $value)
                        <tr>
                            <th style="width:32%" class="text-muted font-weight-normal">{{ $label }}</th>
                            <td style="white-space:pre-line">{{ ($value === null || $value === '') ? '—' : $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4>Submission Details</h4></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <tr><th style="width:32%" class="text-muted font-weight-normal">IP address</th><td>{{ $response->ip ?: '—' }}</td></tr>
                    <tr><th class="text-muted font-weight-normal">User agent</th><td>{{ $response->ua ?: '—' }}</td></tr>
                </table>
            </div>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i>Back</a>
    </div>
</section></div>
@endsection
