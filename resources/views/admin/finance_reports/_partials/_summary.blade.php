@php
    $rp = function ($value) { return 'Rp ' . number_format($value, 0, ',', '.'); };
    $cards = [
        ['Total Amount', $totals['amount'], 'fa-file-invoice', 'bg-primary', $totals['count'] . ' invoices · before VAT'],
        ['Total Net Amount', $totals['net'], 'fa-receipt', 'bg-info', 'Amount + VAT'],
        ['Total Actual Amount', $totals['actual'], 'fa-wallet', 'bg-success', 'Amount − PPh 23'],
        ['Total PPh 23', $totals['pph23'], 'fa-percent', 'bg-warning', 'Withheld by sponsors'],
    ];
@endphp
<div class="row">
    @foreach ($cards as $card)
        <div class="col-md-6 col-lg-3">
            <div class="card card-statistic-1">
                <div class="card-icon {{ $card[3] }}"><i class="fas {{ $card[2] }}"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>{{ $card[0] }}</h4></div>
                    <div class="card-body" style="font-size:17px;">{{ $rp($card[1]) }}</div>
                    <div class="text-muted px-4 pb-2" style="font-size:11px;margin-top:-6px;">{{ $card[4] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
