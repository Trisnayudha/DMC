@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Annual Report {{ $year }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors</a></div>
                <div class="breadcrumb-item active">Annual Report {{ $year }}</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Top action bar --}}
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                <div class="d-flex align-items-center" style="gap:8px;">
                    <a href="{{ route('sponsors.index') }}" class="btn btn-light border">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Sponsors
                    </a>
                    <h2 class="section-title mb-0">Sponsors Annual Report</h2>
                </div>
                <a href="{{ route('sponsors.downloadAnnualReport', ['year' => $year]) }}"
                   class="btn btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Download Excel {{ $year }}
                </a>
            </div>

            {{-- ═══ FILTER PANEL ═══ --}}
            <div class="card" style="border-top: 3px solid #6777ef;">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('sponsors.annual-report') }}" id="filterForm">
                        <div class="row align-items-end" style="gap-y: 8px;">
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Year</label>
                                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:90px;">
                                    @foreach($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Package</label>
                                <select name="package" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:120px;">
                                    <option value="">All Packages</option>
                                    <option value="platinum" {{ $package == 'platinum' ? 'selected' : '' }}>Platinum</option>
                                    <option value="gold"     {{ $package == 'gold'     ? 'selected' : '' }}>Gold</option>
                                    <option value="silver"   {{ $package == 'silver'   ? 'selected' : '' }}>Silver</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Type</label>
                                <select name="renewal_type" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:150px;">
                                    <option value="">All Types</option>
                                    <option value="renewal"     {{ $renewalType == 'renewal'     ? 'selected' : '' }}>Renewal</option>
                                    <option value="upgrade"     {{ $renewalType == 'upgrade'     ? 'selected' : '' }}>Upgrade</option>
                                    <option value="new"         {{ $renewalType == 'new'         ? 'selected' : '' }}>New Sponsor</option>
                                    <option value="new_member"  {{ $renewalType == 'new_member'  ? 'selected' : '' }}>New Member</option>
                                    <option value="not_renewed" {{ $renewalType == 'not_renewed' ? 'selected' : '' }}>Not Renewed</option>
                                </select>
                            </div>
                            <div class="col-auto flex-grow-1">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Search Company</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Search sponsor name…"
                                           value="{{ $search }}" style="min-width:200px;">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            @if($package || $renewalType || $search)
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm d-block">&nbsp;</label>
                                <a href="{{ route('sponsors.annual-report', ['year' => $year]) }}"
                                   class="btn btn-sm btn-light border text-danger">
                                    <i class="fas fa-times mr-1"></i> Reset
                                </a>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══ SUMMARY CARDS ═══ --}}
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1" style="border-bottom: 3px solid #3abaf4;">
                        <div class="card-icon bg-primary"><i class="fas fa-redo-alt"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Renewal</h4></div>
                            <div class="card-body">{{ $renewedCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1" style="border-bottom: 3px solid #f39c12;">
                        <div class="card-icon bg-warning"><i class="fas fa-arrow-up"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Upgrade</h4></div>
                            <div class="card-body">{{ $upgradeCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1" style="border-bottom: 3px solid #47c363;">
                        <div class="card-icon bg-success"><i class="fas fa-star"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>New Sponsor</h4></div>
                            <div class="card-body">{{ $newCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-statistic-1" style="border-bottom: 3px solid #fc544b;">
                        <div class="card-icon bg-danger"><i class="fas fa-times-circle"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Not Renewed</h4></div>
                            <div class="card-body">{{ $notRenewedCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

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

            {{-- ═══ CONTRACT EXPIRY FORECAST ═══ --}}
            @php
                $monthNames = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                               7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                $monthShort = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
                               7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
                $totalExpiring = $expiryForecast->sum(fn($g) => $g->count());
                $maxExpiry = $expiryForecast->isNotEmpty() ? $expiryForecast->map(fn($g) => $g->count())->max() : 1;
            @endphp

            <div class="card" style="border-top: 3px solid #f39c12;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-times mr-2" style="color:#f39c12;"></i>
                            Contract Expiry Forecast — {{ $year }}
                        </h4>
                        <small class="text-muted">Contracts that expire during {{ $year }} — these sponsors will need follow-up for renewal</small>
                    </div>
                    <div class="text-right">
                        @if($peakExpiryMonth)
                            <span style="font-size:12px;color:#888;">Peak month:</span>
                            <span class="ml-1 font-weight-700" style="color:#f39c12;font-size:14px;">
                                {{ $monthNames[$peakExpiryMonth] }}
                                <span style="font-size:12px;color:#888;">({{ $expiryForecast->get($peakExpiryMonth)->count() }} contracts)</span>
                            </span>
                        @endif
                        <div style="font-size:12px;color:#888;margin-top:2px;">
                            Total expiring: <strong>{{ $totalExpiring }}</strong>
                        </div>
                    </div>
                </div>

                @if($expiryForecast->isNotEmpty())
                <div class="card-body pb-2">
                    {{-- Month heatmap strip — click any month to see sponsors --}}
                    <div class="row flex-nowrap mb-0" style="margin:0 -3px;">
                        @for($m = 1; $m <= 12; $m++)
                        @php
                            $mCount = $expiryForecast->has($m) ? $expiryForecast->get($m)->count() : 0;
                            $isPeak = ($m == $peakExpiryMonth);
                            $intensity = $mCount > 0 ? min(100, round($mCount / $maxExpiry * 100)) : 0;
                            if ($mCount == 0) {
                                $bg = '#f8f9fa'; $textColor = '#bbb'; $border = '#e9ecef';
                            } elseif ($isPeak) {
                                $bg = '#f39c12'; $textColor = '#fff'; $border = '#e67e22';
                            } elseif ($intensity >= 70) {
                                $bg = '#fde3aa'; $textColor = '#8a4a00'; $border = '#f39c12';
                            } elseif ($intensity >= 40) {
                                $bg = '#fef3dc'; $textColor = '#8a4a00'; $border = '#fac56a';
                            } else {
                                $bg = '#fffbf0'; $textColor = '#a07030'; $border = '#fde3aa';
                            }
                        @endphp
                        <div style="flex:1;padding:0 3px 8px;">
                            <div id="monthBox{{ $m }}"
                                 onclick="selectMonth({{ $m }})"
                                 onmouseenter="this.style.transform='translateY(-2px)'"
                                 onmouseleave="this.style.transform=''"
                                 style="background:{{ $bg }};border:2px solid {{ $border }};border-radius:8px;text-align:center;padding:10px 4px;cursor:pointer;transition:transform .15s,box-shadow .15s;">
                                <div style="font-size:10px;font-weight:600;color:{{ $textColor }};letter-spacing:.3px;text-transform:uppercase;">{{ $monthShort[$m] }}</div>
                                <div style="font-size:20px;font-weight:800;color:{{ $textColor }};line-height:1.2;margin:2px 0;">{{ $mCount > 0 ? $mCount : '0' }}</div>
                                @if($isPeak)
                                    <div style="font-size:9px;color:#fff;background:#e67e22;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;font-weight:700;">PEAK</div>
                                @else
                                    <div style="font-size:9px;color:{{ $textColor }};opacity:.6;">contract{{ $mCount != 1 ? 's' : '' }}</div>
                                @endif
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Detail area: shows selected month's sponsors --}}
                <div id="expiryDetailArea" style="display:none; border-top:2px solid #f39c12;">

                {{-- Pre-render all 12 months (hidden), JS swaps them in --}}
                @for($m = 1; $m <= 12; $m++)
                @php $mSponsorsForSlot = $expiryForecast->has($m) ? $expiryForecast->get($m) : collect(); @endphp
                <div id="expiryMonth{{ $m }}" style="display:none;">
                    {{-- Month header --}}
                    <div style="background:#fffbf0;padding:10px 20px 8px;display:flex;align-items:center;gap:12px;">
                        <span style="font-weight:700;font-size:14px;color:#8a4a00;">
                            <i class="fas fa-calendar-alt mr-1" style="color:#f39c12;"></i>
                            {{ $monthNames[$m] }} {{ $year }}
                        </span>
                        @if($mSponsorsForSlot->isNotEmpty())
                            <span style="font-size:12px;color:#aaa;">{{ $mSponsorsForSlot->count() }} contract{{ $mSponsorsForSlot->count() != 1 ? 's' : '' }} expiring</span>
                        @endif
                    </div>
                    {{-- Sponsors table or empty state --}}
                    @if($mSponsorsForSlot->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:13px;">
                            <thead>
                                <tr style="background:#fff8ee;">
                                    <th style="width:36px;padding:8px 16px;">#</th>
                                    <th style="min-width:200px;">Company</th>
                                    <th style="width:90px;">Package</th>
                                    <th style="width:140px;">Contract End</th>
                                    <th style="width:110px;">Renewal Type</th>
                                    <th>PIC Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mSponsorsForSlot as $ei => $er)
                                @php
                                    $ePic = $er->sponsor ? $er->sponsor->firstPic : null;
                                    $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                    $ePkgColor = $pkgColors[$er->package] ?? '#6c757d';
                                    $eTypeMap  = [
                                        'renewal'    => ['label'=>'Renewal',    'bg'=>'#3abaf4'],
                                        'upgrade'    => ['label'=>'Upgrade',    'bg'=>'#f39c12'],
                                        'new'        => ['label'=>'New',        'bg'=>'#47c363'],
                                        'new_member' => ['label'=>'New Member', 'bg'=>'#47c363'],
                                    ];
                                    $eType = $eTypeMap[$er->renewal_type] ?? ['label'=>ucfirst($er->renewal_type ?? '—'), 'bg'=>'#6c757d'];
                                    $endDate  = $er->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $er->contract_end)->endOfMonth() : null;
                                    $daysLeft = $endDate ? (int) now()->diffInDays($endDate, false) : null;
                                @endphp
                                <tr>
                                    <td style="padding:10px 16px;color:#aaa;">{{ $ei+1 }}</td>
                                    <td style="padding:10px 16px;">
                                        <div class="font-weight-700" style="color:#2d3748;">{{ $er->sponsor ? $er->sponsor->name : '—' }}</div>
                                        @if($er->sponsor && $er->sponsor->branding_name && $er->sponsor->branding_name !== $er->sponsor->name)
                                            <div style="font-size:11px;color:#888;">{{ $er->sponsor->branding_name }}</div>
                                        @endif
                                    </td>
                                    <td style="padding:10px 16px;">
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $ePkgColor }};">
                                            {{ ucfirst($er->package ?? '—') }}
                                        </span>
                                    </td>
                                    <td style="padding:10px 16px;">
                                        <div style="font-size:12px;font-weight:600;color:#555;">
                                            {{ $er->contract_end ? \Carbon\Carbon::createFromFormat('Y-m', $er->contract_end)->format('M Y') : '—' }}
                                        </div>
                                        @if($daysLeft !== null)
                                            @if($daysLeft < 0)
                                                <div style="font-size:10px;color:#fc544b;font-weight:600;"><i class="fas fa-exclamation-circle"></i> Expired {{ abs($daysLeft) }}d ago</div>
                                            @elseif($daysLeft <= 30)
                                                <div style="font-size:10px;color:#fc544b;font-weight:600;"><i class="fas fa-fire"></i> {{ $daysLeft }}d left</div>
                                            @elseif($daysLeft <= 90)
                                                <div style="font-size:10px;color:#f39c12;font-weight:600;"><i class="fas fa-clock"></i> {{ $daysLeft }}d left</div>
                                            @else
                                                <div style="font-size:10px;color:#47c363;">{{ $daysLeft }}d left</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td style="padding:10px 16px;">
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;color:#fff;background:{{ $eType['bg'] }};">
                                            {{ $eType['label'] }}
                                        </span>
                                    </td>
                                    <td style="padding:10px 16px;">
                                        @if($ePic)
                                            <div class="d-flex align-items-center" style="gap:8px;">
                                                <div style="width:28px;height:28px;border-radius:50%;background:#f39c12;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                    {{ strtoupper(substr($ePic->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div style="font-size:12px;font-weight:600;color:#333;">{{ $ePic->name }}</div>
                                                    @if($ePic->email)
                                                        <a href="mailto:{{ $ePic->email }}" style="font-size:10px;color:#6777ef;display:block;">
                                                            <i class="fas fa-envelope" style="width:12px;"></i> {{ Str::limit($ePic->email, 28) }}
                                                        </a>
                                                    @endif
                                                    @if($ePic->phone)
                                                        <a href="tel:{{ $ePic->phone }}" style="font-size:10px;color:#47c363;display:block;">
                                                            <i class="fas fa-phone" style="width:12px;"></i> {{ $ePic->phone }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span style="font-size:11px;color:#bbb;"><i class="fas fa-user-slash mr-1"></i>No PIC</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-check fa-lg mb-2 d-block" style="opacity:.3;"></i>
                        <span style="font-size:13px;">No contracts expiring in {{ $monthNames[$m] }} {{ $year }}</span>
                    </div>
                    @endif
                </div>
                @endfor
                </div>{{-- end expiryDetailArea --}}

                @else
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-calendar-check fa-2x mb-3 d-block" style="opacity:.3;"></i>
                    No contracts expiring in {{ $year }}.
                </div>
                @endif
            </div>

            {{-- ═══ SPONSOR DATA TABLES (Tabs) ═══ --}}
            <div class="card" style="border-top: 3px solid #6777ef;">
                <div class="card-header p-0" style="border-bottom: none;">
                    <ul class="nav nav-tabs card-header-tabs ml-0" id="reportTabs" role="tablist" style="border-bottom: 1px solid #e4e6fc; padding: 0 20px;">
                        <li class="nav-item">
                            <a class="nav-link {{ ($renewalType !== 'not_renewed') ? 'active' : '' }} font-weight-600"
                               id="renewed-tab" data-toggle="tab" href="#renewedPane" role="tab" style="padding: 14px 20px;">
                                <i class="fas fa-check-circle mr-1" style="color:#47c363;"></i>
                                Renewed &amp; New
                                <span class="badge ml-1" style="background:#47c363;color:#fff;border-radius:10px;padding:2px 7px;font-size:11px;">
                                    {{ $renewedSponsors->count() }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($renewalType === 'not_renewed') ? 'active' : '' }} font-weight-600"
                               id="notrenewed-tab" data-toggle="tab" href="#notRenewedPane" role="tab" style="padding: 14px 20px;">
                                <i class="fas fa-times-circle mr-1" style="color:#fc544b;"></i>
                                Not Renewed
                                <span class="badge ml-1" style="background:#fc544b;color:#fff;border-radius:10px;padding:2px 7px;font-size:11px;">
                                    {{ $notRenewedSponsors->count() }}
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="reportTabsContent">

                    {{-- ── RENEWED TAB ── --}}
                    <div class="tab-pane fade {{ ($renewalType !== 'not_renewed') ? 'show active' : '' }}"
                         id="renewedPane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:13px;">
                                <thead>
                                    <tr style="background:#f0fff4; border-bottom: 2px solid #b2dfdb;">
                                        <th style="width:40px; padding:12px 16px;">#</th>
                                        <th style="min-width:220px;">Company</th>
                                        <th style="width:90px;">Package</th>
                                        <th style="min-width:190px;">Period</th>
                                        <th style="width:110px;">Type</th>
                                        <th style="min-width:200px;">PIC Contact</th>
                                        <th>Confirmation / Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($renewedSponsors as $i => $r)
                                    @php
                                        $pic = $r->sponsor ? $r->sponsor->firstPic : null;
                                        $typeMap = [
                                            'renewal'    => ['label' => 'Renewal',    'bg' => '#3abaf4', 'icon' => 'fas fa-redo-alt'],
                                            'upgrade'    => ['label' => 'Upgrade',    'bg' => '#f39c12', 'icon' => 'fas fa-arrow-up'],
                                            'new'        => ['label' => 'New',        'bg' => '#47c363', 'icon' => 'fas fa-star'],
                                            'new_member' => ['label' => 'New Member', 'bg' => '#47c363', 'icon' => 'fas fa-user-plus'],
                                        ];
                                        $typeInfo = $typeMap[$r->renewal_type] ?? ['label' => ucfirst($r->renewal_type ?? '—'), 'bg' => '#6c757d', 'icon' => 'fas fa-tag'];
                                        $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                        $pkgColor  = $pkgColors[$r->package] ?? '#6c757d';

                                        // Format period
                                        $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                                                   '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
                                        $period = '—';
                                        if ($r->contract_start && $r->contract_end) {
                                            [$sy,$sm] = explode('-', $r->contract_start);
                                            [$ey,$em] = explode('-', $r->contract_end);
                                            $period = ($months[$sm] ?? $sm).' '.$sy.' – '.($months[$em] ?? $em).' '.$ey;
                                        }

                                        // Format confirmation
                                        if ($r->notes) {
                                            $confirmation = $r->notes;
                                        } else {
                                            $pkgMap = ['platinum'=>'Major','gold'=>'Gold','silver'=>'Silver'];
                                            $pkgLabel = $pkgMap[$r->package] ?? ucfirst($r->package ?? '');
                                            $confirmation = 'Confirmed – '.$pkgLabel.' Sponsorship';
                                            if ($r->amount_usd) $confirmation .= ' / USD '.number_format($r->amount_usd,0,'.',',');
                                            if ($r->amount_idr) $confirmation .= ' / IDR '.number_format($r->amount_idr,0,'.',',');
                                        }
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 16px; color:#888;">{{ $i+1 }}</td>
                                        <td style="padding:12px 16px;">
                                            <div class="font-weight-700" style="color:#2d3748;">
                                                {{ $r->sponsor ? $r->sponsor->name : '—' }}
                                            </div>
                                            @if($r->sponsor && $r->sponsor->branding_name && $r->sponsor->branding_name !== $r->sponsor->name)
                                                <div style="font-size:11px;color:#888;">{{ $r->sponsor->branding_name }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $pkgColor }};">
                                                {{ ucfirst($r->package ?? '—') }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px; color:#555; font-size:12px;">{{ $period }}</td>
                                        <td style="padding:12px 16px;">
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;color:#fff;background:{{ $typeInfo['bg'] }};">
                                                <i class="{{ $typeInfo['icon'] }}" style="font-size:10px;"></i>
                                                {{ $typeInfo['label'] }}
                                            </span>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @if($pic)
                                                <div class="d-flex align-items-center" style="gap:8px;">
                                                    <div style="width:30px;height:30px;border-radius:50%;background:#6777ef;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                        {{ strtoupper(substr($pic->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div style="font-size:12px;font-weight:600;color:#333;">{{ $pic->name }}</div>
                                                        @if($pic->title)<div style="font-size:10px;color:#999;">{{ $pic->title }}</div>@endif
                                                        @if($pic->email)
                                                            <a href="mailto:{{ $pic->email }}" style="font-size:10px;color:#6777ef;display:block;">
                                                                <i class="fas fa-envelope" style="width:12px;"></i> {{ Str::limit($pic->email, 26) }}
                                                            </a>
                                                        @endif
                                                        @if($pic->phone)
                                                            <a href="tel:{{ $pic->phone }}" style="font-size:10px;color:#47c363;display:block;">
                                                                <i class="fas fa-phone" style="width:12px;"></i> {{ $pic->phone }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span style="font-size:11px;color:#bbb;"><i class="fas fa-user-slash mr-1"></i>No PIC</span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px; font-size:12px; color:#555; max-width:260px;">
                                            {{ $confirmation }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 d-block" style="opacity:.3;"></i>
                                            No renewed or new sponsors found for the selected filters.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ── NOT RENEWED TAB ── --}}
                    <div class="tab-pane fade {{ ($renewalType === 'not_renewed') ? 'show active' : '' }}"
                         id="notRenewedPane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:13px;">
                                <thead>
                                    <tr style="background:#fff5f5; border-bottom: 2px solid #ffcccc;">
                                        <th style="width:40px; padding:12px 16px;">#</th>
                                        <th style="min-width:220px;">Company</th>
                                        <th style="width:90px;">Last Package</th>
                                        <th style="min-width:190px;">Last Period</th>
                                        <th style="min-width:200px;">PIC Contact</th>
                                        <th>Reason / Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notRenewedSponsors as $i => $n)
                                    @php
                                        $pic = $n->sponsor ? $n->sponsor->firstPic : null;
                                        $pkgColors = ['platinum'=>'#6777ef','gold'=>'#f39c12','silver'=>'#6c757d'];
                                        $pkgColor  = $pkgColors[$n->package] ?? '#6c757d';

                                        $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                                                   '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
                                        $period = '—';
                                        if ($n->contract_start && $n->contract_end) {
                                            [$sy,$sm] = explode('-', $n->contract_start);
                                            [$ey,$em] = explode('-', $n->contract_end);
                                            $period = ($months[$sm] ?? $sm).' '.$sy.' – '.($months[$em] ?? $em).' '.$ey;
                                        }
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 16px; color:#888;">{{ $i+1 }}</td>
                                        <td style="padding:12px 16px;">
                                            <div class="font-weight-700" style="color:#2d3748;">
                                                {{ $n->sponsor ? $n->sponsor->name : '—' }}
                                            </div>
                                            @if($n->sponsor && $n->sponsor->branding_name && $n->sponsor->branding_name !== $n->sponsor->name)
                                                <div style="font-size:11px;color:#888;">{{ $n->sponsor->branding_name }}</div>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px;">
                                            @if($n->package)
                                            <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:{{ $pkgColor }};">
                                                {{ ucfirst($n->package) }}
                                            </span>
                                            @else
                                            <span style="font-size:11px;color:#bbb;">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px; color:#555; font-size:12px;">{{ $period }}</td>
                                        <td style="padding:12px 16px;">
                                            @if($pic)
                                                <div class="d-flex align-items-center" style="gap:8px;">
                                                    <div style="width:30px;height:30px;border-radius:50%;background:#fc544b;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                        {{ strtoupper(substr($pic->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div style="font-size:12px;font-weight:600;color:#333;">{{ $pic->name }}</div>
                                                        @if($pic->title)<div style="font-size:10px;color:#999;">{{ $pic->title }}</div>@endif
                                                        @if($pic->email)
                                                            <a href="mailto:{{ $pic->email }}" style="font-size:10px;color:#6777ef;display:block;">
                                                                <i class="fas fa-envelope" style="width:12px;"></i> {{ Str::limit($pic->email, 26) }}
                                                            </a>
                                                        @endif
                                                        @if($pic->phone)
                                                            <a href="tel:{{ $pic->phone }}" style="font-size:10px;color:#47c363;display:block;">
                                                                <i class="fas fa-phone" style="width:12px;"></i> {{ $pic->phone }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span style="font-size:11px;color:#bbb;"><i class="fas fa-user-slash mr-1"></i>No PIC</span>
                                            @endif
                                        </td>
                                        <td style="padding:12px 16px; font-size:12px; color:#555; max-width:280px;">
                                            @if($n->notes)
                                                {{ $n->notes }}
                                            @else
                                                <span class="text-muted" style="font-style:italic;">No notes recorded</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 d-block" style="opacity:.3;"></i>
                                            No sponsors marked as not renewed for {{ $year }}.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>{{-- end tab-content --}}
            </div>{{-- end card --}}

        </div>{{-- end section-body --}}
    </section>
</div>
@endsection

@push('bottom')
<script>
    var activeMonth = null;

    function selectMonth(monthNum) {
        var detailArea = document.getElementById('expiryDetailArea');

        // Hide current active month panel
        if (activeMonth !== null) {
            var prev = document.getElementById('expiryMonth' + activeMonth);
            if (prev) prev.style.display = 'none';
            // Remove active style from previous box
            resetBoxStyle(activeMonth);
        }

        // If clicking the same month again — close it
        if (activeMonth === monthNum) {
            detailArea.style.display = 'none';
            activeMonth = null;
            return;
        }

        // Show new month panel
        var el = document.getElementById('expiryMonth' + monthNum);
        if (el) {
            detailArea.style.display = 'block';
            el.style.display = 'block';
            activeMonth = monthNum;
            // Mark box as active
            setBoxActive(monthNum);
            // Smooth scroll to detail area
            setTimeout(function() {
                detailArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        }
    }

    function setBoxActive(monthNum) {
        var box = document.getElementById('monthBox' + monthNum);
        if (box) {
            box.style.boxShadow = '0 0 0 3px #f39c12, 0 4px 12px rgba(243,156,18,.35)';
            box.style.transform = 'translateY(-3px)';
        }
    }

    function resetBoxStyle(monthNum) {
        var box = document.getElementById('monthBox' + monthNum);
        if (box) {
            box.style.boxShadow = '';
            box.style.transform = '';
        }
    }

    // Auto-open peak month on load
    @if($peakExpiryMonth)
    document.addEventListener('DOMContentLoaded', function() {
        selectMonth({{ $peakExpiryMonth }});
    });
    @endif
</script>
@endpush


