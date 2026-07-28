{{-- Counter cards --}}

<div class="row">

    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/company-follow-ups?status=needs_follow_up') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="fas fa-people-arrows"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Need Follow Up</h4></div>
                    <div class="card-body">{{ $countNeedsFollowUp }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/company-follow-ups?status=verified') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Verified</h4></div>
                    <div class="card-body">{{ $countVerified }}</div>
                </div>
            </div>
        </a>
    </div>

</div>{{-- /row --}}
