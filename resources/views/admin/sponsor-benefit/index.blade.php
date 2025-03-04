@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Benefit Sponsor Dashboard</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Benefit Sponsor</a></div>
                </div>
            </div>
            <div class="section-body">
                <!-- Filter Periode berdasarkan Tahun -->
                <div class="row mb-3">
                    <div class="col-12">
                        <form action="{{ route('sponsors.benefit.index') }}" method="GET" class="form-inline">
                            <div class="form-group">
                                <label for="year" class="mr-2">Select Year:</label>
                                <input type="number" name="year" id="year" class="form-control"
                                    value="{{ $year }}" min="2000" max="2099">
                            </div>
                            <button type="submit" class="btn btn-primary ml-2">Filter</button>
                        </form>
                    </div>
                </div>

                <!-- Summary Benefit Cards -->
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
                                    {{ $totalBenefits }}
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
                                    {{ $usedBenefits }}
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
                                    {{ $unusedBenefits }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Usage Rate Card -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Overall Benefit Usage Rate ({{ $year }})</h4>
                            </div>
                            <div class="card-body">
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

                <!-- Category Statistics Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Benefit Category Statistics</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Total Benefits</th>
                                                <th>Used</th>
                                                <th>% Used</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($categoryStats as $stat)
                                                <tr>
                                                    <td>{{ $stat['category'] }}</td>
                                                    <td>{{ $stat['total'] }}</td>
                                                    <td>{{ $stat['used'] }}</td>
                                                    <td>{{ $stat['percent_used'] }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sponsor List Table with Benefit Category Percentages -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Sponsor Benefit Usage by Category ({{ $year }})</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('sponsors.benefit.detail', ['sponsor' => 0]) }}"
                                        class="btn btn-info">View All Details</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Sponsor Name</th>
                                                <th>Category Usage</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sponsorStats as $index => $sponsor)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $sponsor['sponsor_name'] }}</td>
                                                    <td>
                                                        @foreach ($sponsor['category_percentages'] as $category => $percent)
                                                            <div class="mb-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <span
                                                                        class="font-weight-bold">{{ $category }}</span>
                                                                    <span>{{ $percent }}%</span>
                                                                </div>
                                                                <div class="progress" style="height: 6px;">
                                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                                        style="width: {{ $percent }}%;"
                                                                        aria-valuenow="{{ $percent }}"
                                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('sponsors.benefit.detail', $sponsor['sponsor_id']) }}"
                                                            class="btn btn-sm btn-primary">
                                                            View Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if (count($sponsorStats) == 0)
                                                <tr>
                                                    <td colspan="4" class="text-center">No sponsor data available for the
                                                        selected year.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Optionally add pagination if sponsorStats is a paginated result -->
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
