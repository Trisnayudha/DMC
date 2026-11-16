@php
    $clip = function ($text) { return $text ? \Illuminate\Support\Str::limit($text, 80) : '—'; };
@endphp
<table class="table table-striped table-hover table-sm small">
    <thead>
        <tr>
            <th>Submitted</th>
            @if($event === '')<th>Event</th>@endif
            <th>Email</th>
            <th title="Overall event rating">Overall</th><th>Content</th><th>Networking</th>
            <th>Liked Most</th><th>Improvement</th><th>Topic Recommendation</th>
            <th title="Wants to speak with McCloskey experts">Expert Contact</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($responses as $response)
            <tr>
                <td style="white-space:nowrap">{{ $response->created_at ? $response->created_at->format('d M Y H:i') : '—' }}</td>
                @if($event === '')<td>{{ \App\Models\SurveyResponse::eventLabel($response->event_code) }}<div class="text-muted"><code>{{ $response->event_code ?: '—' }}</code></div></td>@endif
                <td>{{ $response->email }}</td>
                @if($response->isLegacy())
                    <td colspan="6" class="text-muted">First survey format — informative score {{ $response->informative_score ?? '—' }} / 5. Open the response for details.</td>
                @else
                    <td class="text-center">{{ $response->event_rating ?? '—' }}</td>
                    <td class="text-center">{{ $response->rating_content ?? '—' }}</td>
                    <td class="text-center">{{ $response->rating_networking ?? '—' }}</td>
                    <td title="{{ $response->liked_most }}">{{ $clip($response->liked_most) }}</td>
                    <td title="{{ $response->improvement_feedback }}">{{ $clip($response->improvement_feedback) }}</td>
                    <td title="{{ $response->topic_recommendation }}">{{ $clip($response->topic_recommendation) }}</td>
                @endif
                <td class="text-center">
                    @if($response->wants_expert_contact === null)
                        —
                    @elseif($response->wants_expert_contact)
                        <span class="badge badge-success">Yes</span>
                    @else
                        <span class="badge badge-light">No</span>
                    @endif
                </td>
                <td><a href="{{ route('admin.event-surveys.show', $response->id) }}" class="btn btn-sm btn-outline-primary" style="white-space:nowrap">View</a></td>
            </tr>
        @empty
            <tr><td colspan="11" class="text-center text-muted py-4">No survey responses found.</td></tr>
        @endforelse
    </tbody>
</table>
