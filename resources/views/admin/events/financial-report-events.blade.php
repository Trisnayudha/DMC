@extends('layouts.inspire.master')

@section('content')
<div class="content-wrapper">
    <section class="section">
        <div class="section-header">
            <h1>Payment &amp; Financial Report</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item active">Financial Report</div>
            </div>
        </div>
        <div class="section-body">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center" style="gap:12px;">
                    <div>
                        <h4 class="mb-1">Paid Events</h4>
                        <p class="small text-muted mb-0">Pilih event berbayar untuk melihat dan mengunduh laporan keuangannya.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.financial_reports.index') }}" class="d-flex align-items-center" style="gap:8px;">
                        <label for="financial-event-search" class="sr-only">Cari event</label>
                        <input type="search" id="financial-event-search" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Cari nama event...">
                        <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                        @if ($search !== '') <a href="{{ route('admin.financial_reports.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a> @endif
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 financial-events-table">
                            <thead><tr><th>No.</th><th>Event</th><th>Tanggal</th><th class="text-right">Laporan</th></tr></thead>
                            <tbody>
                                @forelse ($events as $event)
                                    <tr>
                                        <td>{{ $events->firstItem() + $loop->index }}</td>
                                        <td><strong>{{ $event->name }}</strong></td>
                                        <td class="text-nowrap">{{ $event->start_date ? \Illuminate\Support\Carbon::parse($event->start_date)->format('d M Y') : '—' }}</td>
                                        <td class="text-right"><a href="{{ route('events-financial-report', ['slug' => $event->slug]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-chart-bar mr-1"></i> Lihat Laporan</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">{{ $search ? 'Tidak ada event berbayar yang cocok dengan pencarian.' : 'Belum ada event dengan tiket berbayar.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                    <small class="text-muted">{{ $events->firstItem() ?? 0 }}–{{ $events->lastItem() ?? 0 }} dari {{ $events->total() }} event</small>
                    {{ $events->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
</div>
<style>
    .financial-events-table th, .financial-events-table td { padding: 12px 18px; vertical-align: middle; }
    .financial-events-table th { background:#f8f9fc; color:#67728a; font-size:11px; }
    .financial-events-table td { font-size:13px; }
    .section .card-footer .pagination { margin:0; }
    @media(max-width:575px) { .section .card-footer .page-item:not(:first-child):not(:last-child):not(.active) { display:none; } }
</style>
@endsection
