{{-- Satu tabel invoice sponsor (Unpaid atau Paid). Param: $title, $icon, $color, $rows, $totals, $empty. --}}
@php $rp = function ($value) { return number_format($value, 0, ',', '.'); }; @endphp
<div class="card" style="border-top:3px solid {{ $color }};">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <h4 class="mb-0" style="color:{{ $color }};">
            <i class="fas {{ $icon }} mr-1"></i> {{ $title }}
            <span class="badge ml-1" style="background:{{ $color }};color:#fff;">{{ $totals['count'] }}</span>
        </h4>
        <div class="text-muted" style="font-size:12px;">
            Amount <strong class="text-dark">Rp {{ $rp($totals['amount']) }}</strong>
            &middot; Net <strong class="text-dark">Rp {{ $rp($totals['net']) }}</strong>
            &middot; PPh 23 <strong class="text-dark">Rp {{ $rp($totals['pph23']) }}</strong>
            &middot; Actual <strong class="text-dark">Rp {{ $rp($totals['actual']) }}</strong>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:12.5px;">
                <thead class="thead-light">
                    <tr>
                        <th style="width:36px;">#</th>
                        <th style="min-width:130px;">Invoice</th>
                        <th style="min-width:180px;">Sponsor</th>
                        <th style="min-width:220px;">Description</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">VAT</th>
                        <th class="text-right">Net Amount</th>
                        <th class="text-right">PPh 23</th>
                        <th class="text-right">Actual Amount</th>
                        <th style="min-width:110px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $row)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="font-weight-600" style="font-family:monospace;">{{ $row['invoice_no'] }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $row['invoice_date']->format('d M Y') }}</div>
                            </td>
                            <td class="font-weight-600">{{ $row['sponsor'] }}</td>
                            <td class="text-muted">{{ $row['description'] }}</td>
                            <td class="text-right">{{ $rp($row['amount']) }}</td>
                            <td class="text-right">{{ $row['vat'] > 0 ? $rp($row['vat']) : '—' }}</td>
                            <td class="text-right">{{ $rp($row['net']) }}</td>
                            <td class="text-right">{{ $row['pph23'] > 0 ? $rp($row['pph23']) : '—' }}</td>
                            <td class="text-right font-weight-600">{{ $rp($row['actual']) }}</td>
                            <td>
                                @if ($row['is_paid'])
                                    <span class="text-success font-weight-600"><i class="fas fa-check-circle"></i> Paid</span>
                                    <div class="text-muted" style="font-size:11px;">{{ $row['paid_date']->format('d M Y') }}</div>
                                @else
                                    <span class="text-danger font-weight-600">Unpaid</span>
                                    <div class="text-muted" style="font-size:11px;">
                                        {{ $row['invoice_date']->diffInDays(now()) }} days
                                        &middot; {{ $row['sent_date'] ? 'sent ' . $row['sent_date']->format('d M') : 'not sent' }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">{{ $empty }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
