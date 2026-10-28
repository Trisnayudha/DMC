@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Buat Invoice VA Mandiri</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.finance_invoices.index') }}">Invoice VA Mandiri</a></div>
                    <div class="breadcrumb-item active">Buat</div>
                </div>
            </div>

            <div class="section-body">
                @include('admin.finance_invoices.partials._alerts')

                <form method="POST" action="{{ route('admin.finance_invoices.store') }}" id="finance-invoice-form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8">
                            @include('admin.finance_invoices.partials._form-source')
                            @include('admin.finance_invoices.partials._form-bill-to')
                            @include('admin.finance_invoices.partials._form-items')
                        </div>
                        <div class="col-lg-4">
                            @include('admin.finance_invoices.partials._form-summary')
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@include('admin.finance_invoices.partials._form-scripts')
