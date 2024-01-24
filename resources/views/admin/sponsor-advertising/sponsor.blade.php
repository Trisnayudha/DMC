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
                                        Add Sponsor Address</a>
                                </div>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Name File</th>
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
    <div class="modal fade" id="category-model" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ajaxCategoryModel"></h4>
                </div>
                <div class="modal-body">
                    <form action="{{ route('sponsors-advertising.store') }}" id="addEditCategoryForm"
                        name="addEditCategoryForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="sponsor_id" id="sponsor_id" value="{{ $sponsor_id }}">
                        <div class="form-group">
                            <label for="">Name</label>
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">File</label>
                            <input type="file" name="file" id="file" class="form-control">
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

            // $(document).on('click', '.edit', function() {
            //     var id = $(this).data('id');

            //     // ajax
            //     $.ajax({
            //         type: "GET",
            //         url: "{{ url('admin/events/speakers') }}/" + id + "/edit",
            //         data: {
            //             id: id
            //         },
            //         dataType: 'json',
            //         success: function(res) {
            //             $('#ajaxCategoryModel').html("Edit Category");
            //             $('#category-model').modal('show');
            //             $('#id').val(res.id);
            //             $('#name').val(res.name);
            //             $('#company').val(res.company);
            //             $('#job_title').val(res.job_title);
            //             $('#image').val(res.image);
            //         }
            //     });
            // });
            // $(document).on('click', '.delete', function() {

            //     var id = $(this).data('id');
            //     Swal.fire({
            //         title: "Anda Yakin ?",
            //         text: "Ingin Menghapus Data ini.",
            //         icon: "warning",
            //         showCancelButton: true,
            //         confirmButtonColor: '#3085d6',
            //         cancelButtonColor: '#d33',
            //         confirmButtonText: 'Yes, delete it!'
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             $.ajax({
            //                 type: "DELETE",
            //                 url: "{{ url('admin/events/speakers') }}/" + id,
            //                 data: {
            //                     id: id
            //                 },
            //                 dataType: 'json',
            //                 success: function(res) {
            //                     Swal.fire({
            //                         title: "Success",
            //                         icon: "success",
            //                         showConfirmButton: false,
            //                         position: 'center',
            //                         timer: 1500
            //                     }).then(function() {
            //                         // Code to execute after Swal is closed
            //                         window.location.reload();
            //                         console.log(res);
            //                     });
            //                 }
            //             });

            //         }
            //     });


            // });
            // $(document).on('click', '#btn-save', function(event) {
            //     var id = $("#id").val();
            //     var name = $("#name").val();
            //     var company = $("#company").val();
            //     var job_title = $("#job_title").val();
            //     var imageInput = $("#image")[0];

            //     // Cek apakah file gambar dipilih
            //     var image = imageInput.files.length > 0 ? imageInput.files[0] : null;

            //     var formData = new FormData();
            //     formData.append('id', id);
            //     formData.append('name', name);
            //     formData.append('company', company);
            //     formData.append('job_title', job_title);
            //     // Hanya tambahkan file gambar jika dipilih
            //     if (image) {
            //         formData.append('image', image);
            //     }
            //     $("#btn-save").html('Please Wait...');
            //     $("#btn-save").attr("disabled", true);
            //     // ajax
            //     $.ajax({
            //         type: "POST",
            //         url: "{{ url('admin/events/speakers') }}",
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         success: function(res) {
            //             console.log(res)
            //             Swal.fire({
            //                     title: "Success",
            //                     icon: "success",
            //                     showConfirmButton: false,
            //                     position: 'center',
            //                     timer: 1500
            //                 }),
            //                 window.location.reload();
            //             $("#btn-save").html('Submit');
            //             $("#btn-save").attr("disabled", false);
            //         }
            //     });
            // });
        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
    </script>
@endpush
