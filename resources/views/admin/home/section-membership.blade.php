<div class="row">
    <div class="col-lg-8 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                <h4>Membership Growth</h4>
                <div class="card-header-action">
                    <span class="badge badge-primary">Last 6 Months</span>
                </div>
            </div>
            <div class="card-body">
                <canvas id="membershipGrowthChart" height="150"></canvas>
            </div>
        </div>
    </div>

    {{-- Summary kanan --}}
    <div class="col-lg-4 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                <h4>Quick Insights</h4>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <strong>Best Month</strong><br>
                        {{ $bestMonthLabel }} ({{ number_format($bestMonthValue ?? 0) }} New Members)
                    </li>
                    <li class="mb-3">
                        <strong>Lowest Month</strong><br>
                        {{ $lowestMonthLabel }} ({{ number_format($lowestMonthValue ?? 0) }} New Members)
                    </li>
                    <li class="mb-3">
                        <strong>Avg / Month</strong><br>
                        {{ number_format($avgPerMonth ?? 0) }} Members
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Donut Active vs Inactive --}}
    <div class="col-lg-6 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                <h4>Member Activity Status</h4>
            </div>
            <div class="card-body">
                <canvas id="membershipStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Insights 3 angka --}}
    <div class="col-lg-6 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                <h4>Member Engagement Insights</h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h5>{{ $activePercent ?? 0 }}%</h5>
                        <p class="text-muted mb-0">Active Members</p>
                    </div>
                    <div class="col-4">
                        <h5>{{ $joinedEventPercent ?? 0 }}%</h5>
                        <p class="text-muted mb-0">Joined Event</p>
                    </div>
                    <div class="col-4">
                        <h5>{{ $avgNewsPerMember ?? 0 }}</h5>
                        <p class="text-muted mb-0">Avg News / Member</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table inactive --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Inactive Members (30+ Days)</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Company</th>
                                <th>Last Activity</th>
                                <th>Joined At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($inactiveRows ?? []) as $row)
                                <tr>
                                    <td>
                                        {{ $row->name ?? '-' }}
                                        <div class="text-small text-muted">{{ $row->email ?? '' }}</div>
                                    </td>
                                    <td>{{ $row->company_name ?? '-' }}</td>
                                    <td>{{ $row->days_inactive }} days ago</td>
                                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}</td>
                                    <td><span class="badge {{ $row->badge_class }}">{{ $row->badge_text }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-4">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
