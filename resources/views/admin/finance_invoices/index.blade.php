@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Invoice VA Mandiri</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Invoice VA Mandiri</div>
                </div>
            </div>

            <div class="section-body">
                @include('admin.finance_invoices.partials._alerts')

                {{-- Ringkasan --}}
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Menunggu Pembayaran</h4></div>
                                <div class="card-body">{{ $summary['pending_count'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Nilai Belum Dibayar</h4></div>
                                <div class="card-body" style="font-size:16px;">Rp {{ number_format($summary['pending_amount'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Lunas Bulan Ini</h4></div>
                                <div class="card-body">{{ $summary['paid_month'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-coins"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Diterima Bulan Ini</h4></div>
                                <div class="card-body" style="font-size:16px;">Rp {{ number_format($summary['paid_month_amount'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                        <h4 class="mb-0">Daftar Invoice</h4>
                        <a href="{{ route('admin.finance_invoices.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>Buat Invoice
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.finance_invoices.index') }}" class="form-row mb-3">
                            <div class="col-md-5 mb-2">
                                <input type="search" name="search" class="form-control form-control-sm" value="{{ $filters['search'] }}"
                                    placeholder="Cari no. invoice, nama, perusahaan, email, no. VA...">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">Semua status</option>
                                    @foreach (\App\Models\Payments\FinanceInvoice::STATUSES as $key => $label)
                                        <option value="{{ $key }}" {{ $filters['status'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="type" class="form-control form-control-sm">
                                    <option value="">Semua tipe</option>
                                    @foreach (\App\Models\Payments\FinanceInvoice::TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ $filters['type'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-sm btn-primary">Filter</button>
                                <a href="{{ route('admin.finance_invoices.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 finance-invoice-table">
                                <thead>
                                    <tr>
                                        <th>No. Invoice</th>
                                        <th>Tipe</th>
                                        <th>Ditagihkan ke</th>
                                        <th class="text-right">Total</th>
                                        <th>VA Mandiri</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoices as $invoice)
                                        <tr>
                                            <td class="text-nowrap"><strong>{{ $invoice->invoice_number }}</strong>
                                                <div class="small text-muted">{{ $invoice->issued_date->format('d M Y') }}</div>
                                            </td>
                                            <td>{{ $invoice->typeLabel() }}</td>
                                            <td>
                                                {{ $invoice->bill_to_company ?: $invoice->bill_to_name }}
                                                <div class="small text-muted">{{ $invoice->bill_to_email }}</div>
                                            </td>
                                            <td class="text-right text-nowrap">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                            <td class="text-nowrap"><code>{{ $invoice->va_number ?: '—' }}</code></td>
                                            <td class="text-nowrap">{{ $invoice->due_date->format('d M Y') }}</td>
                                            <td>@include('admin.finance_invoices.partials._status-badge', ['invoice' => $invoice])</td>
                                            <td class="text-right">
                                                <a href="{{ route('admin.finance_invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Belum ada invoice.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                        <small class="text-muted">{{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} dari {{ $invoices->total() }} invoice</small>
                        {{ $invoices->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .finance-invoice-table th { background: #f8f9fc; font-size: 11px; color: #67728a; white-space: nowrap; }
        .finance-invoice-table td { font-size: 13px; vertical-align: middle; }
        .card-footer .pagination { margin: 0; }
    </style>
@endsection
