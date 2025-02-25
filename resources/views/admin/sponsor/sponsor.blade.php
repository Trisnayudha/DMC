@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsors Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Sponsors Management</a></div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Sponsors</h2>

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
                <!-- End Card Info Section -->
                <div class="row">
                    <!-- Card Top 5 Sponsor Representative Attend -->
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
                                    <a href="{{ url('/admin/sponsors-representative-count') }}" class="btn btn-primary">Show
                                        More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section dengan Filter di dalam header card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Sponsors Management</h4>
                                <!-- Form Filter Sponsor Type -->
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
                                        <i class="fas fa-plus-circle"></i>
                                        Add Sponsor</a>
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
                                                            @elseif($post->package == 'platinum') badge-primary @endif
                                                        ">
                                                            {{ ucfirst($post->package) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox"
                                                                class="custom-control-input toggle-status"
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
                                                                class="btn btn-icon btn-sm btn-primary"
                                                                data-toggle="tooltip" title="Advertisement/Brochure">
                                                                <i class="fas fa-bullhorn"></i>
                                                            </a>

                                                            <!-- Sponsor Representative -->
                                                            <a href="{{ route('sponsors-representative.show', $post->id) }}"
                                                                class="btn btn-icon btn-sm btn-warning"
                                                                data-toggle="tooltip" title="Sponsor Representative">
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

                                                            <!-- Edit Data -->
                                                            <a href="{{ route('sponsors.edit', $post->id) }}"
                                                                class="btn btn-icon btn-sm btn-success"
                                                                data-toggle="tooltip" title="Edit Data">
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
@endpush
