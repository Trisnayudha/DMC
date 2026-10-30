{{-- Counter cards — mengikuti filter yang aktif --}}

<div class="row">
    @foreach ([
        ['Total Inquiries', $stats['total'], 'bg-primary', 'inbox'],
        ['From Website', $stats['web'], 'bg-info', 'globe'],
        ['From Apps', $stats['apps'], 'bg-warning', 'mobile-alt'],
        ['From Active Members', $stats['members'], 'bg-success', 'user-check'],
    ] as [$title, $value, $color, $icon])
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon {{ $color }}"><i class="fas fa-{{ $icon }}"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>{{ $title }}</h4></div>
                    <div class="card-body">{{ number_format($value) }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>{{-- /row --}}
