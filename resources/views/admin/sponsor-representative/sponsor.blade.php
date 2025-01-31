@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsor Representatives</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">
                        <a href="{{ route('home') }}">Dashboard</a>
                    </div>
                    <div class="breadcrumb-item active">
                        <span>Representatives Management</span>
                    </div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">List of Sponsor Representatives</h2>

                <div class="row">
                    <div class="col-lg-12">

                        {{-- Contoh jika pakai flash session --}}
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h4>Data Sponsor Representatives</h4>
                                <div class="card-header-action">
                                    {{-- Tombol Add --}}
                                    <button id="addNewSponsor" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Representative
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th>Name</th>
                                                <th>Job Title</th>
                                                <th>Instagram</th>
                                                <th>LinkedIn</th>
                                                <th width="12%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach ($data as $row)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $row->name }}</td>
                                                    <td>{{ $row->job_title }}</td>
                                                    <td>{{ $row->instagram }}</td>
                                                    <td>{{ $row->linkedin }}</td>
                                                    <td>
                                                        {{-- Tombol Edit --}}
                                                        <button class="btn btn-success btn-sm edit-sponsor"
                                                            data-id="{{ $row->id }}">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        {{-- Tombol Delete --}}
                                                        <button class="btn btn-danger btn-sm delete-sponsor"
                                                            data-id="{{ $row->id }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div> <!-- /.table-responsive -->
                            </div> <!-- /.card-body -->
                        </div> <!-- /.card -->

                    </div> <!-- /.col-lg-12 -->
                </div> <!-- /.row -->
            </div> <!-- /.section-body -->
        </section>
    </div>

    {{-- Modal Add/Edit --}}
    <div class="modal fade" id="modal-sponsor" tabindex="-1" role="dialog" aria-labelledby="modalSponsorTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="form-sponsor" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="id"> {{-- Hidden ID untuk Edit --}}
                <input type="hidden" name="sponsor_id" id="sponsor_id" value="{{ $sponsor_id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSponsorTitle">Add Representative</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        {{-- Name --}}
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name" id="name">
                        </div>
                        {{-- Job Title --}}
                        <div class="form-group">
                            <label for="job_title">Job Title</label>
                            <input type="text" class="form-control" name="job_title" id="job_title">
                        </div>
                        {{-- Instagram --}}
                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" class="form-control" name="instagram" id="instagram">
                        </div>
                        {{-- LinkedIn --}}
                        <div class="form-group">
                            <label for="linkedin">LinkedIn</label>
                            <input type="text" class="form-control" name="linkedin" id="linkedin">
                        </div>
                        {{-- Image --}}
                        <div class="form-group">
                            <label for="image">Image Profile</label>
                            <input type="file" class="form-control" name="image" id="image">
                            <small class="form-text text-muted">
                                Kosongkan jika tidak ingin mengubah foto saat edit
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="btn-save" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('bottom')
    <script>
        $(document).ready(function() {
            // Jika Anda gunakan DataTables
            $('#laravel_crud').DataTable();

            // Setup CSRF (jika belum ada di tempat lain)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // =========================
            // 1) Show modal ADD
            // =========================
            $('#addNewSponsor').click(function() {
                // Reset form
                $('#form-sponsor')[0].reset();
                $('#id').val('');
                // Ubah title modal
                $('#modalSponsorTitle').text('Add Sponsor Representative');
                // Tampilkan modal
                $('#modal-sponsor').modal('show');
            });

            // =========================
            // 2) Show modal EDIT
            // =========================
            $(document).on('click', '.edit-sponsor', function() {
                var id = $(this).data('id');

                // Lakukan GET ke route resource: sponsors-representative/{id}/edit
                $.ajax({
                    url: '/admin/sponsors-representative/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        // Isi form
                        $('#id').val(res.id);
                        $('#name').val(res.name);
                        $('#job_title').val(res.job_title);
                        $('#instagram').val(res.instagram);
                        $('#linkedin').val(res.linkedin);

                        // sponsor_id kalau perlu diedit juga, sesuaikan:
                        // $('#sponsor_id').val(res.sponsor_id);

                        // Ubah title modal
                        $('#modalSponsorTitle').text('Edit Sponsor Representative');
                        // Tampilkan modal
                        $('#modal-sponsor').modal('show');
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        Swal.fire('Error', 'Tidak dapat mengambil data', 'error');
                    }
                });
            });

            // =========================
            // 3) Submit form (Add/Update)
            // =========================
            $('#form-sponsor').submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                var id = $('#id').val(); // cek hidden input id

                let url = '/admin/sponsors-representative';
                let method = 'POST';

                if (id) {
                    // Edit
                    url = '/admin/sponsors-representative/' + id;
                    formData.append('_method', 'PUT'); // resource route expects PUT for update
                }

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false, // untuk FormData
                    contentType: false, // untuk FormData
                    success: function(res) {
                        if (res.success) {
                            // Tutup modal
                            $('#modal-sponsor').modal('hide');
                            // Notifikasi
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                // Reload halaman
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    }
                });
            });

            // =========================
            // 4) Delete data
            // =========================
            $(document).on('click', '.delete-sponsor', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/sponsors-representative/' + id,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        title: 'Terhapus!',
                                        text: res.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.log(xhr);
                                Swal.fire('Error', 'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
