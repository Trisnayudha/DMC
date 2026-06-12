            {{-- ═══ MONTHLY STATISTICS ═══ --}}
            @php
                $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                           7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                $hasMonthData = collect($monthlyStats)->sum(fn($s) => array_sum($s)) > 0;
                $maxMonthTotal = collect($monthlyStats)->map(fn($s) => array_sum($s))->max() ?: 1;
            @endphp

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Statistics by Month — {{ $year }}</h4>
                        <small class="text-muted">Distribution of sponsor activity per month</small>
                    </div>
                    @php $activeTotal = $renewedCount + $upgradeCount + $newCount + $notRenewedCount; @endphp
                    <div class="text-right">
                        <span class="text-muted" style="font-size:13px;">Total records: <strong>{{ $activeTotal }}</strong></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($hasMonthData)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:13px;">
                            <thead>
                                <tr style="background:#f8f9fa;">
                                    <th style="width:110px; padding: 10px 16px;">Month</th>
                                    <th class="text-center" style="min-width:160px;">
                                        <span style="color:#3abaf4;"><i class="fas fa-redo-alt mr-1"></i>Renewal</span>
                                    </th>
                                    <th class="text-center" style="min-width:160px;">
                                        <span style="color:#f39c12;"><i class="fas fa-arrow-up mr-1"></i>Upgrade</span>
                                    </th>
                                    <th class="text-center" style="min-width:160px;">
                                        <span style="color:#47c363;"><i class="fas fa-star mr-1"></i>New</span>
                                    </th>
                                    <th class="text-center" style="min-width:160px;">
                                        <span style="color:#fc544b;"><i class="fas fa-times-circle mr-1"></i>Not Renewed</span>
                                    </th>
                                    <th class="text-center" style="width:70px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($months as $num => $name)
                                    @php
                                        $s = $monthlyStats[$num];
                                        $total = array_sum($s);
                                    @endphp
                                    @if($total > 0)
                                    <tr>
                                        <td class="font-weight-600" style="padding: 10px 16px; color:#495057;">{{ $name }}</td>
                                        {{-- Renewal --}}
                                        <td class="text-center" style="padding:8px 12px;">
                                            @if($s['renewal'] > 0)
                                            <div class="d-flex align-items-center" style="gap:8px;">
                                                <div class="flex-grow-1">
                                                    <div style="height:8px;border-radius:4px;background:#e9f7fe;overflow:hidden;">
                                                        <div style="height:100%;border-radius:4px;background:#3abaf4;width:{{ round($s['renewal']/$maxMonthTotal*100) }}%;"></div>
                                                    </div>
                                                </div>
                                                <span class="font-weight-700" style="color:#3abaf4;min-width:20px;">{{ $s['renewal'] }}</span>
                                            </div>
                                            @else
                                            <span class="text-muted" style="font-size:12px;">—</span>
                                            @endif
                                        </td>
                                        {{-- Upgrade --}}
                                        <td class="text-center" style="padding:8px 12px;">
                                            @if($s['upgrade'] > 0)
                                            <div class="d-flex align-items-center" style="gap:8px;">
                                                <div class="flex-grow-1">
                                                    <div style="height:8px;border-radius:4px;background:#fff3cd;overflow:hidden;">
                                                        <div style="height:100%;border-radius:4px;background:#f39c12;width:{{ round($s['upgrade']/$maxMonthTotal*100) }}%;"></div>
                                                    </div>
                                                </div>
                                                <span class="font-weight-700" style="color:#f39c12;min-width:20px;">{{ $s['upgrade'] }}</span>
                                            </div>
                                            @else
                                            <span class="text-muted" style="font-size:12px;">—</span>
                                            @endif
                                        </td>
                                        {{-- New --}}
                                        <td class="text-center" style="padding:8px 12px;">
                                            @if($s['new'] > 0)
                                            <div class="d-flex align-items-center" style="gap:8px;">
                                                <div class="flex-grow-1">
                                                    <div style="height:8px;border-radius:4px;background:#d4edda;overflow:hidden;">
                                                        <div style="height:100%;border-radius:4px;background:#47c363;width:{{ round($s['new']/$maxMonthTotal*100) }}%;"></div>
                                                    </div>
                                                </div>
                                                <span class="font-weight-700" style="color:#47c363;min-width:20px;">{{ $s['new'] }}</span>
                                            </div>
                                            @else
                                            <span class="text-muted" style="font-size:12px;">—</span>
                                            @endif
                                        </td>
                                        {{-- Not Renewed --}}
                                        <td class="text-center" style="padding:8px 12px;">
                                            @if($s['not_renewed'] > 0)
                                            <div class="d-flex align-items-center" style="gap:8px;">
                                                <div class="flex-grow-1">
                                                    <div style="height:8px;border-radius:4px;background:#fde8e8;overflow:hidden;">
                                                        <div style="height:100%;border-radius:4px;background:#fc544b;width:{{ round($s['not_renewed']/$maxMonthTotal*100) }}%;"></div>
                                                    </div>
                                                </div>
                                                <span class="font-weight-700" style="color:#fc544b;min-width:20px;">{{ $s['not_renewed'] }}</span>
                                            </div>
                                            @else
                                            <span class="text-muted" style="font-size:12px;">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-weight-700" style="color:#495057;">{{ $total }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background:#f8f9fa; border-top: 2px solid #dee2e6;">
                                    <th style="padding: 10px 16px;">Total</th>
                                    <th class="text-center" style="color:#3abaf4;">{{ $renewedCount }}</th>
                                    <th class="text-center" style="color:#f39c12;">{{ $upgradeCount }}</th>
                                    <th class="text-center" style="color:#47c363;">{{ $newCount }}</th>
                                    <th class="text-center" style="color:#fc544b;">{{ $notRenewedCount }}</th>
                                    <th class="text-center">{{ $activeTotal }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-bar fa-2x mb-3 d-block" style="opacity:.3;"></i>
                        No data available for {{ $year }}
                    </div>
                    @endif
                </div>
            </div>
