{{-- Stats cards + self-edit banner --}}

<div class="row">

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?status_member=active') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-user-check"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Active Members</h4></div>
                    <div class="card-body">{{ $countActiveMember }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?status_member=pending') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="fas fa-user-clock"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>Pending Verification</h4></div>
                    <div class="card-body">{{ $countPendingMember }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <a href="{{ url('admin/users?filter=this_month') }}" class="text-decoration-none">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary"><i class="fas fa-user-plus"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>New This Month</h4></div>
                    <div class="card-body">{{ $countNewThisMonth }}</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-info"><i class="fab fa-mailchimp"></i></div>
            <div class="card-wrap">
                <div class="card-header"><h4>Mailchimp Contacts</h4></div>
                <div class="card-body" id="mc-contact-count">
                    <span class="spinner-border spinner-border-sm text-info" role="status"></span>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /stats row --}}

@if ($countSelfEdited > 0)
    <div class="alert alert-warning alert-dismissible show fade d-flex align-items-center py-2 mb-3" style="gap:10px;">
        <i class="fas fa-exclamation-triangle fa-lg"></i>
        <div class="flex-grow-1">
            <strong>{{ $countSelfEdited }} user</strong> telah mengubah data mereka sendiri melalui apps/website.
            <a href="{{ url('admin/users?filter=self_edited') }}" class="font-weight-bold ml-2">Lihat daftar →</a>
        </div>
        <button type="button" class="close ml-2" data-dismiss="alert"><span>×</span></button>
    </div>
@endif
