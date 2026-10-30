@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsor Log</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors</a></div>
                    <div class="breadcrumb-item active">Sponsor Log</div>
                </div>
            </div>

            <div class="section-body">
                @unless ($ready)
                    <div class="alert alert-warning">The <code>sponsor_logs</code> table has not been created yet. Please run the migration first.</div>
                @endunless
                @include('admin.sponsor.logs.partials._filters')
                @include('admin.sponsor.logs.partials._table')
            </div>{{-- /section-body --}}
        </section>
    </div>
@endsection
