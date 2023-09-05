@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsors Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Sponsors Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Sponsors </h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Sponsors Management</h4>
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
                                                            @elseif($post->package == 'gold')
                                                                badge-warning
                                                            @elseif($post->package == 'platinum')
                                                                badge-primary @endif
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
                                                        <a href="{{ route('sponsors.edit', $post->id) }}"
                                                            class="btn btn-success" title="Edit Data">
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <button class="btn btn-danger delete-sponsor"
                                                            data-id="{{ $post->id }}" title="Hapus Data">
                                                            <span class="fa fa-trash"></span>
                                                        </button>

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
                    url: 'sponsors/update-status/' + sponsorId,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            // Menampilkan pesan sukses menggunakan toast Stisla
                            toastr.success('Status berhasil diperbarui', 'Sukses', {
                                "positionClass": "toast-top-right"
                            });

                            console.log('Status berhasil diperbarui');
                        } else {
                            // Menampilkan pesan kesalahan menggunakan toast Stisla
                            toastr.error('Gagal memperbarui status', 'Error', {
                                "positionClass": "toast-top-right"
                            });

                            console.error('Gagal memperbarui status');
                        }
                    },
                    error: function(xhr) {
                        // Handle kesalahan Ajax
                        console.error('Terjadi kesalahan Ajax');
                    }
                });
            });
        });

        $(document).ready(function() {
            // Menggunakan event delegate untuk menangani banyak tombol Hapus
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
                                        // Redirect atau refresh tampilan setelah menghapus
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
