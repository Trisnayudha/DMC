@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Whatsapp Template Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Whatsapp Template Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Whatsapp Template</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Whatsapp Template Management</h4>
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
                                                Tambah Ticket</a>

                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Text</th>
                                                <th>Image</th>

                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->text }}</td>
                                                    <td>{{ $post->image }}</td>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ajaxCategoryModel"></h4>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="addEditCategoryForm" name="addEditCategoryForm"
                        class="form-horizontal" method="POST">
                        <input type="hidden" name="id" id="id">
                        <div class="form-group">
                            <label for="name">Message</label>
                            <textarea class="form-control" id="text" name="text" rows="3" placeholder=""></textarea>
                            <span id="linkError" class="alert-message"></span>
                        </div>

                        <div class="form-group">
                            <label for="">Image</label>
                            <input type="file" class="form-control" accept="image/*" name="image" id="image">
                        </div>

                        <div class="form-group">
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
    <script>
        $(document).ready(function() {
            $('#text').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Insert "\n" at the current cursor position
                    insertTextAtCursor($(this)[0], '\n');
                }
            });

            // Function to insert text at the current cursor position
            function insertTextAtCursor(textarea, text) {
                var startPos = textarea.selectionStart;
                var endPos = textarea.selectionEnd;

                // Insert the text
                textarea.value = textarea.value.substring(0, startPos) + text + textarea.value.substring(endPos,
                    textarea.value.length);

                // Move the cursor to the end of the inserted text
                textarea.selectionStart = textarea.selectionEnd = startPos + text.length;
            }
        });

        $(function() {
            $('.my-editor').summernote({
                dialogsInBody: true,
                minHeight: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear', 'link', 'picture', 'video',
                        'undo'
                    ]],
                    ['font', ['strikethrough']],
                    ['para', ['paragraph']]
                ]
            })
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
                    type: "GET",
                    url: "{{ url('admin/whatsapp/template') }}/" + id + "/edit",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').html("Edit Category");
                        $('#category-model').modal('show');
                        $('#id').val(res.id);
                        $('#text').val(res.text);
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
                            type: "DELETE",
                            url: "{{ url('admin/whatsapp/template') }}/" + id,
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
                                }).then(function() {
                                    // Code to execute after Swal is closed
                                    window.location.reload();
                                    console.log(res);
                                });
                            }
                        });

                    }
                });


            });
            $(document).on('click', '#btn-save', function(event) {
                var id = $("#id").val();
                var text = $("#text").val();
                var formData = new FormData();
                formData.append('id', id);
                formData.append('text', text);

                // Check if the element with id 'image' exists before accessing its files property
                var imageInput = $("#image")[0];
                if (imageInput && imageInput.files.length > 0) {
                    formData.append('image', imageInput.files[0]);
                }

                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);

                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/whatsapp/template') }}",
                    data: formData,
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
                        $("#btn-save").html('Save changes');
                        $("#btn-save").attr("disabled", false);
                    },
                    error: function(error) {
                        console.error("Error:", error);
                        // Handle error here
                        $("#btn-save").html('Save changes');
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
