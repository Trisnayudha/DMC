@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Events Schedule Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Events Schedule Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Events Schedule</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Events Schedule Management</h4>
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
                                                Tambah Schedule</a>

                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">Sort</th>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Slug</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $post->sort }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->date }}</td>
                                                    <td>{{ $post->slug }}</td>
                                                    <td>
                                                        <form action="{{ route('events.moveSort') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="id"
                                                                value="{{ $post->id }}">

                                                            <div class="m-2">
                                                                <button type="submit" name="new_sort"
                                                                    value="{{ $post->sort - 1 }}" class="btn btn-primary">
                                                                    <span class="fa fa-arrow-up"></span>
                                                                </button>
                                                                <button type="submit" name="new_sort"
                                                                    value="{{ $post->sort + 1 }}" class="btn btn-primary">
                                                                    <span class="fa fa-arrow-down"></span>
                                                                </button>
                                                            </div>
                                                        </form>
                                                        <div class="m-2">
                                                            <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                                class="btn btn-success edit"><span
                                                                    class="fa fa-edit"></span></a>
                                                            <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                                class="btn btn-danger delete"><span
                                                                    class=" fa fa-trash"></a>
                                                        </div>
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
                    <form action="{{ url('admin/events-schedule/addcategory') }}" id="addEditCategoryForm"
                        name="addEditCategoryForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <div class="form-group{{ $errors->has('events_id') ? ' has-error' : '' }}">
                            {!! Form::label('Events') !!}
                            {!! Form::select('events_id', ['' => 'Choose an Event'] + $events->pluck('name', 'id')->toArray(), null, [
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
                            <input type="text" name="name" id="name" placeholder="Name Schedule"
                                class="form-control">
                        </div>
                        <div class="form-group">
                            <input type="text" name="location" id="location" placeholder="Location"
                                class="form-control">
                        </div>
                        <div class="form-group">
                            <div class="input-group date">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                                {!! Form::text('date', date('Y-m-d'), [
                                    'class' => 'form-control datepicker',
                                    'placeholder' => 'date',
                                    'id' => 'date',
                                ]) !!}
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                            {!! Form::label('Event Type') !!}
                            {!! Form::select('type', ['DMC' => 'Djakarta Mining Club', 'Partnership' => 'Partnership'], null, [
                                'class' => 'form-control',
                                'id' => 'type',
                            ]) !!}
                            @if ($errors->has('type'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('type') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">Save
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
                    url: "{{ url('admin/events-schedule/editcategory') }}",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        console.log(res)
                        $('#ajaxCategoryModel').html("Edit Category");
                        $('#category-model').modal('show');
                        $('#id').val(res.id);
                        $('#name').val(res.name);
                        $('#location').val(res.location);
                        $('#events_id').val(res.events_id);

                        // Ubah format tanggal di sisi klien
                        var formattedDate = new Date(res.date);
                        formattedDate = formattedDate.toISOString().split('T')[0];
                        $('#date').val(formattedDate);

                        $('#type').val(res.type);
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
                            url: "{{ url('admin/events-schedule/deletecategory') }}",
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
                                    console.log(res)
                                window.location.reload();
                            }
                        });
                    }
                });


            });
            $(document).on('click', '#btn-save', function(event) {
                var id = $("#id").val();
                var events_id = $("#events_id").val();
                var image = $("#image").val();
                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);
                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/events-highlight/addcategory') }}",
                    data: {
                        id: id,
                        events_id: events_id,
                        image: image
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
                            // window.location.reload();
                            console.log(res)
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
