@php
    $stateLabels = [
        'renewed'     => 'Renewed',
        'new_sponsor' => 'New Sponsor',
        'not_renewed' => 'Not Renewed',
    ];
    $activeState = request('renewal_state');
    $activeYear  = request('renewal_year');
    $cardSuffix  = ($activeYear || $activeState)
        ? ' ' . ($activeYear ?? '') . ($activeState ? ' · ' . ($stateLabels[$activeState] ?? '') : '')
        : '';
@endphp

<!-- Package Count Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary"><i class="fas fa-medal"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Platinum{{ $cardSuffix }}</h4></div>
                <div class="card-body">{{ $platinumCount ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning"><i class="fas fa-trophy"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Gold{{ $cardSuffix }}</h4></div>
                <div class="card-body">{{ $goldCount ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-secondary"><i class="fas fa-medal"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Silver{{ $cardSuffix }}</h4></div>
                <div class="card-body">{{ $silverCount ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success"><i class="fas fa-users"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Total{{ $cardSuffix }}</h4></div>
                <div class="card-body">{{ $totalCount ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Benefit Usage Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-info"><i class="fas fa-boxes"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Total Benefits Assigned</h4></div>
                <div class="card-body">{{ $totalBenefitsAssigned }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Total Benefits Used</h4></div>
                <div class="card-body">{{ $totalBenefitsUsed }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Benefits Unused</h4></div>
                <div class="card-body">{{ $totalBenefitsUnused }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary"><i class="fas fa-percentage"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Usage Rate</h4></div>
                <div class="card-body">{{ $benefitUsageRate }}%</div>
            </div>
        </div>
    </div>
</div>
