{{-- Row cell: Status badge. Expects $post, $isActive, $isDeclined, $isDeactivated. --}}
@if ($isDeactivated)
    <span class="badge badge-secondary member-status-badge mini-badge">Deactivated</span>
@elseif ($isActive)
    <span class="badge badge-success member-status-badge mini-badge">Active</span>
    @if (!empty($post->two_step_verified))
        <br>
        <span class="badge badge-info mini-badge mt-1 d-inline-block"
            title="Verifikasi 2-Langkah oleh {{ $post->two_step_verified_by ?? 'Staff' }} ({{ $post->two_step_verified_at ? \Carbon\Carbon::parse($post->two_step_verified_at)->format('d M Y H:i') : '' }})"
            data-toggle="tooltip">
            <i class="fas fa-check-double mr-1"></i>2-Step
        </span>
    @endif
@elseif ($isDeclined)
    <span class="badge badge-danger member-status-badge mini-badge">Disqualified</span>
@else
    <span class="badge badge-warning member-status-badge mini-badge">Pending</span>
@endif
