@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Videos Highlight Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Videos Highlight Management</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Videos Highlight</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Videos Highlight Management</h4>
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
                                                Tambah Videos</a>

                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Link</th>
                                                <th>Event</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->link }}</td>
                                                    <td>
                                                        @if ($post->events_id)
                                                            {{ $events->firstWhere('id', $post->events_id)->name ?? '-' }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>

                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-success edit"><span
                                                                class="fa fa-edit"></span></a>
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
                        class="form-horizontal" method="POST">
                        <input type="hidden" name="id" id="id">
                        <div class="form-group">
                            <label for="link" class="col-sm-4">Link Youtube</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="link" name="link"
                                    placeholder="https://www.youtube.com/watch?v=...">
                                <span id="linkError" class="alert-message"></span>
                            </div>
                        </div>

                        <div class="form-group" id="youtube-preview-wrapper" style="display:none;">
                            <label class="col-sm-4">Preview</label>
                            <div class="col-sm-12">
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe id="youtube-preview" class="embed-responsive-item" src=""
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="events_id" class="col-sm-4">Event (opsional)</label>
                            <div class="col-sm-12">
                                <select class="form-control" id="events_id" name="events_id">
                                    <option value="">-- Tidak terhubung ke event --</option>
                                    @foreach ($events as $event)
                                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            </div>
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
    <script type="text/javascript">
        function getYoutubeEmbedUrl(url) {
            if (!url) return null;
            var regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i;
            var match = url.match(regExp);
            if (match && match[1]) {
                return 'https://www.youtube.com/embed/' + match[1];
            }
            return null;
        }

        function updateYoutubePreview(url) {
            var embedUrl = getYoutubeEmbedUrl(url);
            if (embedUrl) {
                $('#youtube-preview').attr('src', embedUrl);
                $('#youtube-preview-wrapper').show();
            } else {
                $('#youtube-preview').attr('src', '');
                $('#youtube-preview-wrapper').hide();
            }
        }

        $(document).ready(function($) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#addNewCategory').click(function() {
                $('#addEditCategoryForm').trigger("reset");
                $('#ajaxCategoryModel').html("Add Video");
                $('#youtube-preview').attr('src', '');
                $('#youtube-preview-wrapper').hide();
                $('#category-model').modal('show');
            });

            $('#link').on('input', function() {
                updateYoutubePreview($(this).val());
            });

            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');

                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/videos/editcategory') }}",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').html("Edit Video");
                        $('#category-model').modal('show');
                        $('#id').val(res.id);
                        $('#link').val(res.link);
                        $('#events_id').val(res.events_id || '');
                        updateYoutubePreview(res.link);
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
                            url: "{{ url('admin/videos/deletecategory') }}",
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
                var link = $("#link").val();
                var events_id = $("#events_id").val();
                $("#btn-save").html('Please Wait...');
                $("#btn-save").attr("disabled", true);
                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/videos/addcategory') }}",
                    data: {
                        id: id,
                        link: link,
                        events_id: events_id,
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
