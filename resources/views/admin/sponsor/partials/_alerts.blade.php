@php
    $monthShort = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                   '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
@endphp

@if ($expiredSponsors->count() > 0)
<div class="alert alert-danger py-2 mb-2">
    <div class="d-flex align-items-center justify-content-between">
        <strong><i class="fas fa-exclamation-circle mr-1"></i> Expired Contracts ({{ $expiredSponsors->count() }})</strong>
    </div>
    <div class="d-flex flex-wrap mt-2" style="gap:6px;">
        @foreach ($expiredSponsors as $sponsor)
            @php $p = explode('-', $sponsor->contract_end); @endphp
            <span class="badge badge-light border border-danger text-dark" style="font-size:12px;padding:5px 8px;font-weight:normal;">
                {{ $sponsor->name }}
                <span class="text-danger ml-1">{{ $monthShort[$p[1]] }} {{ $p[0] }}</span>
                <a href="#" class="update-contract-btn ml-1 text-info"
                   data-sponsor-id="{{ $sponsor->id }}"
                   data-contract-start="{{ $sponsor->contract_start }}"
                   data-contract-end="{{ $sponsor->contract_end }}"
                   data-package="{{ $sponsor->package }}"
                   title="Update Contract"><i class="fas fa-edit"></i></a>
            </span>
        @endforeach
    </div>
</div>
@endif

@if ($renewalSponsors->count() > 0)
<div class="alert alert-warning py-2 mb-2">
    <div class="d-flex align-items-center justify-content-between">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Renewal Soon — {{ $renewalSponsors->count() }} sponsor{{ $renewalSponsors->count() === 1 ? '' : 's' }} expiring within 3 months</strong>
    </div>
    <div class="d-flex flex-wrap mt-2" style="gap:6px;">
        @foreach ($renewalSponsors as $sponsor)
            @php
                $endDate  = \Carbon\Carbon::createFromFormat('Y-m', $sponsor->contract_end)->endOfMonth();
                $daysLeft = (int) now()->diffInDays($endDate, false);
                $pill     = $daysLeft <= 30 ? 'danger' : ($daysLeft <= 60 ? 'warning' : 'secondary');
            @endphp
            <span class="badge badge-light border text-dark" style="font-size:12px;padding:5px 8px;font-weight:normal;">
                {{ $sponsor->name }}
                <span class="badge badge-{{ $pill }} ml-1">{{ $daysLeft }}d</span>
                <a href="#" class="update-contract-btn ml-1 text-info"
                   data-sponsor-id="{{ $sponsor->id }}"
                   data-contract-start="{{ $sponsor->contract_start }}"
                   data-contract-end="{{ $sponsor->contract_end }}"
                   data-package="{{ $sponsor->package }}"
                   title="Update Contract / Renewal"><i class="fas fa-edit"></i></a>
            </span>
        @endforeach
    </div>
</div>
@endif
