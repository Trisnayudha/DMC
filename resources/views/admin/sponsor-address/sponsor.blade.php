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
                                                <th>Coordinates</th>
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
                                                    <td class="text-nowrap">{{ $post->lat + 0 }}, {{ $post->lang + 0 }}</td>
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
                        <input type="hidden" name="_method" id="form_method" value="POST">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="3" style="height: auto"
                                placeholder="e.g. Prosperity Tower 10th floor, District 8 SCBD, Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan"
                                required></textarea>
                            <div class="d-flex align-items-center mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-find-location">
                                    <i class="fas fa-map-marker-alt"></i> Find Location
                                </button>
                                <small id="geocode-status" class="ml-2"></small>
                            </div>
                        </div>
                        <div class="form-group" id="map-preview-wrap" style="display: none">
                            <iframe id="map-preview" class="w-100 rounded border-0" height="200" loading="lazy"></iframe>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-7">
                                <label for="coordinates">Coordinates (lat, lng)</label>
                                <input type="text" name="coordinates" id="coordinates" class="form-control"
                                    placeholder="-6.2281521, 106.8071746">
                                <small class="text-muted">Auto-filled. Pin off? Paste from Google Maps (right-click &rarr; copy).</small>
                            </div>
                            <div class="form-group col-md-5">
                                <label for="country">Country</label>
                                <select name="country" id="country" class="form-control select2">
                                    <option value="">Auto detect</option>
                                    @foreach ($country as $item)
                                        <option value="{{ $item['country'] }}">
                                            {{ $item['country'] }}</option>
                                    @endforeach
                                </select>
                            </div>
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

            var storeUrl = "{{ route('sponsors-address.store') }}";
            var lastLookup = '';

            function showPreview(coordinates) {
                var valid = /^\s*-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?\s*$/.test(coordinates || '');
                $('#map-preview-wrap').toggle(valid);
                if (valid) {
                    $('#map-preview').attr('src', 'https://maps.google.com/maps?q=' + encodeURIComponent(coordinates.replace(/\s/g, '')) + '&z=16&output=embed');
                }
            }

            function setStatus(text, cls) {
                $('#geocode-status').attr('class', 'ml-2 ' + cls).text(text);
            }

            function resetForm() {
                $('#addEditCategoryForm').trigger('reset');
                $('#country').val('').trigger('change');
                setStatus('', '');
                showPreview('');
                lastLookup = '';
            }

            function findLocation() {
                var address = $.trim($('#address').val());
                if (!address || address === lastLookup) return;
                lastLookup = address;

                setStatus('Searching...', 'text-muted');
                $.post("{{ route('sponsors-address.geocode') }}", { address: address })
                    .done(function(res) {
                        if (res.country) $('#country').val(res.country).trigger('change');

                        if (res.lat === null) {
                            setStatus('Location not found. Paste coordinates from Google Maps.', 'text-danger');
                            return;
                        }

                        $('#coordinates').val(res.lat + ', ' + res.lng);
                        showPreview(res.lat + ', ' + res.lng);
                        if (res.precise) {
                            setStatus('Location found.', 'text-success');
                        } else {
                            setStatus('Approximate area only. Check the map, paste exact coordinates if needed.', 'text-warning');
                        }
                    })
                    .fail(function() {
                        setStatus('Lookup failed, try again.', 'text-danger');
                        lastLookup = '';
                    });
            }

            $('#btn-find-location').on('click', function() {
                lastLookup = '';
                findLocation();
            });
            $('#address').on('blur', findLocation);
            $('#coordinates').on('change', function() {
                showPreview($(this).val());
            });

            // Add New
            $('#addNewCategory').click(function() {
                resetForm();
                $('#addEditCategoryForm').attr('action', storeUrl);
                $('#form_method').val('POST');
                $('#ajaxCategoryModel').html("Add Sponsor Address");
                $('#category-model').modal('show');
            });

            // Edit Sponsor Address
            $(document).on('click', '.edit-sponsor', function() {
                var id = $(this).data('id');

                $.get("{{ url('admin/sponsors-address') }}/" + id + "/edit", function(data) {
                    resetForm();
                    $('#addEditCategoryForm').attr('action', "{{ url('admin/sponsors-address') }}/" + id);
                    $('#form_method').val('PUT');
                    $('#ajaxCategoryModel').html("Edit Sponsor Address");
                    $('#category-model').modal('show');
                    $('#sponsor_id').val(data.sponsor_id);
                    $('#address').val(data.address);
                    lastLookup = $.trim(data.address);
                    $('#country').val(data.country).trigger('change');

                    if (parseFloat(data.lat) !== 0 || parseFloat(data.lang) !== 0) {
                        var coordinates = parseFloat(data.lat) + ', ' + parseFloat(data.lang);
                        $('#coordinates').val(coordinates);
                        showPreview(coordinates);
                    }
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
