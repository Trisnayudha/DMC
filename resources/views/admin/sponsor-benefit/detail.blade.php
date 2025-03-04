@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Benefit Management for {{ $sponsor->name }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="{{ route('sponsors.index') }}">Sponsors Management</a></div>
                    <div class="breadcrumb-item active">Benefit Details</div>
                </div>
            </div>
            <div class="section-body">
                <!-- Overview with Progress Bar -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4 class="mb-1">Benefit Usage Overview</h4>
                                <p class="mb-3 text-muted">Monitor the overall benefit utilization for this sponsor</p>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $benefitUsageRate }}%;" aria-valuenow="{{ $benefitUsageRate }}"
                                        aria-valuemin="0" aria-valuemax="100">
                                        {{ $benefitUsageRate }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefit Summary Cards -->
                <div class="row">
                    <!-- Total Benefits -->
                    <div class="col-md-4">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Benefits</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalCount }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Benefits Used -->
                    <div class="col-md-4">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Benefits Used</h4>
                                </div>
                                <div class="card-body">
                                    {{ $usedCount }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Benefits Unused -->
                    <div class="col-md-4">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Benefits Unused</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalCount - $usedCount }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefit Details Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Benefit Details per Period</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('sponsors.index') }}" class="btn btn-icon btn-info"><i
                                            class="fas fa-arrow-left"></i> Back</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Period</th>
                                                <th>Benefit</th>
                                                <th>Status</th>
                                                <th>Used At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($benefitDetails as $detail)
                                                <tr>
                                                    <td>{{ $detail->period }}</td>
                                                    <td>{{ $detail->benefit->name }}</td>
                                                    <td>
                                                        @if ($detail->status == 'used')
                                                            <span class="badge badge-success">Used</span>
                                                        @else
                                                            <span class="badge badge-warning">Unused</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $detail->used_at ? $detail->used_at->format('Y-m-d') : '-' }}
                                                    </td>
                                                    <td>
                                                        @if ($detail->status == 'unused')
                                                            <form
                                                                action="{{ route('sponsors.benefit.markUsed', $detail->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-primary"
                                                                    onclick="return confirm('Tandai benefit ini sebagai sudah digunakan?')">
                                                                    Mark as Used
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button class="btn btn-sm btn-secondary" disabled>Used</button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('sponsors.index') }}" class="btn btn-secondary">Back to Sponsors
                                    Management</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endpush
