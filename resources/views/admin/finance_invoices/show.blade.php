@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Invoice {{ $invoice->invoice_number }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('admin.finance_invoices.index') }}">Invoice VA Mandiri</a></div>
                    <div class="breadcrumb-item active">{{ $invoice->invoice_number }}</div>
                </div>
            </div>

            <div class="section-body">
                @include('admin.finance_invoices.partials._alerts')

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                                <h4 class="mb-0">{{ $invoice->typeLabel() }}
                                    @include('admin.finance_invoices.partials._status-badge', ['invoice' => $invoice])
                                </h4>
                                <a href="{{ route('admin.finance_invoices.pdf', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-file-pdf mr-1"></i>PDF Invoice
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small text-uppercase">Ditagihkan ke</div>
                                        <strong>{{ $invoice->bill_to_company ?: $invoice->bill_to_name }}</strong><br>
                                        @if ($invoice->bill_to_company){{ $invoice->bill_to_name }}<br>@endif
                                        {{ $invoice->bill_to_email }}
                                        @if ($invoice->bill_to_cc)<br><small class="text-muted">CC: {{ $invoice->bill_to_cc }}</small>@endif
                                        @if ($invoice->bill_to_phone)<br>{{ $invoice->bill_to_phone }}@endif
                                        @if ($invoice->bill_to_address)<br><small>{!! nl2br(e($invoice->bill_to_address)) !!}</small>@endif
                                    </div>
                                    <div class="col-md-6 text-md-right">
                                        <div class="text-muted small text-uppercase">Tanggal / Jatuh Tempo</div>
                                        {{ $invoice->issued_date->format('d M Y') }} / {{ $invoice->due_date->format('d M Y') }}
                                        @if ($invoice->sponsor)
                                            <div class="mt-2 small">Sponsor: <strong>{{ $invoice->sponsor->name }}</strong></div>
                                        @endif
                                        @if ($invoice->sponsorRenewal)
                                            <div class="small">Kontrak: {{ $invoice->sponsorRenewal->contract_start }} s/d {{ $invoice->sponsorRenewal->contract_end }}
                                                · Paid Date: {{ $invoice->sponsorRenewal->paid_date ? $invoice->sponsorRenewal->paid_date->format('d M Y') : '—' }}</div>
                                        @endif
                                        @if ($invoice->event)
                                            <div class="mt-2 small">Event: <strong>{{ $invoice->event->name }}</strong></div>
                                        @endif
                                    </div>
                                </div>

                                <table class="table table-sm">
                                    <thead class="thead-light">
                                        <tr><th>Deskripsi</th><th class="text-center">Qty</th><th class="text-right">Harga Satuan</th><th class="text-right">Jumlah</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice->items as $item)
                                            <tr>
                                                <td>{{ $item['description'] }}</td>
                                                <td class="text-center">{{ $item['qty'] }}</td>
                                                <td class="text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                                                <td class="text-right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr><td colspan="3" class="text-right">Subtotal</td><td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
                                        @if ($invoice->vat_amount > 0)
                                            <tr><td colspan="3" class="text-right">PPN {{ rtrim(rtrim(number_format($invoice->vat_percent, 2, '.', ''), '0'), '.') }}%</td><td class="text-right">Rp {{ number_format($invoice->vat_amount, 0, ',', '.') }}</td></tr>
                                        @endif
                                        <tr class="font-weight-bold"><td colspan="3" class="text-right">Total</td><td class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td></tr>
                                    </tfoot>
                                </table>
                                @if ($invoice->notes)
                                    <div class="small text-muted">Catatan: {!! nl2br(e($invoice->notes)) !!}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        @include('admin.finance_invoices.partials._show-payment')
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
