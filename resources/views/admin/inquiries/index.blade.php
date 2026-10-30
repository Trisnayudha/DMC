@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Inquiry Database</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Inquiry Database</div>
                </div>
            </div>

            <div class="section-body">
                @unless ($ready)
                    <div class="alert alert-warning">Tabel <code>contact_inquiry</code> belum dibuat. Jalankan migration-nya dulu.</div>
                @endunless
                @include('admin.inquiries.partials._stats')
                @include('admin.inquiries.partials._table')
            </div>{{-- /section-body --}}
        </section>
    </div>
@endsection

@include('admin.inquiries.partials._scripts')
