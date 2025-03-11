@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsors Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Sponsors Management</a></div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Sponsors</h2>
                @if ($expiredSponsors->count() > 0)
                    <!-- Notifikasi Alert untuk Expired Contracts -->
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-circle"></i> Expired Contracts</h4>
                        <p>The following companies have passed their contract end date:</p>
                        <ul>
                            @foreach ($expiredSponsors as $sponsor)
                                @php
                                    $monthNames = [
                                        '01' => 'Januari',
                                        '02' => 'Februari',
                                        '03' => 'Maret',
                                        '04' => 'April',
                                        '05' => 'Mei',
                                        '06' => 'Juni',
                                        '07' => 'Juli',
                                        '08' => 'Agustus',
                                        '09' => 'September',
                                        '10' => 'Oktober',
                                        '11' => 'November',
                                        '12' => 'Desember',
                                    ];
                                    $parts = explode('-', $sponsor->contract_end);
                                    $displayContractEnd = $monthNames[$parts[1]] . ' ' . $parts[0];
                                @endphp
                                <li>
                                    {{ $sponsor->name }} (Contract End: {{ $displayContractEnd }})
                                    <a href="#" class="btn btn-sm btn-info update-contract-btn"
                                        data-sponsor-id="{{ $sponsor->id }}"
                                        data-contract-start="{{ $sponsor->contract_start }}"
                                        data-contract-end="{{ $sponsor->contract_end }}">
                                        Update Contract
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Notifikasi Alert untuk Renewal Soon -->
                @if ($renewalSponsors->count() > 0)
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> Renewal Soon</h4>
                        <p>The following companies will need to renew their contract within the next 1-2 months:</p>
                        <ul>
                            @foreach ($renewalSponsors as $sponsor)
                                @php
                                    $monthNames = [
                                        '01' => 'Januari',
                                        '02' => 'Februari',
                                        '03' => 'Maret',
                                        '04' => 'April',
                                        '05' => 'Mei',
                                        '06' => 'Juni',
                                        '07' => 'Juli',
                                        '08' => 'Agustus',
                                        '09' => 'September',
                                        '10' => 'Oktober',
                                        '11' => 'November',
                                        '12' => 'Desember',
                                    ];
                                    $parts = explode('-', $sponsor->contract_end);
                                    $displayContractEnd = $monthNames[$parts[1]] . ' ' . $parts[0];
                                    $endDate = \Carbon\Carbon::createFromFormat(
                                        'Y-m',
                                        $sponsor->contract_end,
                                    )->endOfMonth();
                                    $daysLeft = now()->diffInDays($endDate, false);
                                @endphp
                                <li>
                                    {{ $sponsor->name }} (Contract End: {{ $displayContractEnd }}, in {{ $daysLeft }}
                                    days)
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <!-- Card Info Section (tetap sama seperti sebelumnya) -->
                <div class="row">
                    <!-- Card Platinum -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Platinum</h4>
                                </div>
                                <div class="card-body">
                                    {{ $platinumCount ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card Gold -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Gold</h4>
                                </div>
                                <div class="card-body">
                                    {{ $goldCount ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card Silver -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-secondary">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Silver</h4>
                                </div>
                                <div class="card-body">
                                    {{ $silverCount ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card Total -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalCount ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefit Usage Summary Cards -->
                <div class="row">
                    <!-- Card: Total Benefits Assigned -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Benefits Assigned</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalBenefitsAssigned }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card: Total Benefits Used -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Benefits Used</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalBenefitsUsed }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card: Total Benefits Unused -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Benefits Unused</h4>
                                </div>
                                <div class="card-body">
                                    {{ $totalBenefitsUnused }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card: Usage Rate -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Usage Rate</h4>
                                </div>
                                <div class="card-body">
                                    {{ $benefitUsageRate }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Top 5 Sponsor Representative Attend -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Top 5 Sponsor Representative Attend</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Company</th>
                                                <th>Count Attend</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topSponsors as $sponsor)
                                                <tr>
                                                    <td>{{ $sponsor->company }}</td>
                                                    <td>{{ $sponsor->count_attend }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-right mt-3">
                                    <a href="{{ url('/admin/sponsors-representative-count') }}"
                                        class="btn btn-primary">Show More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tabel Sponsor Engagement Count -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Sponsor Engagement Count</h4>
                            </div>
                            <div class="card-body">
                                @php
                                    // $engagementCount: collection mapping sponsor id to engagement count
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
                                <div class="text-right mt-3">
                                    <a href="{{ url('/admin/sponsor-engagement') }}" class="btn btn-primary">Show More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Near End Period Sponsors -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>5 Companies Nearing Contract End</h4>
                            <p class="text-muted">Sponsors with contract end date within the next 3 months</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Contract End</th>
                                            <th>Time Left</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($nearEndSponsors as $sponsor)
                                            @php
                                                // Konversi contract_end (format "YYYY-MM") ke tanggal dengan endOfMonth()
                                                $endDate = \Carbon\Carbon::createFromFormat(
                                                    'Y-m',
                                                    $sponsor->contract_end,
                                                )->endOfMonth();
                                                // Hitung sisa hari dari hari ini sampai akhir bulan kontrak
                                                $daysLeft = now()->diffInDays($endDate, false);
                                                // Tentukan badge warna dan label berdasarkan sisa hari
                                                if ($daysLeft <= 30) {
                                                    $badgeColor = 'danger';
                                                    $urgencyText = 'Urgent';
                                                } elseif ($daysLeft <= 60) {
                                                    $badgeColor = 'warning';
                                                    $urgencyText = 'Moderate';
                                                } else {
                                                    $badgeColor = 'success';
                                                    $urgencyText = 'Safe';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $sponsor->name }}</td>
                                                <td>{{ $sponsor->contract_end }}</td>
                                                <td>
                                                    {{ $daysLeft }} days
                                                    <span
                                                        class="badge badge-{{ $badgeColor }}">{{ $urgencyText }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('sponsors.benefit.index') }}" class="btn btn-info">Show More</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section dengan Filter di dalam header card (Sponsor Management List) -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Sponsors Management</h4>
                            <form method="GET" action="{{ route('sponsors.index') }}" class="form-inline">
                                <div class="input-group">
                                    <select name="type" id="filterType" class="form-control"
                                        onchange="this.form.submit()">
                                        <option value="">Semua</option>
                                        <option value="platinum" {{ request('type') == 'platinum' ? 'selected' : '' }}>
                                            Platinum</option>
                                        <option value="gold" {{ request('type') == 'gold' ? 'selected' : '' }}>Gold
                                        </option>
                                        <option value="silver" {{ request('type') == 'silver' ? 'selected' : '' }}>
                                            Silver</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-warning">
                                    <div class="alert-title">Whoops!</div>
                                    @lang('general.validation_error_message')
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <div class="float-right">
                                <a href="{{ url('admin/sponsors/create') }}"
                                    class="btn btn-block btn-icon icon-left btn-success btn-filter mb-3"
                                    id="addNewCategory">
                                    <i class="fas fa-plus-circle"></i> Add Sponsor
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table id="laravel_crud" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="10px">No</th>
                                            <th>Name Sponsor</th>
                                            <th>Package</th>
                                            <th>Status Display</th>
                                            <th width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        @foreach ($data as $post)
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td>{{ $post->name }}</td>
                                                <td>
                                                    <span
                                                        class="badge
                                                        @if ($post->package == 'silver') badge-secondary
                                                        @elseif($post->package == 'gold') badge-warning
                                                        @elseif($post->package == 'platinum') badge-primary @endif">
                                                        {{ ucfirst($post->package) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input toggle-status"
                                                            data-id="{{ $post->id }}"
                                                            id="statusToggle{{ $post->id }}"
                                                            {{ $post->status == 'publish' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="statusToggle{{ $post->id }}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap" style="gap: 4px; max-width: 150px;">
                                                        <!-- Advertisement/Brochure -->
                                                        <a href="{{ route('sponsors-advertising.show', $post->id) }}"
                                                            class="btn btn-icon btn-sm btn-primary" data-toggle="tooltip"
                                                            title="Advertisement/Brochure">
                                                            <i class="fas fa-bullhorn"></i>
                                                        </a>

                                                        <!-- Sponsor Representative -->
                                                        <a href="{{ route('sponsors-representative.show', $post->id) }}"
                                                            class="btn btn-icon btn-sm btn-warning" data-toggle="tooltip"
                                                            title="Sponsor Representative">
                                                            <i class="fas fa-user-friends"></i>
                                                        </a>

                                                        <!-- Alamat Sponsor -->
                                                        <a href="{{ route('sponsors-address.show', $post->id) }}"
                                                            class="btn btn-icon btn-sm btn-info" data-toggle="tooltip"
                                                            title="Alamat Sponsor">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                        </a>

                                                        <!-- Photos/Videos Activity -->
                                                        <a href="{{ route('photos-videos-activity.show', $post->id) }}"
                                                            class="btn btn-icon btn-sm btn-secondary"
                                                            data-toggle="tooltip" title="Photos/Videos Activity">
                                                            <i class="fas fa-camera"></i>
                                                        </a>

                                                        <!-- Sponsor Benefit Management -->
                                                        <a href="{{ route('sponsors.benefit.detail', $post->id) }}"
                                                            class="btn btn-icon btn-sm btn-info" data-toggle="tooltip"
                                                            title="Sponsor Benefit Management">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </a>

                                                        <!-- Edit Data -->
                                                        <a href="{{ route('sponsors.edit', $post->id) }}"
                                                            class="btn btn-icon btn-sm btn-success" data-toggle="tooltip"
                                                            title="Edit Data">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </a>

                                                        <!-- Hapus Data -->
                                                        <button class="btn btn-icon btn-sm btn-danger delete-sponsor"
                                                            data-id="{{ $post->id }}" data-toggle="tooltip"
                                                            title="Hapus Data">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- End Table Section -->
            </div>
        </section>
    </div>
@endsection

@push('bottom')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
    </script>
    <!-- Modal Update Contract -->
    <div class="modal fade" id="updateContractModal" tabindex="-1" role="dialog"
        aria-labelledby="updateContractModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="updateContractForm">
                    @csrf
                    @method('POST')
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateContractModalLabel">Update Contract</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="modalSponsorId" name="sponsor_id" value="">
                        <div class="form-group">
                            <label for="modalContractStart">Contract Start</label>
                            <input type="month" name="contract_start" id="modalContractStart" class="form-control"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="modalContractEnd">Contract End</label>
                            <input type="month" name="contract_end" id="modalContractEnd" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Contract</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.toggle-status').change(function() {
                let sponsorId = $(this).data('id');
                let status = this.checked ? 'publish' : 'draft';

                $.ajax({
                    url: '/admin/sponsors/update-status/' + sponsorId,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Status berhasil diperbarui', 'Sukses', {
                                "positionClass": "toast-top-right"
                            });
                        } else {
                            toastr.error('Gagal memperbarui status', 'Error', {
                                "positionClass": "toast-top-right"
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Terjadi kesalahan Ajax');
                    }
                });
            });
        });

        $(document).ready(function() {
            $('.table').on('click', '.delete-sponsor', function() {
                let sponsorId = $(this).data('id');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data sponsor akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'sponsors/' + sponsorId,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Sukses!',
                                        text: 'Data sponsor berhasil dihapus.',
                                        icon: 'success'
                                    }).then((result) => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: 'Terjadi kesalahan saat menghapus data sponsor.',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan saat menghapus data sponsor.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    <script>
        // Trigger modal update contract pada tombol quick action
        $(document).on('click', '.update-contract-btn', function(e) {
            e.preventDefault();
            let sponsorId = $(this).data('sponsor-id');
            let contractStart = $(this).data('contract-start');
            let contractEnd = $(this).data('contract-end');
            // Set data ke modal
            $('#modalSponsorId').val(sponsorId);
            $('#modalContractStart').val(contractStart);
            $('#modalContractEnd').val(contractEnd);
            // Tampilkan modal
            $('#updateContractModal').modal('show');
        });

        // Submit form update contract via AJAX
        $('#updateContractForm').on('submit', function(e) {
            e.preventDefault();
            let sponsorId = $('#modalSponsorId').val();
            let url = '/admin/sponsors/' + sponsorId + '/update-contract';
            let formData = $(this).serialize();

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Success', {
                            "positionClass": "toast-top-right"
                        });
                        $('#updateContractModal').modal('hide');
                        // Reload halaman atau update bagian contract pada dashboard
                        location.reload();
                    } else {
                        toastr.error(response.message, 'Error', {
                            "positionClass": "toast-top-right"
                        });
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred while updating the contract.', 'Error', {
                        "positionClass": "toast-top-right"
                    });
                }
            });
        });
    </script>
@endpush
