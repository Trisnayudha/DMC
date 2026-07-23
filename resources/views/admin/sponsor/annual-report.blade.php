@extends('layouts.inspire.master')

{{--
    Sponsors Annual Report.
    Each section is split into partials under resources/views/admin/sponsor/annual-report/
    for easier maintenance. Data is prepared by SponsorAnnualReportController.
--}}

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

            {{-- Filter: year / package / type / search --}}
            @include('admin.sponsor.annual-report._filters')

            {{-- Headcount per company: number of sponsors vs. last year (achieved / not) --}}
            @include('admin.sponsor.annual-report._headcount')

            {{-- Summary cards: renewal / upgrade / new / not renewed / total, with package breakdown --}}
            @include('admin.sponsor.annual-report._summary-cards')

            {{-- Monthly activity statistics --}}
            @include('admin.sponsor.annual-report._monthly-stats')

            {{-- Contract expiry forecast: monthly heatmap + per-month detail + follow-up status --}}
            @include('admin.sponsor.annual-report._expiry-forecast')

            {{-- ═══ SPONSOR DATA TABLES (Tabs) ═══ --}}
            <div class="card" style="border-top: 3px solid #6777ef;">
                <div class="card-header p-0" style="border-bottom: none;">
                    <ul class="nav nav-tabs card-header-tabs ml-0" id="reportTabs" role="tablist" style="border-bottom: 1px solid #e4e6fc; padding: 0 20px;">
                        <li class="nav-item">
                            <a class="nav-link {{ ($renewalType !== 'not_renewed') ? 'active' : '' }} font-weight-600"
                               id="renewed-tab" data-toggle="tab" href="#renewedPane" role="tab" style="padding: 14px 20px;">
                                <i class="fas fa-check-circle mr-1" style="color:#47c363;"></i>
                                Confirmed Sponsors
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
                        <li class="nav-item">
                            <a class="nav-link font-weight-600"
                               id="pending-tab" data-toggle="tab" href="#pendingPane" role="tab" style="padding: 14px 20px;">
                                <i class="fas fa-hourglass-half mr-1" style="color:#f39c12;"></i>
                                Pending Renewal
                                <span class="badge ml-1" style="background:#f39c12;color:#fff;border-radius:10px;padding:2px 7px;font-size:11px;">
                                    {{ $pendingRenewals->count() }}
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="reportTabsContent">
                    @include('admin.sponsor.annual-report._tab-renewed')
                    @include('admin.sponsor.annual-report._tab-not-renewed')
                    @include('admin.sponsor.annual-report._tab-pending')
                </div>
            </div>

        </div>{{-- end section-body --}}
    </section>
</div>
@endsection

{{-- Modal: Update Contract / Not Renewed / Follow-up + JS handlers (used by Pending Renewal tab) --}}
@include('admin.sponsor.partials._modals')
@include('admin.sponsor.partials._contract_scripts')

{{-- Modal: Edit Contract Record (same one used by the Contract History page) + JS handlers --}}
@include('admin.sponsor.contract-history._edit-modal')
@include('admin.sponsor.partials._contract_history_scripts')
