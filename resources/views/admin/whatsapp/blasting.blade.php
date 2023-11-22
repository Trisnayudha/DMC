@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Whatsapp Blasting Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Whatsapp Blasting Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Whatsapp Blasting</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Whatsapp Blasting Management</h4>
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
                                                Tambah Blasting</a>

                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Camp name</th>
                                                <th>No Hp</th>
                                                <th>Status</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->camp_id }}</td>
                                                    <td>{{ $post->wa_temp_id }}</td>
                                                    <td>{{ $post->status }}</td>
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
                            <label for="name">Campaign</label>
                            <select name="camp_id" id="camp_id" class="form-control">
                                <option value="">-- Please Select --</option>
                                @foreach ($camp as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Template</label>
                            <select name="temp_id" id="temp_id" class="form-control">
                                <option value="">-- Please Select --</option>
                                @foreach ($temp as $item)
                                    <option value="{{ $item->id }}">{{ $item->text }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Sender</label>
                            <select name="send_id" id="send_id" class="form-control">
                                <option value="">-- Please Select --</option>
                                @foreach ($send as $item)
                                    <option value="{{ $item->id }}">{{ $item->phone }}</option>
                                @endforeach
                            </select>
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
            // Function to truncate text to a specified number of words
            function truncateText(text, maxWords) {
                var words = text.split(' ');
                if (words.length > maxWords) {
                    return words.slice(0, maxWords).join(' ') + '...';
                } else {
                    return text;
                }
            }

            // Example usage
            $('#camp_id option').each(function() {
                var originalText = $(this).text();
                var truncatedText = truncateText(originalText, 20);
                $(this).text(truncatedText);
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
                    type: "GET",
                    url: "{{ url('admin/whatsapp/campaign') }}/" + id + "/edit",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').html("Edit Category");
                        $('#category-model').modal('show');
                        $('#id').val(res.id);
                        $('#name').val(res.name);
                        $('#date').val(res.date);
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
                            url: "{{ url('admin/whatsapp/campaign') }}/" + id,
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
                var camp_id = $("#camp_id").val();
                var temp_id = $("#temp_id").val();
                var send_id = $("#send_id").val();
                console.log(camp_id)
                console.log(temp_id)
                console.log(send_id)
                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);

                // ajax
                $.ajax({
                        type: "POST",
                        url: "{{ url('admin/whatsapp/blasting') }}",
                        data: {
                            id: id,
                            camp_id: camp_id,
                            temp_id: temp_id,
                            send_id: send_id,
                        },
                        dataType: 'json'
                    })
                    .done(function(res) {
                        Swal.fire({
                            title: "Success",
                            icon: "success",
                            showConfirmButton: false,
                            position: 'center',
                            timer: 1500
                        });
                        window.location.reload();
                    })
                    .fail(function(error) {
                        console.error("Error:", error);
                        Swal.fire({
                            title: "Error",
                            text: "There was an error processing your request.",
                            icon: "error",
                            position: 'center',
                        });
                    })
                    .always(function() {
                        $("#btn-save").html('Submit');
                        $("#btn-save").attr("disabled", false);
                    });
            });

        });
        $(document).ready(function() {
            $('#laravel_crud').DataTable();
        });
    </script>
@endsection
