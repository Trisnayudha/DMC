@extends('layouts.inspire.master')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Program Article Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><a href="">Program Article</a></div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">Program / Article</h2>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">

                            <div class="card-header">
                                <h4>Manage Program</h4>
                                <div class="card-header-action">
                                    <a href="javascript:void(0)" class="btn btn-icon icon-left btn-success" id="btnAdd">
                                        <i class="fas fa-plus-circle"></i> Add Program
                                    </a>
                                </div>
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

                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10px">No</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th width="20%">Cover</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no=1; @endphp
                                            @foreach ($list as $row)
                                                <tr id="row_{{ $row->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>
                                                        <div style="font-weight:600">{{ $row->title }}</div>
                                                        <small class="text-muted">Slug: {{ $row->slug }}</small>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ $row->status == 'published' ? 'success' : ($row->status == 'draft' ? 'warning' : 'secondary') }}">
                                                            {{ strtoupper($row->status) }}
                                                        </span>
                                                        <div>
                                                            <small class="text-muted">
                                                                Publish:
                                                                {{ $row->published_at ? $row->published_at->format('d M Y H:i') : '-' }}
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if ($row->cover_image)
                                                            <img src="{{ asset($row->cover_image) }}" width="120"
                                                                style="border-radius:8px;">
                                                        @else
                                                            <span class="text-muted">No cover</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" data-id="{{ $row->id }}"
                                                            class="btn btn-primary btn-sm btnManage">
                                                            <i class="fas fa-images"></i>
                                                        </a>
                                                        <a href="javascript:void(0)" data-id="{{ $row->id }}"
                                                            class="btn btn-success btn-sm btnEdit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0)" data-id="{{ $row->id }}"
                                                            class="btn btn-danger btn-sm btnDelete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
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

    {{-- MODAL ADD/EDIT PROGRAM --}}
    <div class="modal fade" id="program-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="programModalTitle">Add Program</h4>
                </div>
                <div class="modal-body">
                    <form action="{{ url('admin/program/store') }}" method="POST" enctype="multipart/form-data"
                        id="programForm">
                        @csrf
                        <input type="hidden" name="id" id="program_id">

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Excerpt</label>
                            <textarea name="excerpt" id="excerpt" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Content</label>
                            <textarea name="content" id="content" class="form-control my-editor"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label>Published At</label>
                                <input type="datetime-local" name="published_at" id="published_at" class="form-control">
                                <small class="text-muted">Kosongkan kalau draft.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Cover Image (optional)</label>
                            <input type="file" name="cover_image" class="form-control">
                            <div class="mt-2" id="coverPreview"></div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL MANAGE MEDIA --}}
    <div class="modal fade" id="media-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Manage Media</h4>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="media_program_id">

                    <div class="alert alert-light">
                        <b>Gallery Images</b> (multiple) + <b>Video</b> (max 1 - URL)
                    </div>

                    {{-- Upload images --}}
                    <form action="{{ url('admin/program/upload-images') }}" method="POST" enctype="multipart/form-data"
                        id="uploadImagesForm">
                        @csrf
                        <input type="hidden" name="program_id" id="upload_program_id">
                        <div class="form-group">
                            <label>Upload Images</label>
                            <input type="file" name="images[]" multiple class="form-control">
                        </div>
                        <button class="btn btn-success btn-sm" type="submit"><i class="fas fa-upload"></i>
                            Upload</button>
                    </form>

                    <hr>

                    {{-- Video url --}}
                    <form action="{{ url('admin/program/video') }}" method="POST" id="videoForm">
                        @csrf
                        <input type="hidden" name="program_id" id="video_program_id">
                        <div class="form-group">
                            <label>Video URL (YouTube/Vimeo)</label>
                            <input type="text" name="video_url" id="video_url" class="form-control"
                                placeholder="https://www.youtube.com/watch?v=...">
                            <small class="text-muted">Kosongkan lalu Save untuk remove video.</small>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-save"></i> Save
                            Video</button>
                    </form>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="10%">Sort</th>
                                    <th>Preview</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="mediaTableBody">
                                {{-- filled by ajax --}}
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- scripts --}}
    <script>
        $(function() {
            // summernote
            $('.my-editor').summernote({
                dialogsInBody: true,
                minHeight: 180,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear', 'link', 'picture', 'video',
                        'undo'
                    ]],
                    ['para', ['ul', 'ol', 'paragraph']]
                ]
            });

            // datatable
            $('#laravel_crud').DataTable();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // add
            $('#btnAdd').click(function() {
                $('#programForm')[0].reset();
                $('#program_id').val('');
                $('#content').summernote('code', '');
                $('#coverPreview').html('');
                $('#programModalTitle').text('Add Program');
                $('#program-modal').modal('show');
            });

            // edit
            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                $.post("{{ url('admin/program/edit') }}", {
                    id: id
                }, function(res) {
                    $('#programModalTitle').text('Edit Program');
                    $('#program_id').val(res.id);
                    $('#title').val(res.title);
                    $('#excerpt').val(res.excerpt);
                    $('#content').summernote('code', res.content ?? '');
                    $('#status').val(res.status);
                    $('#published_at').val(res.published_at ?? '');
                    if (res.cover_image) {
                        $('#coverPreview').html('<img src="' + res.cover_image +
                            '" width="160" style="border-radius:8px;">');
                    } else {
                        $('#coverPreview').html('');
                    }
                    $('#program-modal').modal('show');
                }, 'json');
            });

            // delete
            $(document).on('click', '.btnDelete', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: "Anda Yakin ?",
                    text: "Ingin Menghapus Program ini.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ url('admin/program/delete') }}", {
                            id: id
                        }, function(res) {
                            $('#row_' + res.id).remove();
                            Swal.fire({
                                title: "Success",
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1200
                            });
                        }, 'json');
                    }
                });
            });

            // manage media
            $(document).on('click', '.btnManage', function() {
                let id = $(this).data('id');
                loadMedia(id);
                $('#media-modal').modal('show');
            });

            function loadMedia(id) {
                // reuse endpoint edit untuk ambil media
                $.post("{{ url('admin/program/edit') }}", {
                    id: id
                }, function(res) {
                    $('#media_program_id').val(res.id);
                    $('#upload_program_id').val(res.id);
                    $('#video_program_id').val(res.id);
                    $('#video_url').val(res.video ? (res.video.video_url ?? '') : '');

                    let html = '';
                    if (res.images && res.images.length) {
                        res.images.forEach(function(img) {
                            html += `
                        <tr id="media_row_${img.id}">
                            <td style="width:120px;">
                                <input type="number" class="form-control form-control-sm media-sort" data-id="${img.id}" value="${img.sort}">
                            </td>
                            <td>
                                <img src="${img.file_path}" width="160" style="border-radius:8px;">
                            </td>
                            <td>
                                <a href="javascript:void(0)" class="btn btn-danger btn-sm mediaDelete" data-id="${img.id}">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                        });
                    } else {
                        html = `<tr><td colspan="3" class="text-center text-muted">No images yet</td></tr>`;
                    }

                    $('#mediaTableBody').html(html);
                }, 'json');
            }

            // update sort
            $(document).on('change', '.media-sort', function() {
                let id = $(this).data('id');
                let sort = $(this).val();

                $.post("{{ url('admin/program/media/update-sort') }}", {
                    id: id,
                    sort: sort
                }, function() {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1200,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Sort updated'
                    });
                }, 'json');
            });

            // delete media
            $(document).on('click', '.mediaDelete', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: "Delete media?",
                    text: "Image will be removed.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                }).then((r) => {
                    if (r.isConfirmed) {
                        $.post("{{ url('admin/program/media/delete') }}", {
                            id: id
                        }, function(res) {
                            $('#media_row_' + res.id).remove();
                            Swal.fire({
                                title: "Deleted",
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1000
                            });
                        }, 'json');
                    }
                });
            });
        });
    </script>
@endsection
