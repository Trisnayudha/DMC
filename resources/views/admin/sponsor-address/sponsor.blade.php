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
                                                <th>Address</th>
                                                <th>Country</th>
                                                <th width="15%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; ?>
                                            @foreach ($data as $post)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $post->address }}</td>
                                                    <td>{{ $post->country }}</td>
                                                    <td>
                                                        <button class="btn btn-success edit-sponsor"
                                                            data-id="{{ $post->id }}" title="Edit">
                                                            <span class="fa fa-edit"></span>
                                                        </button>
                                                        <button class="btn btn-danger delete-sponsor"
                                                            data-id="{{ $post->id }}" title="Delete">
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
                    <form action="{{ route('sponsors-address.store') }}" id="addEditCategoryForm" name="addEditCategoryForm"
                        class="form-horizontal" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="sponsor_id" id="sponsor_id" value="{{ $sponsor_id }}">
                        <input type="hidden" name="id" id="address_id">
                        <div class="form-group">
                            <label for=""> Link Gmaps </label>
                            <input type="text" name="link_gmaps" id="link_gmaps" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for=""> Address </label>
                            <input type="text" name="address" id="address" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Lat</label>
                            <input type="text" name="lat" id="lat" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Lang</label>
                            <input type="text" name="lang" id="lang" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="code">Country</label>
                            <select name="country" id="country" class="form-control select2">
                                <option value="">Select</option>
                                @foreach ($country as $item)
                                    <option value="{{ $item['country'] }}">
                                        {{ $item['country'] }}</option>
                                @endforeach
                            </select>
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
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Add New
            $('#addNewCategory').click(function() {
                $('#addEditCategoryForm').trigger("reset");
                $('#ajaxCategoryModel').html("Add Sponsor Address");
                $('#category-model').modal('show');
            });

            // Edit Sponsor Address
            $(document).on('click', '.edit-sponsor', function() {
                var id = $(this).data('id');

                $.get("{{ url('admin/sponsors-address') }}/" + id + "/edit", function(data) {
                    $('#ajaxCategoryModel').html("Edit Sponsor Address");
                    $('#category-model').modal('show');
                    $('#sponsor_id').val(data.sponsor_id);
                    $('#link_gmaps').val(data.link_gmaps);
                    $('#address').val(data.address);
                    $('#lat').val(data.lat);
                    $('#lang').val(data.lang);
                    $('#country').val(data.country);
                });
            });

            // Delete Sponsor Address
            $(document).on('click', '.delete-sponsor', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to delete this address?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ url('admin/sponsors-address') }}/" + id,
                            dataType: "json",
                            success: function(response) {
                                Swal.fire("Deleted!", "Address has been deleted.",
                                    "success");
                                location.reload();
                            },
                            error: function(response) {
                                Swal.fire("Error!", "Could not delete address.",
                                    "error");
                            }
                        });
                    }
                });
            });

            $('#laravel_crud').DataTable();
        });
    </script>
@endpush
