@extends('layouts.inspire.master')

{{--
    Financial Reports (Finance) — invoice sponsor. Data dari FinanceReportController / FinanceReportService.
    Partials: _filters (periode), _summary (Total Amount / Actual / PPh 23), _table (Unpaid, Paid).
--}}

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Financial Reports</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Financial Reports</div>
                </div>
            </div>

            <div class="section-body">
                @include('admin.finance_reports._partials._filters')
                @include('admin.finance_reports._partials._month_strip')
                @include('admin.finance_reports._partials._charts')

                @php
                    $periodLabel = $filters['month']
                        ? \Carbon\Carbon::create($filters['year'], $filters['month'], 1)->format('F Y')
                        : 'Full year ' . $filters['year'];
                @endphp
                <h2 class="section-title mt-0 mb-3" style="font-size:15px;">Summary — {{ $periodLabel }}</h2>
                @include('admin.finance_reports._partials._summary')

                @include('admin.finance_reports._partials._table', [
                    'title'  => 'Invoice Unpaid',
                    'icon'   => 'fa-hourglass-half',
                    'color'  => '#fc544b',
                    'rows'   => $unpaid,
                    'totals' => $unpaid_totals,
                    'empty'  => 'No unpaid invoices in ' . $periodLabel . '.',
                ])

                @include('admin.finance_reports._partials._table', [
                    'title'  => 'Invoice Paid',
                    'icon'   => 'fa-check-circle',
                    'color'  => '#47c363',
                    'rows'   => $paid,
                    'totals' => $paid_totals,
                    'empty'  => 'No paid invoices in ' . $periodLabel . '.',
                ])

                <p class="text-muted" style="font-size:12px;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Sponsorship invoices only. Net Amount = Amount + VAT. Actual Amount = Amount − PPh 23 (PPh 23 as entered on the contract).
                    Year and month are based on the invoice date.
                </p>
            </div>
        </section>
    </div>
@endsection
