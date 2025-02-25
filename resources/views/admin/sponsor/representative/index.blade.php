@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsors Representative Count Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="#">Sponsors Representative Count Management</a></div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Sponsors Representative Count</h2>

                <!-- Form Filter Tahun & Company -->
                <div class="mb-3">
                    <form method="GET" action="{{ route('sponsors.representative.index') }}" class="form-inline">

                        {{-- Filter Tahun --}}
                        <label for="year" class="mr-2">Filter Tahun:</label>
                        <input type="number" name="year" id="year" class="form-control mr-2"
                            value="{{ $year }}" min="2000" max="{{ now()->year }}">

                        {{-- Filter Sponsor --}}
                        <label for="company" class="mr-2">Sponsor:</label>
                        <select name="company" id="company" class="form-control mr-2">
                            <option value="">-- All Sponsor --</option>
                            @foreach ($sponsorList as $sponsor)
                                <option value="{{ $sponsor->name }}"
                                    {{ $filterSponsor == $sponsor->name ? 'selected' : '' }}>
                                    {{ $sponsor->name }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
                <!-- End Filter Tahun & Company -->

                <!-- Tabel Detail Representative & Event Attendance -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Detail Representative & Event Attendance (Tahun: {{ $year }})</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Name</th>
                                                <th>Company</th>
                                                <th>Attend Time</th>
                                                <th>Event</th>
                                                <th>Present</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($representatives as $index => $rep)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $rep->representative_name }}</td>
                                                    <td>{{ $rep->company }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($rep->attend_time)->format('d-m-Y H:i') }}
                                                    </td>
                                                    <td>{{ $rep->event_name }}</td>
                                                    <td>
                                                        @if ($rep->present)
                                                            Hadir pada
                                                            {{ \Carbon\Carbon::parse($rep->present)->format('d-m-Y H:i') }}
                                                        @else
                                                            Tidak Hadir
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Tidak ada data representative.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Sponsor yang Representativenya Tidak Pernah Attend -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Sponsor yang Representativenya Tidak Pernah Attend (Tahun: {{ $year }})</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="laravel_crud_non_attend" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Company</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($nonAttendSponsors as $index => $sponsor)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $sponsor->company }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center">Tidak ada data sponsor.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <script>
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
            $('#laravel_crud_non_attend').DataTable();
        });
    </script>
@endpush
