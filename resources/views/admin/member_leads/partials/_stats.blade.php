{{-- Counter cards --}}

<div class="row">

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/leads?result=pending') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="fas fa-bullseye"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Pending Follow Up</h4></div>
                    <div class="card-body">{{ $countPending }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/leads?result=pending') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger"><i class="fas fa-clock"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Over SLA
                            <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                                title="Lead pending yang sudah lewat deadline follow up (48 jam sejak verifikasi)."
                                data-toggle="tooltip"></i>
                        </h4>
                    </div>
                    <div class="card-body">{{ $countOverSla }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/leads?result=win') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Win</h4></div>
                    <div class="card-body">{{ $countWin }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/leads?result=loss') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-secondary"><i class="fas fa-times-circle"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Loss</h4></div>
                    <div class="card-body">{{ $countLoss }}</div>
                </div>
            </div>
        </a>
    </div>

</div>{{-- /row --}}

<div class="row">

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/leads?result=all') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Total Leads</h4></div>
                    <div class="card-body">{{ $countTotalLeads }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-info"><i class="fas fa-percentage"></i></div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Conversion Rate
                        <i class="fas fa-info-circle text-muted ml-1" style="font-size:12px;"
                            title="Win ÷ Total Lead." data-toggle="tooltip"></i>
                    </h4>
                </div>
                <div class="card-body">{{ $conversionRate !== null ? $conversionRate . '%' : '—' }}</div>
            </div>
        </div>
    </div>

</div>{{-- /row 2 --}}
