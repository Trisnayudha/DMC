@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsor Social Media Engagement</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="{{ route('sponsor-engagement.index') }}">Engagement</a></div>
                </div>
            </div>
            <div class="section-body">
                <!-- Filter Form -->
                <div class="row mb-3">
                    <div class="col-12">
                        <form action="{{ route('sponsor-engagement.index') }}" method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="platform" class="mr-2">Platform:</label>
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">All</option>
                                    <option value="Instagram" {{ request('platform') == 'Instagram' ? 'selected' : '' }}>
                                        Instagram</option>
                                    <option value="LinkedIn" {{ request('platform') == 'LinkedIn' ? 'selected' : '' }}>
                                        LinkedIn</option>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <label for="year" class="mr-2">Year:</label>
                                <input type="number" name="year" id="year" class="form-control"
                                    value="{{ request('year', now()->format('Y')) }}" min="2000" max="2099">
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                    </div>
                </div>

                <!-- Add Engagement Button -->
                <div class="row mb-3">
                    <div class="col-12 text-right">
                        <a href="{{ route('sponsor-engagement.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Engagement
                        </a>
                    </div>
                </div>

                <!-- Engagement Statistics Cards -->
                <div class="row">
                    <!-- Total Engagement -->
                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Engagement</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['like'] + $stats['comment'] + $stats['share'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Likes -->
                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-thumbs-up"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Likes</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['like'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Comments -->
                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-comment"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Comments</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['comment'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Shares -->
                    <div class="col-md-3">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-share"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Shares</h4>
                                </div>
                                <div class="card-body">
                                    {{ $stats['share'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sponsor Engagement Count Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Sponsor Engagement Count</h4>
                            </div>
                            <div class="card-body">
                                @php
                                    // $engagementCount: collection mapping sponsor id ke jumlah engagement
                                @endphp
                                <div class="table-responsive">
                                    <table class="table table-striped" id="engagementCountTable">
                                        <thead>
                                            <tr>
                                                <th>Sponsor</th>
                                                <th>Engagement Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($allSponsors as $sponsor)
                                                <tr>
                                                    <td>{{ $sponsor->name }}</td>
                                                    <td>{{ $engagementCount->has($sponsor->id) ? $engagementCount[$sponsor->id] : 0 }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if ($allSponsors->isEmpty())
                                                <tr>
                                                    <td colspan="2" class="text-center">No sponsor data found for the
                                                        selected filters.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Engagement Activities List Table (menggunakan DataTables) -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Engagement Activities</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="engagementTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Sponsor</th>
                                                <th>Platform</th>
                                                <th>Activity Type</th>
                                                <th>Activity Date</th>
                                                <th>Screenshot</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($engagements as $index => $engagement)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $engagement->sponsor->name }}</td>
                                                    <td>{{ $engagement->platform }}</td>
                                                    <td>{{ ucfirst($engagement->activity_type) }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($engagement->activity_date)->format('d M Y') }}
                                                    </td>
                                                    <td>
                                                        @if ($engagement->screenshot)
                                                            <a href="{{ asset($engagement->screenshot) }}" target="_blank">
                                                                <img src="{{ asset($engagement->screenshot) }}"
                                                                    alt="screenshot" style="width:50px;height:auto;">
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if ($engagements->isEmpty())
                                                <tr>
                                                    <td colspan="6" class="text-center">No engagement data found for the
                                                        selected filters.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Jika diperlukan, tambahkan pagination di sini -->
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <!-- DataTables CSS & JS (gunakan CDN atau asset lokal) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#engagementTable').DataTable({
                "order": [
                    [4, "desc"]
                ],
                "pageLength": 10
            });

            $('#engagementCountTable').DataTable({
                "order": [
                    [1, "desc"]
                ],
                "paging": false,
                "searching": false,
                "info": false
            });
        });
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endpush
