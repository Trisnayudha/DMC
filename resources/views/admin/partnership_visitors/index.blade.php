@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Event Partnership Visitors</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Event Partnership Visitors</div>
                </div>
            </div>

            <div class="section-body">
                @include('admin.partnership_visitors.partials._stats')
                @include('admin.partnership_visitors.partials._table')
            </div>{{-- /section-body --}}
        </section>
    </div>
@endsection

@include('admin.partnership_visitors.partials._scripts')
