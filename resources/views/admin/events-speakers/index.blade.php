@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Events Speakers Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Events Speakers Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Events Speakers</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Events Speakers Management</h4>
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

                                <form method="GET" action="">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">

                                        </div>
                                        <div class="form-group col-md-2">

                                        </div>
                                        <div class="form-group col-md-2">

                                        </div>
                                        <div class="form-group col-md-2">

                                        </div>
                                        <div class="form-group col-md-2">

                                            <a href="javascript:void(0)"
                                                class="btn btn-block btn-icon icon-left btn-success btn-filter"
                                                id="addNewCategory">
                                                <i class="fas fa-plus-circle"></i>
                                                Tambah Speakers</a>

                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Name</th>
                                                <th>Job Title</th>
                                                <th>Company</th>
                                                <th>Image</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->company }}</td>
                                                    <td>
                                                        @if (!empty($post->image))
                                                            <img src="{{ asset($post->image) }}" alt="{{ $post->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                        @else
                                                            <span class="badge badge-light">No Image</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-success edit"><span
                                                                class="fa fa-edit"></span></a>
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-danger delete"><span class=" fa fa-trash"></a>
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
    <div class="modal fade" id="category-model" aria-hidden="true" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ajaxCategoryModel">Tambah Speaker</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="javascript:void(0)" id="addEditCategoryForm" name="addEditCategoryForm"
                    class="form-horizontal" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Nama speaker" required>
                        </div>
                        <div class="form-group">
                            <label for="job_title">Job Title</label>
                            <input type="text" name="job_title" id="job_title" class="form-control" placeholder="Job Title / Jabatan">
                        </div>
                        <div class="form-group">
                            <label for="company">Company</label>
                            <input type="text" name="company" id="company" class="form-control" placeholder="Perusahaan / Organisasi">
                        </div>
                        <div class="form-group">
                            <label for="image">Foto Speaker</label>
                            <input type="file" name="image" id="image" class="form-control-file" accept="image/*">
                            <div id="imagePreviewContainer" class="mt-2" style="display: none;">
                                <small class="text-muted d-block mb-1">Foto saat ini:</small>
                                <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height: 80px; max-width: 80px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-save">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function($) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#addNewCategory').click(function() {
                $('#addEditCategoryForm')[0].reset();
                $('#id').val('');
                $('#imagePreviewContainer').hide();
                $('#imagePreview').attr('src', '');
                $('#ajaxCategoryModel').html("Tambah Speaker");
                $('#btn-save').html('<i class="fas fa-save mr-1"></i> Simpan').prop("disabled", false);
                $('#category-model').modal('show');
            });

            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');

                // Reset form terlebih dahulu agar data speaker sebelumnya tidak tertinggal
                $('#addEditCategoryForm')[0].reset();
                $('#id').val('');
                $('#imagePreviewContainer').hide();
                $('#imagePreview').attr('src', '');
                $('#ajaxCategoryModel').html("Edit Speaker");
                $('#btn-save').html('<i class="fas fa-save mr-1"></i> Simpan').prop("disabled", false);

                // AJAX fetch dengan cache: false untuk memastikan data selalu fresh dari database
                $.ajax({
                    type: "GET",
                    url: "{{ url('admin/events/speakers') }}/" + id + "/edit",
                    cache: false,
                    dataType: 'json',
                    success: function(res) {
                        $('#id').val(res.id);
                        $('#name').val(res.name || '');
                        $('#company').val(res.company || '');
                        $('#job_title').val(res.job_title || '');

                        // Bersihkan input file (jangan set val string ke input file)
                        $('#image').val('');
                        if (res.image) {
                            var imgUrl = res.image.startsWith('http') ? res.image : '{{ asset("") }}' + res.image.replace(/^\//, '');
                            $('#imagePreview').attr('src', imgUrl);
                            $('#imagePreviewContainer').show();
                        } else {
                            $('#imagePreviewContainer').hide();
                        }

                        $('#category-model').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: "Gagal",
                            text: "Gagal memuat data speaker. Silakan coba lagi.",
                            icon: "error"
                        });
                    }
                });
            });

            $(document).on('click', '.delete', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: "Anda Yakin?",
                    text: "Ingin menghapus data speaker ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ url('admin/events/speakers') }}/" + id,
                            dataType: 'json',
                            cache: false,
                            success: function(res) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: "Data speaker berhasil dihapus.",
                                    icon: "success",
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(function() {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                var errMsg = "Gagal menghapus data speaker.";
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errMsg = xhr.responseJSON.error;
                                }
                                Swal.fire({
                                    title: "Gagal",
                                    text: errMsg,
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });

            $('#addEditCategoryForm').on('submit', function(e) {
                e.preventDefault();

                var id = $("#id").val();
                var name = $("#name").val();
                var company = $("#company").val();
                var job_title = $("#job_title").val();
                var imageInput = $("#image")[0];
                var image = (imageInput && imageInput.files.length > 0) ? imageInput.files[0] : null;

                var formData = new FormData();
                if (id) {
                    formData.append('id', id);
                }
                formData.append('name', name);
                formData.append('company', company);
                formData.append('job_title', job_title);
                if (image) {
                    formData.append('image', image);
                }

                var $btnSave = $("#btn-save");
                var originalHtml = $btnSave.html();
                $btnSave.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...').prop("disabled", true);

                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/events/speakers') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function(res) {
                        $('#category-model').modal('hide');
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Data speaker berhasil disimpan.",
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        $btnSave.html(originalHtml).prop("disabled", false);
                        var errMsg = "Gagal menyimpan data speaker.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errMsg = xhr.responseJSON.error;
                        }
                        Swal.fire({
                            title: "Gagal",
                            text: errMsg,
                            icon: "error"
                        });
                    }
                });
            });

            $('#laravel_crud').DataTable();
        });
    </script>
@endsection
