<div class="row mb-3">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary"><i class="fas fa-building"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Active Sponsors</h4></div>
                <div class="card-body">{{ $sponsors->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1">
            <div class="card-icon bg-secondary"><i class="fas fa-user-tie"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Total PICs</h4></div>
                <div class="card-body">{{ $totalPics }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Billing Contacts</h4></div>
                <div class="card-body">{{ $totalBillings }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success"><i class="fas fa-user-friends"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Total Representatives</h4></div>
                <div class="card-body">{{ $totalReps }}</div>
            </div>
        </div>
    </div>
</div>
