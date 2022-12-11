@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Events Ticket Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Events Ticket Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Events Ticket</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Events Ticket Management</h4>
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
                                                <th>Nama Ticket</th>
                                                <th>Price Rupiah</th>
                                                <th>Price USD</th>
                                                <th>Status Ticket</th>
                                                <th>Status Sold</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->title }}</td>
                                                    <td>{{ $post->price_rupiah }}</td>
                                                    <td>{{ $post->price_dollar }}</td>
                                                    <td>{{ $post->status_ticket }}</td>
                                                    <td>{{ $post->status_sold }}</td>
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
                        <div class="form-group{{ $errors->has('events_id') ? ' has-error' : '' }}">
                            {!! Form::label('Events') !!}
                            {!! Form::select('events_id', $events->pluck('name', 'id'), null, [
                                'class' => 'form-control select2',
                                'id' => 'events_id',
                            ]) !!}
                            @if ($errors->has('events_id'))
                                <span class="help-block">
                                    <strong style="color:red">{{ $errors->first('events_id') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-4">Title Ticket</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="title" name="title"
                                    placeholder="Judul Ticket">
                                <span id="linkError" class="alert-message"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">Price Rupiah</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="price_rupiah" name="price_rupiah"
                                    placeholder="Rp">
                                <span id="linkError" class="alert-message"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">Price Dollar</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="price_dollar" name="price_dollar"
                                    placeholder="$">
                                <span id="linkError" class="alert-message"></span>
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('status_ticket') ? ' has-error' : '' }}">
                            {!! Form::label('Status Ticket') !!}
                            {!! Form::select('status_ticket', ['on' => 'Available', 'off' => 'Not Available'], null, [
                                'class' => 'form-control',
                                'id' => 'status_ticket',
                            ]) !!}
                            @if ($errors->has('status_ticket'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('status_ticket') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="form-group{{ $errors->has('status_sold') ? ' has-error' : '' }}">
                            {!! Form::label('Status Sold') !!}
                            {!! Form::select('status_sold', ['on' => 'Available', 'off' => 'Not Available'], null, [
                                'class' => 'form-control',
                                'id' => 'status_sold',
                            ]) !!}
                            @if ($errors->has('status_sold'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('status_sold') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                            {!! Form::label('Deskripsi *') !!}
                            {!! Form::textarea('description', old('description'), [
                                'id' => 'description',
                                'class' => 'form-control my-editor',
                                'placeholder' => 'Berita',
                            ]) !!}
                            @if ($errors->has('description'))
                                <span class="help-block">
                                    <strong style="color:red">{{ $errors->first('description') }}</strong>
                                </span>
                            @endif
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

            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');

                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('videos/editcategory') }}",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').html("Edit Category");
                        $('#category-model').modal('show');
                        $('#id').val(res.id);
                        $('#link').val(res.link);
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
                            url: "{{ url('videos/deletecategory') }}",
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
                var id = $("#id").val();
                var events_id = $("#events_id").val();
                var title = $("#title").val();
                var price_rupiah = $("#price_rupiah").val();
                var price_dollar = $("#price_dollar").val();
                var type = $("#type").val();
                var description = $("#description").val();
                var status_ticket = $("#status_ticket").val();
                var status_sold = $("#status_sold").val();
                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);
                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('events-tickets/addcategory') }}",
                    data: {
                        id: id,
                        events_id: events_id,
                        title: title,
                        price_rupiah: price_rupiah,
                        price_dollar: price_dollar,
                        description: description,
                        status_ticket: status_ticket,
                        status_sold: status_sold,
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
