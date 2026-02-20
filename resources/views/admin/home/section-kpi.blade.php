<div class="row">

    {{-- 1. New Members --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="far fa-user-plus"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>New Members</h4>
                </div>
                <div class="card-body">
                    {{ number_format($newMembersThisMonth ?? 0) }}

                    @php
                        $gp = $growthPercent ?? 0;
                        $isUp = $gp >= 0;
                    @endphp

                    <div class="text-small {{ $isUp ? 'text-success' : 'text-danger' }}">
                        {{ $isUp ? '+' : '' }}{{ $gp }}% this month
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Active Members --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Active Members</h4>
                </div>
                <div class="card-body">
                    {{ number_format($activeMembers ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Expiring Membership --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
                <i class="fas fa-hourglass-end"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Expiring (30 Days)</h4>
                </div>
                <div class="card-body">
                    {{ number_format($expiring30Days ?? 0) }}

                    {{-- optional info kecil --}}
                    <div class="text-small text-muted" style="margin-top:6px;">
                        Inactive &gt; 1 year: {{ number_format($inactiveOver1Year ?? 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Total Events --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-info">
                <i class="far fa-calendar-alt"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Events</h4>
                </div>
                <div class="card-body">
                    {{ number_format($totalEvents ?? 0) }}
                </div>
            </div>
        </div>
    </div>

</div>
<div class="row">

    {{-- 5. Upcoming Events --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Upcoming Events</h4>
                </div>
                <div class="card-body">
                    {{ number_format($upcomingEvents ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- 6. Event Registrations --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Event Registrations</h4>
                </div>
                <div class="card-body">
                    {{ number_format($eventRegistrations ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- 7. Published News --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success">
                <i class="far fa-newspaper"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Published News</h4>
                </div>
                <div class="card-body">
                    {{ number_format($publishedNews ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- 8. News Views --}}
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-dark">
                <i class="fas fa-eye"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>News Views</h4>
                </div>
                <div class="card-body">
                    {{ number_format($newsViewsThisMonth ?? 0) }}
                    <div class="text-small text-muted">This month</div>
                </div>
            </div>
        </div>
    </div>

</div>
