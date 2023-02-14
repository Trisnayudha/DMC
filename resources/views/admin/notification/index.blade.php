@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Notification Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Notification Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Notification</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Notification Management</h4>
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
                                                Tambah Notif</a>

                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Title</th>
                                                <th>Message</th>
                                                <th>Type</th>
                                                <th>Category</th>
                                                <th>Target Slug</th>
                                                <th>Target Id</th>
                                                <th>Uname</th>
                                                <th>Users Id</th>
                                                <th>All Users</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->title }}</td>
                                                    <td>{{ $post->message }}</td>
                                                    <td>{{ $post->type }}</td>
                                                    <td>{{ $post->category }}</td>
                                                    <td>{{ $post->target_slug }}</td>
                                                    <td>{{ $post->target_id }}</td>
                                                    <td>{{ $post->uname }}</td>
                                                    <td>{{ $post->users_id }}</td>
                                                    <td>{{ $post->all_users }}</td>
                                                    <td>

                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-success edit"><span
                                                                class="fa fa-edit"></span></a>
                                                        {{-- @dd($post-id); --}}
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
    <div class="modal fade" id="category-model" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ajaxCategoryModel"></h4>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="addEditCategoryForm" name="addEditCategoryForm"
                        class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="form-group">
                            <label for="">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="">Message</label>
                            <input type="text" name="message" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select class="form-control" name="category" id="category" required>
                                <option value="">Choose</option>
                                <option value="notification">Notification</option>
                                <option value="highlight">Highlight</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" name="type" id="type" required>
                                <option value="">Choose</option>
                                <option value="events">Event</option>
                                <option value="news">News</option>
                                <option value="broadcast">Broadcast</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="target_id" id="target_id" class="form-control" style="display:none;">

                            </select>
                        </div>
                        <div class="form-group">
                            <label>Blasting</label>
                            <select class="form-control" name="all_users" id="all_users" required>
                                <option value="">Choose</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select name="users_id[]" id="users_id" class="form-control" style="display:none;">

                            </select>
                        </div>

                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary" id="btn-save" value="addNewCategory">Save
                                changes
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">

                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(function() {
            $('#summernote').summernote()
            bsCustomFileInput.init();
        });
        $(document).ready(function() {
            $('#type').change(function() {
                if ($(this).val() === 'events') {
                    $('#target_id').empty();
                    $('#target_id').show();
                    $('#target_id').select2();
                    $.ajax({
                        url: "{{ url('marketing-ads/event') }}",
                        method: "GET",
                        success: function(data) {
                            console.log(data)
                            if (data.success) {
                                var payload = data.payload;
                                for (var i = 0; i < payload.length; i++) {
                                    $('#target_id').append('<option value="' + payload[i].slug +
                                        '">' + payload[i].name + '</option>');
                                }
                            }
                        }
                    });
                } else if ($(this).val() === 'news') {
                    $('#target_id').empty();
                    $('#target_id').show();
                    $('#target_id').select2();
                    $.ajax({
                        url: "{{ url('marketing-ads/news') }}",
                        method: "GET",
                        success: function(data) {
                            console.log(data)
                            if (data.success) {
                                var payload = data.payload;
                                for (var i = 0; i < payload.length; i++) {
                                    $('#target_id').append('<option value="' + payload[i].slug +
                                        '">' + payload[i].title + '</option>');
                                }
                            }
                        }
                    });
                } else {
                    $('#target_id').empty();
                    $('#target_id').select2('destroy');
                    $('#target_id').hide();
                }
            });
            $('#all_users').change(function() {
                if ($(this).val() === 'no') {
                    $('#users_id').empty();
                    $('#users_id').show();
                    $('#users_id').select2({
                        multiple: true
                    });
                    $.ajax({
                        url: "{{ url('admin/notification/users') }}",
                        method: "GET",
                        success: function(data) {
                            console.log(data)
                            if (data.success) {
                                var payload = data.payload;
                                for (var i = 0; i < payload.length; i++) {
                                    $('#users_id').append('<option value="' + payload[i].id +
                                        '">' + payload[i].name + ' - ' + payload[i]
                                        .email + '</option>');
                                }
                            }
                        }
                    });
                } else if ($(this).val() === 'yes') {
                    $('#users_id').empty();
                    $('#users_id').select2('destroy');
                    $('#users_id').hide();
                } else {
                    $('#users_id').select2('destroy');
                    $('#users_id').empty();
                    $('#users_id').hide();

                }
            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function($) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#addNewCategory').click(function() {
                $('#addEditCategoryForm').trigger("reset");
                $('#ajaxCategoryModel').html("Add Category");
                $('#category-model').modal('show');
            });

            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');

                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('marketing-ads/edit') }}",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').html("Edit Category");
                        $('#category-model').modal('show');
                        $('#id').val(res.id);
                        $('#image').val(res.image);
                        $('#type').val(res.type);
                        $('#location').val(res.location);
                    }
                });
            });
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
                            url: "{{ url('admin/notification/delete') }}",
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
                                    }),
                                    window.location.reload();
                            }
                        });
                    }
                });


            });
            $(document).on('click', '#btn-save', function(event) {
                var title = $("input[name='title']").val();
                var message = $("input[name='message']").val();
                var category = $("select[name='category']").val();
                var location = $("select[name='location']").val();
                var type = $("select[name='type']").val();
                var all_users = $("select[name='all_users']").val();

                if (!title || !message || location == "" || type == "" || all_users == "" || category ==
                    "") {
                    alert("Semua field harus diisi!");
                    return false;
                }
                var form = $('#addEditCategoryForm')[0];
                var data = new FormData(form);
                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);
                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/notification/add') }}",
                    data: data,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);
                        Swal.fire({
                                title: "Success",
                                icon: "success",
                                showConfirmButton: false,
                                position: 'center',
                                timer: 1500
                            }),
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
