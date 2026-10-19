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
                <ul class="list-unstyled">

                    @foreach ($dataSourceStats as $item)
                        <li class="mb-3">
                            <strong>{{ ucfirst($item->source) }}</strong><br>
                            {{ number_format($item->total) }} Members
                        </li>
                    @endforeach

                    @if ($dataSourceStats->count() == 0)
                        <li class="text-muted">
                            No data source available
                        </li>
                    @endif

                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                @php
                    $catTierTooltip = implode('<br>', array_merge(
                        ['<i>Jumlah member di company yang sudah verified.</i>'],
                        \App\Support\CompanyCategoryTier::tooltipLines()
                    ));
                @endphp
                <h4>
                    Company Category Distribution
                    <i class="fas fa-info-circle text-muted ml-1" style="font-size:13px;cursor:help;"
                        data-toggle="tooltip" data-html="true" data-placement="bottom"
                        title="{!! $catTierTooltip !!}"></i>
                </h4>
            </div>
            <div class="card-body">
                <canvas id="companyCategoryChart" height="220"></canvas>

                {{-- Breakdown: tiap Category N + kategori company di dalamnya. --}}
                @php $companyCategoryGrandTotal = array_sum(array_column($companyCategoryBreakdown ?? [], 'total')); @endphp
                <div class="mt-3" style="font-size:12px;">
                    @foreach ($companyCategoryBreakdown ?? [] as $groupLabel => $group)
                        <div class="d-flex align-items-start py-1" style="gap:8px;border-top:1px solid #f1f3f5;">
                            <span class="flex-shrink-0 mt-1" style="width:10px;height:10px;border-radius:2px;background:{{ $companyCategoryColors[$loop->index] ?? '#cdd3d8' }};"></span>
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="d-flex" style="gap:8px;">
                                    <span class="font-weight-bold">{{ $groupLabel }}</span>
                                    <span class="text-muted text-truncate" title="{{ $group['name'] }}">{{ $group['name'] }}</span>
                                    <span class="ml-auto font-weight-bold">{{ number_format($group['total'], 0, ',', '.') }}</span>
                                    <span class="text-muted text-right" style="width:40px;">
                                        {{ $companyCategoryGrandTotal ? number_format($group['total'] * 100 / $companyCategoryGrandTotal, 1, ',', '.') : 0 }}%
                                    </span>
                                </div>
                                @if (!empty($group['categories']))
                                    <div class="text-muted" style="font-size:11px;">
                                        @foreach ($group['categories'] as $categoryName => $categoryCount)
                                            {{ $categoryName }} <b>{{ number_format($categoryCount, 0, ',', '.') }}</b>@if (!$loop->last) · @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                @php
                    $jobTitleTierTooltip = implode('<br>', [
                        '<strong>Tier 1</strong>: CEO, COO, CFO, President, Commissioner, Owner, Founder, Partner, Board',
                        '<strong>Tier 2</strong>: Director, VP, SVP, EVP, General Manager, Country Manager, Head',
                        '<strong>Tier 3</strong>: Senior Manager, Manager, Mgr, Superintendent, Team Lead, Principal',
                        '<strong>Tier 4</strong>: Supervisor, Coordinator, Specialist, Engineer, Analyst, Officer, Advisor, Consultant',
                        '<strong>Tier 5</strong>: Staff, Admin, Administrator, Assistant, Junior, Intern, Trainee, Operator',
                        '<strong>Fallback</strong>: jika tidak kena keyword, masuk Tier 4',
                    ]);
                @endphp
                <h4 class="mb-0">
                    Tier Job Title Distribution
                    <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-html="true"
                        data-placement="top" style="cursor: help;" title="{!! $jobTitleTierTooltip !!}"></i>
                </h4>
            </div>
            <div class="card-body">
                <canvas id="jobTitleTierChart" height="220"></canvas>
            </div>
        </div>
    </div>
    {{-- Donut Active vs Inactive --}}
    <div class="col-lg-4 col-md-12 col-12">
        <div class="card">
            <div class="card-header">
                <h4>Member Activity Status</h4>
            </div>
            <div class="card-body">
                <canvas id="membershipStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>

</div>
<div class="row">
    {{-- Insights 3 angka --}}
    <div class="col-lg-12 col-md-12 col-12">
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
                    {{-- <div class="col-4">
                        <h5>{{ $avgNewsPerMember ?? 0 }}</h5>
                        <p class="text-muted mb-0">Avg News / Member</p>
                    </div> --}}
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
