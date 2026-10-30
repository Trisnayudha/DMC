{{-- Tabel Sponsor Log: satu baris per aktivitas, perubahan per field old → new --}}

@php
    $actionBadges = [
        'created'       => ['success', 'Created'],
        'updated'       => ['info', 'Updated'],
        'deleted'       => ['danger', 'Deleted'],
        'registered'    => ['primary', 'Registered to event'],
        'ticket_resent' => ['secondary', 'Ticket resent'],
    ];
    $show = function ($value) {
        if ($value === null || $value === '') {
            return '—';
        }
        $text = is_scalar($value) ? (string) $value : json_encode($value);
        return \Illuminate\Support\Str::limit($text, 120);
    };
@endphp

<div class="card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-history mr-1"></i>Activity</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th class="text-nowrap">Time</th>
                        <th>Sponsor</th>
                        <th>Section</th>
                        <th>Action</th>
                        <th style="min-width:360px;">Changes</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs ?? [] as $log)
                        @php [$badge, $actionLabel] = $actionBadges[$log->action] ?? ['light', $log->action]; @endphp
                        <tr>
                            <td class="text-nowrap">{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                @if ($log->sponsor)
                                    <a href="{{ route('sponsors.logs', ['sponsor_id' => $log->sponsor_id]) }}" title="{{ $log->sponsor->name }}">
                                        {{ $log->sponsor->branding_name ?: $log->sponsor->name }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $log->sponsor_id ? '#' . $log->sponsor_id . ' (deleted)' : '—' }}</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $log->subject }}@if ($log->subject_id) <span class="text-muted small">#{{ $log->subject_id }}</span>@endif</td>
                            <td class="text-nowrap"><span class="badge badge-{{ $badge }}">{{ $actionLabel }}</span></td>
                            <td class="small">
                                @if ($log->description)
                                    <div class="mb-1">{{ $log->description }}</div>
                                @endif
                                @foreach ($log->changes ?? [] as $field => $diff)
                                    <div>
                                        <strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong>:
                                        @if ($log->action === 'updated')
                                            <span class="text-danger">{{ $show($diff['old'] ?? null) }}</span>
                                            <i class="fas fa-long-arrow-alt-right text-muted mx-1"></i>
                                            <span class="text-success">{{ $show($diff['new'] ?? null) }}</span>
                                        @else
                                            {{ $show($log->action === 'deleted' ? ($diff['old'] ?? null) : ($diff['new'] ?? null)) }}
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                            <td class="text-nowrap">{{ $log->cms_user_name ?: 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs)
            <div class="mt-3">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
