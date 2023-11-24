@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Whatsapp DB Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Whatsapp DB Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Whatsapp DB</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Whatsapp DB Management</h4>
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

                                <div class="form-row">
                                    <div class="form-group col-md-8">
                                        <form action="{{ route('db.index') }}" method="GET" id="filterForm">
                                            <select name="filter" id="filter" class="form-control">
                                                <option value="">-- Select Filter Database --</option>
                                                @foreach ($camp as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <a href="{{ asset('excel/import-template-wa.xlsx') }}"
                                            class="btn btn-block btn-icon icon-left btn-info btn-filter" download>
                                            <i class="fa fa-download" aria-hidden="true"></i>
                                            Template
                                        </a>
                                    </div>
                                    <div class="form-group col-md-2">

                                        <a href="javascript:void(0)"
                                            class="btn btn-block btn-icon icon-left btn-success btn-filter"
                                            id="addNewCategory">
                                            <i class="fas fa-plus-circle"></i>
                                            Tambah Ticket</a>

                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Name</th>
                                                <th>Nomor HP</th>
                                                <th>Company</th>
                                                <th>Job</th>
                                                <th>Campaign</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->phone }}</td>
                                                    <td>{{ $post->company_name }}</td>
                                                    <td>{{ $post->job_title }}</td>
                                                    <td>{{ $post->camp_name }}</td>
                                                    <td>
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
                            <label for="name">Campaign</label>
                            <select name="camp_id" id="camp_id" class="form-control">
                                <option value="">-- Please Select --</option>
                                @foreach ($camp as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="image">Excel File</label>
                            <input type="file" class="form-control" accept=".xlsx, .xls" name="file" id="file">
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
                            url: "{{ url('admin/whatsapp/db') }}/" + id,
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
                var campId = $("#camp_id").val();

                var formData = new FormData();
                formData.append('id', id);
                formData.append('camp_id', campId);

                var fileInput = $("#file")[0];
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('file', fileInput.files[0]);
                }

                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);

                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/whatsapp/db') }}",
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
                        Swal.fire({
                            title: "Error",
                            text: "An error occurred. Please try again.",
                            icon: "error",
                            showConfirmButton: true,
                        });

                        $("#btn-save").html('Save changes');
                        $("#btn-save").attr("disabled", false);
                    }
                });
            });


        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
        document.getElementById('filter').addEventListener('change', function() {
            // Trigger form submission when the select value changes
            document.getElementById('filterForm').submit();
        });
    </script>
@endsection
