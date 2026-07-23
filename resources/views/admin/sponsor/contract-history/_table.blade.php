            {{-- ═══ HISTORY TABLE ═══ --}}
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:12.5px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th style="min-width:200px;">Sponsor</th>
                                    <th style="width:90px;">Package</th>
                                    <th style="min-width:170px;">Period</th>
                                    <th style="width:110px;">Status</th>
                                    <th style="width:110px;">Type</th>
                                    <th style="min-width:140px;">Quotation</th>
                                    <th style="min-width:140px;">Invoice</th>
                                    <th style="width:120px;">Paid</th>
                                    <th style="min-width:160px;">Notes</th>
                                    <th style="width:60px;">Edit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($renewals as $i => $r)
                                @php
                                    $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                    $pkgColor  = $pkgColors[$r->package] ?? '#6c757d';
                                    $typeMap = [
                                        'renewal'    => 'Renewal',
                                        'upgrade'    => 'Upgrade',
                                        'new'        => 'New',
                                        'new_member' => 'New Member',
                                    ];
                                    $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                                               '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
                                    $period = '—';
                                    if ($r->contract_start && $r->contract_end) {
                                        [$sy,$sm] = explode('-', $r->contract_start);
                                        [$ey,$em] = explode('-', $r->contract_end);
                                        $period = ($months[$sm] ?? $sm).' '.$sy.' – '.($months[$em] ?? $em).' '.$ey;
                                    }
                                    $turnaround = $r->payment_turnaround_days;
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $renewals->firstItem() + $i }}</td>
                                    <td>
                                        <div class="font-weight-700" style="color:#2d3748;">
                                            {{ $r->sponsor ? $r->sponsor->name : '—' }}
                                        </div>
                                        @if($r->sponsor && $r->sponsor->branding_name && $r->sponsor->branding_name !== $r->sponsor->name)
                                            <div style="font-size:11px;color:#888;">{{ $r->sponsor->branding_name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $pkgColor }};">
                                            {{ ucfirst($r->package ?? '—') }}
                                        </span>
                                    </td>
                                    <td style="color:#555;">{{ $period }}</td>
                                    <td>
                                        @if($r->renewal_status === 'renewed')
                                            <span class="badge" style="background:#47c363;color:#fff;">Renewed</span>
                                        @else
                                            <span class="badge" style="background:#fc544b;color:#fff;">Not Renewed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-size:11.5px;color:#555;">{{ $typeMap[$r->renewal_type] ?? '—' }}</span>
                                    </td>
                                    <td style="font-size:11.5px;">
                                        @if($r->quotation_number)
                                            <div style="font-family:monospace;">{{ $r->quotation_number }}</div>
                                            <div class="text-muted">{{ $r->quotation_date ? $r->quotation_date->format('d M Y') : '—' }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size:11.5px;">
                                        @if($r->invoice_number)
                                            <div style="font-family:monospace;">{{ $r->invoice_number }}</div>
                                            <div class="text-muted">{{ $r->invoice_date ? $r->invoice_date->format('d M Y') : '—' }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size:11.5px;">
                                        @if($r->paid_date)
                                            <span class="badge" style="background:#47c363;color:#fff;">Paid</span>
                                            <div class="text-muted">{{ $r->paid_date->format('d M Y') }}</div>
                                        @elseif($r->invoice_date)
                                            <span class="badge" style="background:#f39c12;color:#fff;">Unpaid</span>
                                            <div class="text-muted">{{ $turnaround }}d</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size:11.5px;color:#555;max-width:220px;">
                                        {{ $r->notes ? \Illuminate\Support\Str::limit($r->notes, 60) : '—' }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-renewal-btn"
                                            data-id="{{ $r->id }}"
                                            data-sponsor-name="{{ $r->sponsor ? $r->sponsor->name : '—' }}"
                                            data-status="{{ $r->renewal_status }}"
                                            data-contract-start="{{ $r->contract_start }}"
                                            data-contract-end="{{ $r->contract_end }}"
                                            data-package="{{ $r->package }}"
                                            data-renewal-type="{{ $r->renewal_type }}"
                                            data-amount-usd="{{ $r->amount_usd }}"
                                            data-amount-idr="{{ $r->amount_idr }}"
                                            data-quotation-number="{{ $r->quotation_number }}"
                                            data-quotation-date="{{ $r->quotation_date ? $r->quotation_date->format('Y-m-d') : '' }}"
                                            data-invoice-date="{{ $r->invoice_date ? $r->invoice_date->format('Y-m-d') : '' }}"
                                            data-invoice-number="{{ $r->invoice_number }}"
                                            data-paid-date="{{ $r->paid_date ? $r->paid_date->format('Y-m-d') : '' }}"
                                            data-notes="{{ $r->notes }}"
                                            data-toggle="tooltip" title="Edit record">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3 d-block" style="opacity:.3;"></i>
                                        No contract history found for the selected filters.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($renewals->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center py-2">
                        <small class="text-muted">
                            Showing {{ $renewals->firstItem() }}–{{ $renewals->lastItem() }} of {{ $renewals->total() }} records
                        </small>
                        {{ $renewals->links() }}
                    </div>
                @else
                    <div class="card-footer py-2">
                        <small class="text-muted">{{ $renewals->total() }} records</small>
                    </div>
                @endif
            </div>
