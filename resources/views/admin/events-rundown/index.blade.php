@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Events Rundown Management</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Events Rundown Management</div>
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

                                <div class="mb-3 text-end">
                                    <a href="javascript:void(0)" class="btn btn-success" id="addNewCategory">
                                        <i class="fas fa-plus-circle me-1"></i> Tambah Rundown
                                    </a>
                                </div>

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
                                            @php $no = 1; @endphp
                                            @foreach ($list as $post)
                                                <tr id="row_{{ $post->id }}">
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->name }}</td>
                                                    <td>{{ \Illuminate\Support\Carbon::parse($post->date)->format('Y-m-d H:i') }}
                                                    </td>
                                                    <td>{{ $post->event_name }}</td>
                                                    <td>
                                                        @if (isset($post->speakers) && $post->speakers->count())
                                                            {{ $post->speakers->pluck('name')->implode(', ') }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-success btn-sm edit">
                                                            <span class="fa fa-edit"></span>
                                                        </a>
                                                        <a href="javascript:void(0)" data-id="{{ $post->id }}"
                                                            class="btn btn-danger btn-sm delete">
                                                            <span class="fa fa-trash"></span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div><!-- /.table-responsive -->
                            </div><!-- /.card-body -->
                        </div><!-- /.card -->
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal Add/Edit --}}
    <div class="modal fade" id="category-model" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ajaxCategoryModel"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="addEditCategoryForm" name="addEditCategoryForm"
                        class="form-horizontal" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="id">

                        <div class="form-group mb-3">
                            <label for="name" class="mb-1">Name</label>
                            <input type="text" name="name" id="name" class="form-control" autocomplete="off">
                        </div>

                        <div class="form-group mb-3">
                            <label for="date" class="mb-1">Date & Time</label>
                            <input type="datetime-local" class="form-control" name="date" id="date">
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-1">Speakers</label>
                            <select class="form-control select2" multiple style="width: 100%;" name="speakers[]"
                                id="speakers_id">
                                @foreach ($speakers as $key)
                                    <option value="{{ $key->id }}" data-image="{{ asset($key->image) }}">
                                        {{ $key->name . ' - ' . $key->job_title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Pilih lebih dari satu jika diperlukan.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="events_id" class="mb-1">Event</label>
                            <select name="events_id" id="events_id" class="form-control">
                                @foreach ($event as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="btn-save" value="addNewCategory">
                                Save changes
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>

                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        $(document).ready(function() {
            // DataTables
            $('#laravel_crud').DataTable();

            // Select2 with avatar
            $('.select2').select2({
                width: '100%',
                templateResult: function(option) {
                    if (!option.id) return option.text;
                    var imageUrl = $(option.element).data('image');
                    if (!imageUrl) return option.text;
                    return $(
                        '<span style="display:flex;align-items:center;gap:8px;">' +
                        '<img src="' + imageUrl +
                        '" style="width:24px;height:24px;border-radius:50%;object-fit:cover;" />' +
                        '<span>' + option.text + '</span>' +
                        '</span>'
                    );
                },
                templateSelection: function(option) {
                    return option.text;
                }
            });

            // CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Add new
            $('#addNewCategory').on('click', function() {
                $('#addEditCategoryForm').trigger('reset');
                $('#speakers_id').val(null).trigger('change');
                $('#ajaxCategoryModel').text('Add Rundown');
                $('#id').val('');
                $('#btn-save').val('create');
                $('#category-model').modal('show');
            });

            // Edit
            $(document).on('click', '.edit', function() {
                var id = $(this).data('id');
                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/events/rundown') }}/" + id + "/edit",
                    dataType: 'json',
                    success: function(res) {
                        $('#ajaxCategoryModel').text('Edit Rundown');
                        $('#category-model').modal('show');

                        // Controller ideal response: {id,name,date,events_id,speakers_ids:[...]}
                        var speakersIds = res.speakers_ids ?
                            res.speakers_ids :
                            (res.speakers ? res.speakers.map(function(s) {
                                return s.id;
                            }) : []);

                        $('#id').val(res.id || res.id_rundown || id);
                        $('#name').val(res.name || '');
                        $('#date').val(res.date || ''); // must be Y-m-dTH:i
                        $('#events_id').val(res.events_id || '').trigger('change');
                        $('#speakers_id').val(speakersIds).trigger('change');

                        $('#btn-save').val('update');
                    },
                    error: function() {
                        Swal.fire('Error', 'Data tidak ditemukan', 'error');
                    }
                });
            });

            // Delete
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
                            dataType: 'json',
                            success: function() {
                                Swal.fire({
                                    title: "Success",
                                    icon: "success",
                                    showConfirmButton: false,
                                    position: 'center',
                                    timer: 1200
                                }).then(function() {
                                    window.location.reload();
                                });
                            }
                        });
                    }
                });
            });

            // Save (create/update) — upsert via POST store()
            $(document).on('click', '#btn-save', function() {
                var formData = new FormData();
                formData.append('id', $('#id').val());
                formData.append('name', $('#name').val());
                formData.append('date', $('#date').val());
                formData.append('events_id', $('#events_id').val());

                var selectedSpeakers = $('#speakers_id').val() || [];
                selectedSpeakers.forEach(function(v) {
                    formData.append('speakers[]', v);
                });

                $("#btn-save").html('Please Wait...').prop("disabled", true);

                $.ajax({
                    type: "POST",
                    url: "{{ url('admin/events/rundown') }}",
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function() {
                        Swal.fire({
                            title: "Success",
                            icon: "success",
                            showConfirmButton: false,
                            position: 'center',
                            timer: 1200
                        });
                        window.location.reload();
                    },
                    error: function(xhr) {
                        let msg = 'Gagal menyimpan';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                            .message;
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function() {
                        $("#btn-save").html('Save changes').prop("disabled", false);
                    }
                });
            });
        });
    </script>
@endsection
