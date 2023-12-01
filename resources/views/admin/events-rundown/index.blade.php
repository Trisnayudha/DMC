@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Events Rundown Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Events Rundown Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Events Rundown</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Events Rundown Management</h4>
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
                                                Tambah Rundown</a>

                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Events</th>
                                                <th>Speakers</th>

                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ $post->date }}</td>
                                                    <td>{{ $post->event_name }}</td>
                                                    <td> </td>
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
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Date Times</label>
                            <input type="text" class="form-control datetimepicker" value="" name="date"
                                id="date">
                        </div>
                        <div class="form-group">
                            <label>Speakers</label>
                            <select class="form-control select2" multiple="multiple" style="width: 100%;" name="speakers_id"
                                id="speakers_id">
                                @foreach ($speakers as $key)
                                    <option value="{{ $key->id }}" data-image="{{ asset($key->image) }}">
                                        {{ $key->name . ' - ' . $key->job_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="events_id"> Event </label>
                            <select name="events_id" id="events_id" class="form-control">
                                @foreach ($event as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
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
                    url: "{{ url('admin/events/rundown') }}/" + id + "/edit",
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

                        // Populate speakers in the select2 dropdown
                        var selectedSpeakers = res.speakers.map(speaker => speaker.id);
                        $('#speakers_id').val(selectedSpeakers).trigger('change');

                        // Populate the events dropdown
                        $('#events_id').val(res.events_id);

                        // Change the button value to indicate edit
                        $('#btn-save').val('updateCategory');
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
                            url: "{{ url('admin/events/rundown') }}/" + id,
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
                var name = $("#name").val();
                var date = $("#date").val();

                // Extract selected values from the Speakers dropdown
                var selectedSpeakers = $('#speakers_id').val() ||
            []; // Corrected the ID for the speakers dropdown

                // Extract the selected value from the Event dropdown
                var selectedEvent = $('#events_id').val();

                var formData = new FormData();
                formData.append('id', id);
                formData.append('name', name);
                formData.append('date', date);

                // Append selected speakers as an array
                for (var i = 0; i < selectedSpeakers.length; i++) {
                    formData.append('speakers[]', selectedSpeakers[i]);
                }

                formData.append('events_id', selectedEvent);

                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);

                // ajax
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/events/rundown') }}",
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
                        // console.log(res)
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

        $(document).ready(function() {
            $('.select2').select2({
                templateResult: formatOption // Custom function to format the dropdown options
            });

            function formatOption(option) {
                if (!option.id) {
                    return option.text;
                }

                var imageUrl = $(option.element).data('image');

                if (!imageUrl) {
                    return option.text;
                }

                var $option = $(
                    '<span><img src="' + asset(imageUrl) + '" class="img-flag" /> ' + option.text + '</span>'
                );

                return $option;
            }
        });
    </script>
@endsection
