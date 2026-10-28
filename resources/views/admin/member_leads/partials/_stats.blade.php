@php
    $summaryCards = [
        ['Total Leads', $countTotalLeads, 'users', 'blue', ['result' => 'all']],
        ['Eligible', $countEligible, 'check-circle', 'green', ['result' => 'all', 'eligibility' => 'eligible']],
        ['Do Not Send', $countDoNotSend, 'ban', 'red', ['result' => 'all', 'eligibility' => 'do_not_send']],
        ['HOT', $temperatureCounts['hot'] ?? 0, 'fire', 'hot', ['result' => 'all', 'temperature' => 'hot']],
        ['WARM', $temperatureCounts['warm'] ?? 0, 'thermometer-half', 'amber', ['result' => 'all', 'temperature' => 'warm']],
        ['COLD', $temperatureCounts['cold'] ?? 0, 'snowflake', 'cyan', ['result' => 'all', 'temperature' => 'cold']],
        ['Pending', $countPending, 'clock', 'amber', ['result' => 'pending']],
        ['Win', $countWin, 'trophy', 'green', ['result' => 'win']],
        ['Loss', $countLoss, 'times-circle', 'red', ['result' => 'loss']],
    ];
@endphp
<div class="lead-summary" aria-label="Lead overview">
    @foreach ($summaryCards as [$label, $count, $icon, $color, $query])
        <a href="{{ url('admin/leads') . '?' . http_build_query($query) }}" class="lead-stat lead-stat-{{ $color }}">
            <span class="lead-stat-icon"><i class="fas fa-{{ $icon }}" aria-hidden="true"></i></span>
            <span class="lead-stat-content">
                <span class="lead-stat-label">{{ $label }}</span>
                <span class="lead-stat-value">{{ number_format($count) }}
                    @if ($label !== 'Total Leads')
                        <small>{{ $countTotalLeads ? round($count / $countTotalLeads * 100) : 0 }}%</small>
                    @endif
                </span>
            </span>
        </a>
    @endforeach
</div>
