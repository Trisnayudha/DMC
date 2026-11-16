@php
    $avg = function ($value) { return $value !== null ? number_format((float) $value, 2) . ' / 5' : '—'; };
    $total = (int) ($summary->total ?? 0);
    if ($legacy) {
        $tiles = [
            ['Responses', number_format($total), 'poll'],
            ['Avg. Informative Score', $avg($summary->avg_informative), 'star'],
            ['Members', number_format((int) $summary->members), 'user-check'],
            ['Wants More Info', number_format((int) $summary->more_info_yes), 'info-circle'],
        ];
    } else {
        $tiles = [
            ['Responses', number_format($total), 'poll'],
            ['Avg. Overall Rating', $avg($summary->avg_event), 'star'],
            ['Avg. Content Rating', $avg($summary->avg_content), 'book-open'],
            ['Avg. Networking Rating', $avg($summary->avg_networking), 'handshake'],
            ['Wants Expert Contact', number_format((int) $summary->expert_yes), 'user-tie'],
        ];
    }
@endphp
<div class="row">
    @foreach($tiles as $tile)
        <div class="col-lg col-md-4 col-sm-6">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary"><i class="fas fa-{{ $tile[2] }}"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>{{ $tile[0] }}</h4></div>
                    <div class="card-body" style="font-size:18px">{{ $tile[1] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
