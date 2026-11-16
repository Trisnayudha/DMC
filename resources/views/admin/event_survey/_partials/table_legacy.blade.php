@php
    $clip = function ($text) { return $text ? \Illuminate\Support\Str::limit($text, 80) : '—'; };
    $yesNo = function ($value) { return $value === null ? '—' : ($value ? 'Yes' : 'No'); };
@endphp
<table class="table table-striped table-hover table-sm small">
    <thead>
        <tr>
            <th>Submitted</th><th>Email</th><th>Informative</th><th>Most Relevant Presentations</th>
            <th>Member</th><th>Wants More Info</th><th>Feedback</th><th>Topics 2026</th><th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($responses as $response)
            @php $presentations = is_array($response->most_relevant_presentations) ? implode('; ', $response->most_relevant_presentations) : ''; @endphp
            <tr>
                <td style="white-space:nowrap">{{ $response->created_at ? $response->created_at->format('d M Y H:i') : '—' }}</td>
                <td>{{ $response->email }}</td>
                <td class="text-center">{{ $response->informative_score ?? '—' }}</td>
                <td title="{{ $presentations }}">{{ $clip($presentations) }}</td>
                <td class="text-center">{{ $yesNo($response->is_member) }}</td>
                <td class="text-center">{{ $yesNo($response->wants_more_info) }}</td>
                <td title="{{ $response->feedback }}">{{ $clip($response->feedback) }}</td>
                <td title="{{ $response->topics_2026 }}">{{ $clip($response->topics_2026) }}</td>
                <td><a href="{{ route('admin.event-surveys.show', $response->id) }}" class="btn btn-sm btn-outline-primary" style="white-space:nowrap">View</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No survey responses found.</td></tr>
        @endforelse
    </tbody>
</table>
