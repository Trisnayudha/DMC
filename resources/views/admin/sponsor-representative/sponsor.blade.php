@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsor Representatives</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active"><span>Representatives Management</span></div>
                </div>
            </div>

            <div class="section-body">
                <h2 class="section-title">List of Sponsor Representatives</h2>

                <div class="row">
                    <div class="col-lg-12">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h4>Data Sponsor Representatives</h4>
                                <div class="card-header-action">
                                    <button id="addNewSponsor" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Representative
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="laravel_crud" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th>Name</th>
                                                <th>Job Title</th>
                                                <th>Email</th>
                                                <th>Instagram</th>
                                                <th>LinkedIn</th>
                                                <th width="12%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach ($data as $row)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $row->name }}</td>
                                                    <td>{{ $row->job_title }}</td>
                                                    <td>{{ $row->email }}</td>
                                                    <td>{{ $row->instagram }}</td>
                                                    <td>{{ $row->linkedin }}</td>
                                                    <td>
                                                        <button class="btn btn-success btn-sm edit-sponsor"
                                                            data-id="{{ $row->id }}">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm delete-sponsor"
                                                            data-id="{{ $row->id }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div> <!-- /.table-responsive -->
                            </div> <!-- /.card-body -->
                        </div> <!-- /.card -->
                    </div> <!-- /.col-lg-12 -->
                </div> <!-- /.row -->
            </div> <!-- /.section-body -->
        </section>
    </div>

    {{-- Modal Add/Edit Sponsor Representative --}}
    <div class="modal fade" id="modal-sponsor" tabindex="-1" role="dialog" aria-labelledby="modalSponsorTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="form-sponsor" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="id"> {{-- Hidden field untuk Edit --}}
                <input type="hidden" name="sponsor_id" id="sponsor_id" value="{{ $sponsor_id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSponsorTitle">Add Sponsor Representative</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- Name --}}
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name" id="name">
                        </div>
                        {{-- Job Title --}}
                        <div class="form-group">
                            <label for="job_title">Job Title</label>
                            <input type="text" class="form-control" name="job_title" id="job_title">
                        </div>
                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email"
                                placeholder="email@example.com">
                        </div>
                        {{-- Instagram --}}
                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" class="form-control" name="instagram" id="instagram">
                        </div>
                        {{-- LinkedIn --}}
                        <div class="form-group">
                            <label for="linkedin">LinkedIn</label>
                            <input type="text" class="form-control" name="linkedin" id="linkedin">
                        </div>
                        {{-- Image Input & Cropper --}}
                        <div class="form-group">
                            <label for="modalImageInput">Image Profile</label>
                            <input type="file" class="form-control" name="image" id="modalImageInput" accept="image/*">
                            <small class="form-text text-muted">Select an image to crop (ratio 1:1).</small>
                        </div>
                        {{-- Cropper Container --}}
                        <div class="form-group" id="modalCropContainer" style="display:none;">
                            <label>Preview & Crop</label>
                            <div>
                                <img id="modalImagePreview" style="max-width:100%;">
                            </div>
                            <button type="button" id="modalCropButton" class="btn btn-primary mt-2">Crop Image</button>
                        </div>
                        {{-- Hidden input untuk menyimpan cropped image (base64) --}}
                        <input type="hidden" name="cropped_image" id="modalCroppedImage">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="btn-save" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('bottom')
    <!-- Include CSS & JS untuk DataTables, SweetAlert, Cropper.js, dan jQuery -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#laravel_crud').DataTable();

            // Setup CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1) Show modal ADD
            $('#addNewSponsor').click(function() {
                $('#form-sponsor')[0].reset();
                $('#id').val('');
                $('#modalSponsorTitle').text('Add Sponsor Representative');
                $('#modal-sponsor').modal('show');
            });

            // 2) Show modal EDIT
            $(document).on('click', '.edit-sponsor', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '/admin/sponsors-representative/' + id + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        $('#id').val(res.id);
                        $('#name').val(res.name);
                        $('#job_title').val(res.job_title);
                        $('#email').val(res.email);
                        $('#instagram').val(res.instagram);
                        $('#linkedin').val(res.linkedin);
                        $('#modalSponsorTitle').text('Edit Sponsor Representative');
                        $('#modal-sponsor').modal('show');
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        Swal.fire('Error', 'Unable to fetch data', 'error');
                    }
                });
            });

            // 3) Submit form (Add/Update) via AJAX
            $('#form-sponsor').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var id = $('#id').val();
                var url = '/admin/sponsors-representative';
                var method = 'POST';
                if (id) {
                    url = '/admin/sponsors-representative/' + id;
                    formData.append('_method', 'PUT');
                }
                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            $('#modal-sponsor').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        Swal.fire('Error', 'An error occurred', 'error');
                    }
                });
            });

            // 4) Delete data
            $(document).on('click', '.delete-sponsor', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Deleted data cannot be recovered!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/sponsors-representative/' + id,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: res.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.log(xhr);
                                Swal.fire('Error', 'An error occurred', 'error');
                            }
                        });
                    }
                });
            });

            // Cropper.js integration for modal image
            var modalCropper;
            var modalImage = document.getElementById('modalImagePreview');
            var modalImageInput = document.getElementById('modalImageInput');
            var modalCropContainer = document.getElementById('modalCropContainer');
            var modalCropButton = document.getElementById('modalCropButton');
            var modalCroppedImageInput = document.getElementById('modalCroppedImage');

            modalImageInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files.length > 0) {
                    var file = e.target.files[0];
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        modalImage.src = e.target.result;
                        modalCropContainer.style.display = 'block';
                        if (modalCropper) {
                            modalCropper.destroy();
                        }
                        modalCropper = new Cropper(modalImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                        });
                    };
                    reader.readAsDataURL(file);
                }
            });

            modalCropButton.addEventListener('click', function() {
                if (modalCropper) {
                    var canvas = modalCropper.getCroppedCanvas({
                        width: 300,
                        height: 300,
                    });
                    var croppedDataUrl = canvas.toDataURL('image/jpeg');
                    modalCroppedImageInput.value = croppedDataUrl;
                    Swal.fire({
                        title: 'Cropped Image',
                        imageUrl: croppedDataUrl,
                        imageAlt: 'Cropped Image',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
    </script>
@endpush
