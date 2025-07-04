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

                                <div class="float-right mb-2">

                                    <a href="javascript:void(0)"
                                        class="btn btn-block btn-icon icon-left btn-success btn-filter" id="addNewCategory">
                                        <i class="fas fa-plus-circle"></i>
                                        Add Sponsor Photos & Videos</a>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Type</th>
                                                <th>Path</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($data as $post)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->type }}</td>
                                                    <td>{{ $post->path }}</td>

                                                    <td>
                                                        <button class="btn btn-success btn-edit"
                                                            data-id="{{ $post->id }}" title="Edit Data">
                                                            <span class="fa fa-edit"></span>
                                                        </button>
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
    <div class="modal fade" id="category-model" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ajaxCategoryModel"></h4>
                </div>
                <div class="modal-body">
                    <form action="{{ route('photos-videos-activity.store') }}" id="addEditCategoryForm"
                        name="addEditCategoryForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="sponsor_id" id="sponsor_id" value="{{ $sponsor_id }}">
                        <div class="form-group">
                            <label for="file">File</label>
                            <input type="file" name="file" id="file" class="form-control"
                                accept="image/*,video/*">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="btn-save" value="addNewCategory">Save
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">

                </div>
            </div>
        </div>
    </div>
@endsection

@push('bottom')
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

            // Edit sponsor
            $(document).on('click', '.btn-edit', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $.ajax({
                    type: "GET",
                    url: "/admin/photos-videos-activity/" + id + "/edit",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').html("Edit Sponsor");
                        $('#category-model').modal('show');
                        $('#addEditCategoryForm').attr('action',
                            '/admin/photos-videos-activity/' + id);
                        // Remove previous _method if exists
                        $('#addEditCategoryForm input[name="_method"]').remove();
                        $('#addEditCategoryForm').append(
                            '<input type="hidden" name="_method" value="PUT">');
                        $('#file').val('');
                        // You can prefill other fields if needed from res
                    }
                });
            });

            // Delete sponsor
            $(document).on('click', '.delete-sponsor', function() {
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
                            url: "/admin/photos-videos-activity/" + id,
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: res.message,
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        });
                    }
                });
            });

            // Submit form for create/update
            $(document).on('submit', '#addEditCategoryForm', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var actionUrl = $(this).attr('action');
                $.ajax({
                    type: "POST",
                    url: actionUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({
                            title: "Success",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            });
        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
    </script>
@endpush
