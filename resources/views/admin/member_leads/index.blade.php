@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Lead Follow-Up</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ Route('users') }}">Members Management</a></div>
                    <div class="breadcrumb-item active">Lead Follow-Up</div>
                </div>
            </div>

            <div class="section-body">

                {{-- Flash alerts --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>×</span></button>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <div id="alert-area" class="mb-2"></div>

                @include('admin.member_leads.partials._stats')

                @include('admin.member_leads.partials._table')

            </div>{{-- /section-body --}}
        </section>
    </div>

    @include('admin.member_leads.partials._modal_follow_up')
@endsection

@include('admin.member_leads.partials._scripts')
