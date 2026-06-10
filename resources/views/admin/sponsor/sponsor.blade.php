@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsors Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Sponsors Management</a></div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Sponsors</h2>

                {{-- Quick navigation shortcuts --}}
                <div class="d-flex flex-wrap mb-3" style="gap:8px">
                    <a href="{{ route('sponsors.contact-directory') }}"
                       class="btn btn-info">
                        <i class="fas fa-address-book mr-1"></i> Contact Directory
                    </a>
                    <a href="{{ route('sponsors.representative.index') }}"
                       class="btn btn-light border">
                        <i class="fas fa-calendar-check mr-1"></i> Representative Attendance
                    </a>
                    <a href="{{ route('sponsors.nearing-contract') }}"
                       class="btn btn-light border">
                        <i class="fas fa-hourglass-half mr-1 text-warning"></i> Nearing Contract
                    </a>
                    <a href="{{ route('sponsors.annual-report') }}"
                       class="btn btn-warning">
                        <i class="fas fa-chart-bar mr-1"></i> Annual Report
                    </a>
                </div>

                {{-- Expired & Renewal Soon alerts --}}
                @include('admin.sponsor.partials._alerts')

                {{-- Package count + benefit usage stat cards --}}
                @include('admin.sponsor.partials._stats')

                {{-- Top 5 Attend + Engagement Count tables --}}
                @include('admin.sponsor.partials._summary_tables')

                {{-- Nearing Contract End card --}}
                <div class="row">
                    @include('admin.sponsor.partials._nearing_contract')
                </div>

                {{-- Recent Sponsor Inquiries --}}
                @include('admin.sponsor.partials._inquiries')

                {{-- Sponsors Management table with filters --}}
                @include('admin.sponsor.partials._table')

            </div>
        </section>
    </div>
@endsection

{{-- Modals --}}
@include('admin.sponsor.partials._modals')

{{-- All inline JS (wrapped in @push) --}}
@include('admin.sponsor.partials._scripts')
