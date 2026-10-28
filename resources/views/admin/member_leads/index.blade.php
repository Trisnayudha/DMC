@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper lead-management">
        <section class="section">
            <div class="section-header">
                <h1>Lead Follow-Up</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('users') }}">Members Management</a></div>
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

                <div class="card lead-list-card">
                    <div class="card-header">
                        <div>
                            <h4>Leads Management</h4>
                            <p class="text-muted mb-0 small">Track potential sponsors and follow-up progress.</p>
                        </div>
                        <div class="lead-heading-actions">
                            <button type="button" class="bulk-btn bulk-btn-primary" id="bulk-btn-follow-up" disabled
                                data-toggle="modal" data-target="#bulkFollowUpModal">
                                <i class="fas fa-paper-plane"></i> Bulk Follow-up / Send Kit
                            </button>
                            <button type="button" class="bulk-btn btn-open-bulk-temperature" id="bulk-btn-temperature" disabled>
                                <i class="fas fa-thermometer-half"></i> Bulk Temperature
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('admin.member_leads.partials._filters')
                        @include('admin.member_leads.partials._table')
                    </div>
                </div>
                <details class="lead-guide">
                    <summary><i class="fas fa-info-circle mr-1"></i> Status &amp; scoring guide</summary>
                    @include('admin.member_leads.partials._legend')
                </details>

            </div>{{-- /section-body --}}
        </section>
    </div>

    @include('admin.member_leads.partials._modal_follow_up')
    @include('admin.member_leads.partials._modal_do_not_send')
    @include('admin.member_leads.partials._modal_add_lead')
    @include('admin.member_leads.partials._modal_notes')
    @include('admin.member_leads.partials._modal_channel')
    @include('admin.member_leads.partials._modal_temperature')
    @include('admin.member_leads.partials._modal_bulk')
@endsection

@include('admin.member_leads.partials._scripts')

@push('top')
    @include('admin.member_leads.partials._styles')
@endpush
