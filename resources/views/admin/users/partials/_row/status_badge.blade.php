{{-- Row cell: Status badge. Expects $post, $isActive, $isDeclined, $isDeactivated,
     $isInVerification, $isLead. --}}
@if ($isDeactivated)
    <span class="badge badge-secondary member-status-badge mini-badge">Deactivated</span>
    @if (!empty($post->deactivation_reason))
        <br>
        <span class="text-muted d-inline-block mt-1" style="font-size:9.5px; max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:bottom;"
            title="Deactivated by {{ $post->deactivated_by ?? 'Staff' }} ({{ $post->deactivated_at ? \Carbon\Carbon::parse($post->deactivated_at)->format('d M Y H:i') : '' }}): {{ $post->deactivation_reason }}"
            data-toggle="tooltip">
            <i class="fas fa-info-circle mr-1"></i>{{ \Illuminate\Support\Str::limit($post->deactivation_reason, 22) }}
        </span>
    @endif
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
@elseif ($isInVerification)
    <span class="badge badge-warning member-status-badge mini-badge"
        title="Admin sudah mulai review — SLA berjalan" data-toggle="tooltip">Verification</span>
@else
    <span class="badge badge-warning member-status-badge mini-badge"
        title="Baru masuk, belum ada admin yang mulai review" data-toggle="tooltip">New</span>
@endif
@if ($isLead)
    <br>
    <span class="badge badge-primary mini-badge mt-1 d-inline-block"
        title="Explore Marketing — masuk daftar Lead Follow-Up" data-toggle="tooltip">
        <i class="fas fa-bullseye mr-1"></i>Lead
    </span>
@endif
