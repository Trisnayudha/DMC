@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Lucky Draw — Entries</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.lucky_draw.items.index') }}">Lucky Draw</a></div>
                    <div class="breadcrumb-item active">Entries</div>
                </div>
            </div>

            <div class="section-body">

                @include('admin.lucky_draw.entries.partials._table')

            </div>{{-- /section-body --}}
        </section>
    </div>
@endsection

@include('admin.lucky_draw.entries.partials._scripts')
