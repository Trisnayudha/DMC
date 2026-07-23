@extends('layouts.inspire.master')

{{--
    Contract History: daftar semua sponsor_renewals lintas sponsor, dengan
    kemampuan edit untuk membetulkan data setelah renew/decline tercatat
    (mis. isi Paid Date belakangan, atau perbaiki input yang salah).
    Data disiapkan oleh SponsorContractHistoryController.
--}}

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Contract History</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors</a></div>
                <div class="breadcrumb-item active">Contract History</div>
            </div>
        </div>

        <div class="section-body">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
                <div class="d-flex align-items-center" style="gap:8px;">
                    <a href="{{ route('sponsors.index') }}" class="btn btn-light border">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Sponsors
                    </a>
                    <h2 class="section-title mb-0">Contract History</h2>
                </div>
                <a href="{{ route('sponsors.annual-report') }}" class="btn btn-warning">
                    <i class="fas fa-chart-bar mr-1"></i> Annual Report
                </a>
            </div>

            @include('admin.sponsor.contract-history._filters')

            @include('admin.sponsor.contract-history._table')

        </div>
    </section>
</div>
@endsection

@include('admin.sponsor.contract-history._edit-modal')
@include('admin.sponsor.partials._contract_history_scripts')
