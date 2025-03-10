@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Marketing Ads Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Marketing Ads Management</a></div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Marketing Ads</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Marketing Ads Management</h4>
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
                                        <div class="form-group col-md-4"></div>
                                        <div class="form-group col-md-2"></div>
                                        <div class="form-group col-md-2"></div>
                                        <div class="form-group col-md-2"></div>
                                        <div class="form-group col-md-2">
                                            <a href="javascript:void(0)"
                                                class="btn btn-block btn-icon icon-left btn-success btn-filter"
                                                id="addNewCategory">
                                                <i class="fas fa-plus-circle"></i>
                                                Tambah Ads
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Image</th>
                                                <th>Location</th>
                                                <th>Type</th>
                                                <th>Target ID</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>
                                                        <img alt="image" src="{{ asset($post->image) }}"
                                                            class="rounded-circle" width="35" data-toggle="tooltip">
                                                    </td>
                                                    <td>{{ $post->location }}</td>
                                                    <td>{{ $post->type }}</td>
                                                    <td>{{ $post->target_id }}</td>
                                                    <td>
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-success edit">
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-danger delete">
                                                            <span class="fa fa-trash"></span>
                                                        </a>
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

    <!-- Modal -->
    <div class="modal fade" id="category-model" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ajaxCategoryModel"></h4>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="addEditCategoryForm" name="addEditCategoryForm"
                        class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <!-- Field file input -->
                        <div class="form-group">
                            <label for="">Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image">
                                <label class="custom-file-label" for="image">Choose file</label>
                            </div>
                        </div>
                        <!-- Field hidden untuk base64 image -->
                        <input type="hidden" name="base64_image" id="base64_image">

                        <div class="form-group">
                            <label>Location</label>
                            <select class="form-control" name="location" id="location">
                                <option value="">Choose</option>
                                <option value="popup">Pop Up Home Page</option>
                                <option value="splash">Splash Screen</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" name="type" id="type">
                                <option value="">Choose</option>
                                <option value="events">Event</option>
                                <option value="news">News</option>
                                <option value="website">Website</option>
                            </select>
                        </div>
                        <!-- Container untuk target_id yang akan berubah: dropdown (events/news) atau input teks (website) -->
                        <div class="form-group" id="targetGroup" style="display:none;"></div>
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary" id="btn-save" value="addNewCategory">Save
                                changes</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    <!-- Include Plugin JS -->
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(function() {
            $('#summernote').summernote();
            bsCustomFileInput.init();
        });

        // Konversi file image ke base64 ketika file dipilih
        $('#image').on('change', function(e) {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(evt) {
                    // Simpan hasil base64 ke input hidden
                    $('#base64_image').val(evt.target.result);
                    console.log('Base64 Image:', evt.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle dynamic load target_id options berdasarkan type
        $(document).ready(function() {
            $('#type').change(function() {
                var selectedType = $(this).val();
                if (selectedType === 'events') {
                    var selectHtml =
                        '<select name="target_id" id="target_id" class="form-control"></select>';
                    $('#targetGroup').html(selectHtml).show();
                    $.ajax({
                        url: "{{ url('admin/marketing-ads/event') }}",
                        method: "GET",
                        success: function(data) {
                            if (data.success) {
                                var payload = data.payload;
                                for (var i = 0; i < payload.length; i++) {
                                    $('#target_id').append('<option value="' + payload[i].slug +
                                        '">' + payload[i].name + '</option>');
                                }
                            }
                        }
                    });
                } else if (selectedType === 'news') {
                    var selectHtml =
                        '<select name="target_id" id="target_id" class="form-control"></select>';
                    $('#targetGroup').html(selectHtml).show();
                    $.ajax({
                        url: "{{ url('admin/marketing-ads/news') }}",
                        method: "GET",
                        success: function(data) {
                            if (data.success) {
                                var payload = data.payload;
                                for (var i = 0; i < payload.length; i++) {
                                    $('#target_id').append('<option value="' + payload[i].slug +
                                        '">' + payload[i].title + '</option>');
                                }
                            }
                        }
                    });
                } else if (selectedType === 'website') {
                    var inputHtml =
                        '<input type="text" name="target_id" id="target_id" class="form-control" placeholder="Masukkan link website">';
                    $('#targetGroup').html(inputHtml).show();
                } else {
                    $('#targetGroup').hide();
                }
            });
        });

        // Handle CRUD dengan AJAX
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Modal tambah
            $('#addNewCategory').click(function() {
                $('#addEditCategoryForm').trigger("reset");
                $('#ajaxCategoryModel').html("Add Category");
                $('#targetGroup').hide();
                $('#category-model').modal('show');
            });

            // Edit: ambil data untuk di-edit
            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/marketing-ads/edit') }}",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').html("Edit Category");
                        $('#category-model').modal('show');
                        $('#id').val(res.id);
                        $('#location').val(res.location);
                        $('#type').val(res.type).trigger('change');
                        // Setelah field targetGroup ter-load, set nilainya
                        setTimeout(function() {
                            $('#target_id').val(res.target_id);
                        }, 500);
                    }
                });
            });

            // Hapus data
            $(document).on('click', '.delete', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: "Anda Yakin ?",
                    text: "Ingin Menghapus Data ini.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ url('admin/marketing-ads/delete') }}",
                            data: {
                                id: id
                            },
                            dataType: 'json',
                            success: function(res) {
                                Swal.fire({
                                    title: "Success",
                                    icon: "success",
                                    showConfirmButton: false,
                                    position: 'center',
                                    timer: 1500
                                });
                                window.location.reload();
                            }
                        });
                    }
                });
            });

            // Simpan data (create/update)
            $(document).on('click', '#btn-save', function(event) {
                event.preventDefault();
                var form = $('#addEditCategoryForm')[0];
                var data = new FormData(form);
                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/marketing-ads/add') }}",
                    data: data,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        Swal.fire({
                            title: "Success",
                            icon: "success",
                            showConfirmButton: false,
                            position: 'center',
                            timer: 1500
                        });
                        window.location.reload();
                        $("#btn-save").html('Submit');
                        $("#btn-save").attr("disabled", false);
                    }
                });
            });
        });

        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
    </script>
@endsection
